<?php
namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\ServiceTable;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function showMenu($identifier)
    {
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();
        $owner = User::find($table->user_id);

        $mySongsCount = Queue::where('service_table_id', $table->id)
            ->whereIn('status', ['pending', 'playing'])
            ->where('type', 'song')
            ->count();

        $globalQueue = Queue::where('user_id', $table->user_id)
            ->where('status', 'pending')
            ->where('type', 'song')
            ->orderBy('order_index', 'asc')
            ->get();

        $myActiveSongs = Queue::with('song')
            ->where('service_table_id', $table->id)
            ->whereIn('status', ['pending', 'playing'])
            ->where('type', 'song')
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('order_index', 'asc')
            ->get()
            ->map(function ($queueItem) use ($globalQueue) {
                $position = $queueItem->status === 'playing'
                    ? 0
                    : $globalQueue->where('order_index', '<', $queueItem->order_index)->count() + 1;

                return [
                    'id'            => $queueItem->id,
                    'title'         => $queueItem->song
                        ? ($queueItem->song->youtube_title ?: $queueItem->song->title)
                        : 'Pedido: ' . $queueItem->customer_name,
                    'position'      => $position,
                    'customer_name' => $queueItem->customer_name,
                    'status'        => $queueItem->status,
                    'song'          => $queueItem->song,
                ];
            });

        return Inertia::render('Customer/Menu', [
            'table'         => $table,
            'brandSettings' => $owner->settings,
            'myActiveSongs' => $myActiveSongs,
            'mySongsCount'  => $mySongsCount,
            'ownerId'       => $table->user_id,
        ]);
    }

    public function showSongSearch($identifier)
    {
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();
        $owner = User::find($table->user_id);

        return Inertia::render('Customer/SongSearch', [
            'table'         => $table,
            'brandSettings' => $owner->settings,
        ]);
    }

    public function searchSongs(Request $request, $identifier)
    {
        $query = $request->input('q');

        if (! $query) {
            return response()->json([
                'local'   => [],
                'youtube' => [],
            ]);
        }

        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();

        $localSongs = Song::where('user_id', $table->user_id)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('artist', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

        $youtubeResults = [];

        if ($localSongs->count() < 3) {
            $response = Http::get("https://www.googleapis.com/youtube/v3/search", [
                'part'            => 'snippet',
                'q'               => $query . " karaoke",
                'maxResults'      => 10,
                'type'            => 'video',
                'videoEmbeddable' => 'true',
                'key'             => config('services.youtube.key'),
            ]);

            if ($response->successful()) {
                $youtubeResults = $response->json('items', []);
            }
        }

        return response()->json([
            'local'   => $localSongs,
            'youtube' => $youtubeResults,
        ]);
    }

    public function storeSong(Request $request, $identifier)
    {
        $validated = $request->validate([
            'youtube_id'    => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            'title'         => ['required', 'string', 'max:255'],
            'artist'        => ['nullable', 'string', 'max:255'],
            'youtube_title' => ['nullable', 'string', 'max:255'],
            'thumbnail_url' => ['nullable', 'string', 'max:500'],
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();

        DB::transaction(function () use ($validated, $table) {
            $song = Song::firstOrCreate(
                [
                    'user_id'    => $table->user_id,
                    'youtube_id' => $validated['youtube_id'],
                ],
                [
                    'title'         => $validated['title'],
                    'artist'        => $validated['artist'] ?? null,
                    'youtube_title' => $validated['youtube_title'] ?? $validated['title'],
                    'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                    'times_played'  => 0,
                ]
            );

            $hasPlaying = Queue::where('user_id', $table->user_id)
                ->where('status', 'playing')
                ->where('type', 'song')
                ->exists();

            $queue = Queue::create([
                'user_id'          => $table->user_id,
                'song_id'          => $song->id,
                'service_table_id' => $table->id,
                'customer_name'    => $validated['customer_name'],
                'type'             => 'song',
                'status'           => $hasPlaying ? 'pending' : 'playing',
                'is_vip'           => false,
                'amount_paid'      => 0,
                'order_index'      => 0,
            ]);

            $this->reorderQueue($table->user_id);

            $queue->load(['song', 'serviceTable']);

            $this->broadcastQueue($queue);
        });

        return redirect()
            ->route('customer.menu', $identifier)
            ->with('success', '¡Tu canción ha sido añadida a la cola!');
    }

    protected function reorderQueue(int $userId): void
    {
        $items = Queue::where('user_id', $userId)
            ->whereIn('status', ['pending', 'ready'])
            ->where('type', 'song')
            ->orderByDesc('is_vip')
            ->orderByDesc('amount_paid')
            ->orderBy('created_at')
            ->get();

        foreach ($items as $index => $item) {
            $item->update([
                'order_index' => $index + 1,
            ]);
        }
    }

    protected function broadcastQueue(Queue $queue): void
    {
        $fullQueue = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $queue->user_id)
            ->whereIn('status', ['pending', 'ready', 'playing'])
            ->where('type', 'song')
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('order_index')
            ->get()
            ->toArray();

        broadcast(new QueueUpdated($queue, $fullQueue));
    }
}
