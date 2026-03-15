<?php
namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\ServiceTable;
use App\Models\Song;
use App\Models\SongStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function showMenu(string $identifier)
    {
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();
        $owner = User::findOrFail($table->user_id);

        $mySongsCount = Queue::where('service_table_id', $table->id)
            ->whereIn('status', ['pending', 'playing'])
            ->where('type', 'song')
            ->count();

        $globalQueue = Queue::where('user_id', $table->user_id)
            ->where('status', 'pending')
            ->where('type', 'song')
            ->orderBy('order_index')
            ->get();

        $myActiveSongs = Queue::with('song')
            ->where('service_table_id', $table->id)
            ->whereIn('status', ['pending', 'playing'])
            ->where('type', 'song')
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('order_index')
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

    public function showSongSearch(string $identifier)
    {
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();
        $owner = User::findOrFail($table->user_id);

        return Inertia::render('Customer/SongSearch', [
            'table'         => $table,
            'brandSettings' => $owner->settings,
        ]);
    }

    public function searchSongs(Request $request, string $identifier)
    {
        $query = trim((string) $request->input('q'));

        if ($query === '') {
            return response()->json([
                'local'   => [],
                'youtube' => [],
            ]);
        }

        ServiceTable::where('identifier', $identifier)->firstOrFail();

        $localSongs = Song::query()
            ->search($query)
            ->embeddable()
            ->activePrivacy()
            ->limit(5)
            ->get([
                'id',
                'youtube_id',
                'youtube_title',
                'title',
                'artist',
                'channel_title',
                'thumbnail_url',
                'duration_seconds',
            ]);

        $youtubeResults = [];

        if ($localSongs->count() < 3) {
            $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
                'part'            => 'snippet',
                'q'               => $query . ' karaoke',
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

    public function storeSong(Request $request, string $identifier)
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
            $song = $this->findOrCreateSongFromYoutube($validated);

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

            $songStat = SongStat::firstOrCreateFor($table->user_id, $song->id);
            $songStat->markAsRequested();

            $queue->load(['song', 'serviceTable']);

            $this->broadcastQueue($queue);
        });

        return redirect()
            ->route('customer.menu', $identifier)
            ->with('success', '¡Tu canción ha sido añadida a la cola!');
    }

    protected function findOrCreateSongFromYoutube(array $validated): Song
    {
        $youtubeData = $this->fetchYoutubeVideoData($validated['youtube_id']);

        $attributes = $this->buildSongAttributes($validated, $youtubeData);

        $song = Song::firstOrNew([
            'youtube_id' => $validated['youtube_id'],
        ]);

        $song->fill($attributes);
        $song->save();

        return $song;
    }

    protected function fetchYoutubeVideoData(string $youtubeId): ?array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'part' => 'snippet,contentDetails,status',
            'id'   => $youtubeId,
            'key'  => config('services.youtube.key'),
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('items.0');
    }

    protected function buildSongAttributes(array $validated, ?array $youtubeData): array
    {
        $snippet        = $youtubeData['snippet'] ?? [];
        $contentDetails = $youtubeData['contentDetails'] ?? [];
        $status         = $youtubeData['status'] ?? [];

        $youtubeTitle      = $snippet['title'] ?? $validated['youtube_title'] ?? $validated['title'];
        $cleanYoutubeTitle = $this->cleanYoutubeTitle($youtubeTitle);

        $channelTitle = $snippet['channelTitle'] ?? null;

        [$artist, $title] = $this->normalizeArtistAndTitle(
            $cleanYoutubeTitle,
            $validated['artist'] ?? null,
            $channelTitle
        );

        return [
            'youtube_title'        => $youtubeTitle,
            'title'                => $title,
            'artist'               => $artist,
            'channel_title'        => $channelTitle,
            'thumbnail_url'        => $this->resolveThumbnailUrl($snippet, $validated['thumbnail_url'] ?? null),
            'duration_seconds'     => $this->parseYoutubeDuration($contentDetails['duration'] ?? null),
            'category_id'          => $snippet['categoryId'] ?? null,
            'tags'                 => $snippet['tags'] ?? null,
            'youtube_published_at' => $snippet['publishedAt'] ?? null,
            'is_embeddable'        => (bool) ($status['embeddable'] ?? true),
            'privacy_status'       => $status['privacyStatus'] ?? null,
            'definition'           => $contentDetails['definition'] ?? null,
            'has_caption'          => ($contentDetails['caption'] ?? 'false') === 'true',
        ];
    }

    protected function normalizeArtistAndTitle(
        string $cleanYoutubeTitle,
        ?string $validatedArtist = null,
        ?string $channelTitle = null
    ): array {
        $artist = $validatedArtist ?: ($channelTitle ?: 'Desconocido');
        $title  = $cleanYoutubeTitle;

        if (str_contains($cleanYoutubeTitle, ' - ')) {
            $parts = explode(' - ', $cleanYoutubeTitle, 2);

            $artist = trim($parts[0]) ?: $artist;
            $title  = trim($parts[1]) ?: $cleanYoutubeTitle;
        }

        return [$artist, $title];
    }

    protected function cleanYoutubeTitle(string $title): string
    {
        $patterns = [
            '/\bkaraoke\b/i',
            '/\blyrics?\b/i',
            '/\blyric video\b/i',
            '/\bletra\b/i',
            '/\bpista\b/i',
            '/\binstrumental\b/i',
            '/\bversión\b/i',
            '/\bversion\b/i',
            '/\bofficial video\b/i',
            '/\bvideo oficial\b/i',
            '/\baudio oficial\b/i',
            '/\bcon letra\b/i',
            '/\bsin voz\b/i',
            '/\bfull hd\b/i',
            '/\b4k\b/i',
            '/\bhd\b/i',
        ];

        $title = preg_replace($patterns, '', $title);

        $title = preg_replace('/\(\s*\)/', '', $title);
        $title = preg_replace('/\[\s*\]/', '', $title);
        $title = preg_replace('/\(([-\s]*)\)/', '', $title);
        $title = preg_replace('/\[([-\s]*)\]/', '', $title);

        $title = preg_replace('/\s{2,}/', ' ', $title);
        $title = preg_replace('/\s*-\s*-\s*/', ' - ', $title);

        $title = preg_replace('/\(\s+/', '(', $title);
        $title = preg_replace('/\s+\)/', ')', $title);

        $title = trim($title);
        $title = trim($title, '-');
        $title = preg_replace('/\s{2,}/', ' ', $title);

        return trim($title);
    }

    protected function resolveThumbnailUrl(array $snippet, ?string $fallback = null): ?string
    {
        return $snippet['thumbnails']['high']['url'] ?? $snippet['thumbnails']['medium']['url'] ?? $snippet['thumbnails']['default']['url'] ?? $fallback;
    }

    protected function parseYoutubeDuration(?string $duration): ?int
    {
        if (! $duration) {
            return null;
        }

        try {
            $interval = new \DateInterval($duration);

            return ($interval->d * 86400)
             + ($interval->h * 3600)
             + ($interval->i * 60)
             + $interval->s;
        } catch (\Throwable $e) {
            return null;
        }
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
