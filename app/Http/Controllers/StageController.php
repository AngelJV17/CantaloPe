<?php
namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\Setting;
use App\Models\SongStat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StageController extends Controller
{
    public function show(User $user)
    {
        $settings = Setting::where('user_id', $user->id)->first();

        $current = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->where('status', 'playing')
            ->where('type', 'song')
            ->first();

        $next = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'ready'])
            ->where('type', 'song')
            ->orderBy('order_index')
            ->first();

        return Inertia::render('Stage/Player', [
            'current'  => $current,
            'next'     => $next,
            'settings' => $settings,
            'ownerId'  => $user->id,
        ]);
    }

    public function finish(Queue $queue)
    {
        $result = DB::transaction(function () use ($queue) {
            $queue->loadMissing(['song', 'serviceTable']);

            $queue->update([
                'status' => 'played',
            ]);

            // Registrar estadística de reproducción
            if ($queue->song_id) {
                $songStat = SongStat::firstOrCreateFor($queue->user_id, $queue->song_id);
                $songStat->markAsPlayed();
            }

            $current = Queue::with(['song', 'serviceTable'])
                ->where('user_id', $queue->user_id)
                ->whereIn('status', ['pending', 'ready'])
                ->where('type', 'song')
                ->orderBy('order_index')
                ->first();

            if ($current) {
                $current->update([
                    'status' => 'playing',
                ]);

                $current->load(['song', 'serviceTable']);
            }

            $next = null;

            if ($current) {
                $next = Queue::with(['song', 'serviceTable'])
                    ->where('user_id', $queue->user_id)
                    ->where('status', 'pending')
                    ->where('type', 'song')
                    ->orderBy('order_index')
                    ->first();
            }

            $fullQueue = Queue::with(['song', 'serviceTable'])
                ->where('user_id', $queue->user_id)
                ->whereIn('status', ['pending', 'ready', 'playing'])
                ->where('type', 'song')
                ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
                ->orderBy('order_index')
                ->get()
                ->toArray();

            broadcast(new QueueUpdated($current ?? $queue, $fullQueue));

            return [
                'current' => $current,
                'next'    => $next,
            ];
        });

        return response()->json([
            'success' => true,
            'current' => $result['current'],
            'next'    => $result['next'],
        ]);
    }
}
