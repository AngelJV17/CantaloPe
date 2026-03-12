<?php
namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\Setting;
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
            ->first();

        $next = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
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

    public function finish(Queue $queue)
    {
        $result = DB::transaction(function () use ($queue) {

            $queue->update([
                'status' => 'played',
            ]);

            $current = Queue::with(['song', 'serviceTable'])
                ->where('user_id', $queue->user_id)
                ->whereIn('status', ['pending', 'ready'])
                ->orderBy('order_index')
                ->first();

            if ($current) {
                $current->update([
                    'status' => 'playing',
                ]);
            }

            $next = null;

            if ($current) {
                $next = Queue::with(['song', 'serviceTable'])
                    ->where('user_id', $queue->user_id)
                    ->where('status', 'pending')
                    ->orderBy('order_index')
                    ->first();
            }

            // 🔹 cola completa para realtime
            $fullQueue = Queue::with(['song', 'serviceTable'])
                ->where('user_id', $queue->user_id)
                ->whereIn('status', ['pending', 'ready', 'playing'])
                ->orderBy('order_index')
                ->get()
                ->toArray();

            // 🔹 broadcast correcto
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
