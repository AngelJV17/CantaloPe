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
            ->where('type', 'song')
            ->where('status', 'playing')
            ->first();

        $next = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->where('type', 'song')
            ->whereIn('status', ['pending', 'ready'])
            ->orderBy('order_index')
            ->first();

        return Inertia::render('Stage/Player', [
            'current'  => $current,
            'next'     => $next,
            'settings' => $settings,
            'ownerId'  => $user->id,
        ]);
    }

    public function current(User $user)
    {
        $current = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->where('type', 'song')
            ->where('status', 'playing')
            ->first();

        $next = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->where('type', 'song')
            ->whereIn('status', ['pending', 'ready'])
            ->orderBy('order_index')
            ->first();

        return response()->json([
            'current' => $current,
            'next'    => $next,
        ]);
    }

    public function finish(Queue $queue)
    {
        $result = DB::transaction(function () use ($queue) {
            abort_unless($queue->type === 'song', 404);

            $queue->update([
                'status'      => 'played',
                'finished_at' => now(),
            ]);

            if ($queue->song_id) {
                $stat = SongStat::firstOrCreateFor($queue->user_id, $queue->song_id);
                $stat->markAsPlayed();
            }

            $current = Queue::with(['song', 'serviceTable'])
                ->where('user_id', $queue->user_id)
                ->where('type', 'song')
                ->whereIn('status', ['pending', 'ready'])
                ->orderBy('order_index')
                ->first();

            if ($current) {
                $current->update([
                    'status'        => 'playing',
                    'started_at'    => now(),
                    'finished_at'   => null,
                    'failed_reason' => null,
                ]);
            }

            $next = null;

            if ($current) {
                $next = Queue::with(['song', 'serviceTable'])
                    ->where('user_id', $queue->user_id)
                    ->where('type', 'song')
                    ->where('status', 'pending')
                    ->orderBy('order_index')
                    ->first();
            }

            $fullQueue = Queue::with(['song', 'serviceTable'])
                ->where('user_id', $queue->user_id)
                ->where('type', 'song')
                ->whereIn('status', ['pending', 'ready', 'playing'])
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
