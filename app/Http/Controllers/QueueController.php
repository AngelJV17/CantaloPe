<?php
namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QueueController extends Controller
{
    public function index()
    {
        $queues = Queue::with(['song', 'serviceTable'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'ready', 'playing'])
            ->where('type', 'song')
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('order_index')
            ->get();

        $history = Queue::with(['song', 'serviceTable'])
            ->where('user_id', Auth::id())
            ->where('type', 'song')
            ->where('status', 'played')
            ->latest('finished_at')
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Queues/Index', [
            'queues'  => $queues,
            'history' => $history,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_table_id' => ['required', 'exists:service_tables,id'],
            'song_id'          => ['nullable', 'exists:songs,id'],
            'customer_name'    => ['required', 'string', 'max:100'],
            'is_vip'           => ['nullable', 'boolean'],
            'amount_paid'      => ['nullable', 'numeric'],
        ]);

        DB::transaction(function () use ($validated) {
            $queue = Queue::create([
                'user_id'          => Auth::id(),
                'service_table_id' => $validated['service_table_id'],
                'song_id'          => $validated['song_id'] ?? null,
                'customer_name'    => $validated['customer_name'],
                'type'             => 'song',
                'is_vip'           => $validated['is_vip'] ?? false,
                'amount_paid'      => $validated['amount_paid'] ?? 0,
                'status'           => 'pending',
                'order_index'      => 0,
            ]);

            $this->reorderQueue(Auth::id());
            $queue->load(['song', 'serviceTable']);
            $this->broadcastQueue($queue);
        });

        return back()->with('success', 'Pedido agregado a la cola.');
    }

    public function updateStatus(Request $request, Queue $queue)
    {
        abort_unless($queue->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,ready,playing,played,failed'],
        ]);

        DB::transaction(function () use ($queue, $validated) {
            if ($validated['status'] === 'playing') {
                Queue::where('user_id', $queue->user_id)
                    ->where('type', 'song')
                    ->where('status', 'playing')
                    ->where('id', '!=', $queue->id)
                    ->update([
                        'status'      => 'played',
                        'finished_at' => now(),
                    ]);

                $queue->update([
                    'status'        => 'playing',
                    'started_at'    => now(),
                    'finished_at'   => null,
                    'failed_reason' => null,
                ]);
            }

            if ($validated['status'] === 'played') {
                $queue->update([
                    'status'      => 'played',
                    'finished_at' => now(),
                ]);
            }

            if ($validated['status'] === 'pending') {
                $queue->update([
                    'status'        => 'pending',
                    'started_at'    => null,
                    'finished_at'   => null,
                    'failed_reason' => null,
                ]);
            }

            if ($validated['status'] === 'ready') {
                $queue->update([
                    'status' => 'ready',
                ]);
            }

            if ($validated['status'] === 'failed') {
                $queue->update([
                    'status'        => 'failed',
                    'finished_at'   => now(),
                    'failed_reason' => 'Marcada manualmente como fallida',
                ]);
            }

            $this->reorderQueue($queue->user_id);

            $queue->refresh()->load(['song', 'serviceTable']);
            $this->broadcastQueue($queue);
        });

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    protected function reorderQueue(int $userId): void
    {
        $items = Queue::where('user_id', $userId)
            ->where('type', 'song')
            ->whereIn('status', ['pending', 'ready'])
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
            ->where('type', 'song')
            ->whereIn('status', ['pending', 'ready', 'playing'])
            ->orderByRaw("CASE WHEN status = 'playing' THEN 0 ELSE 1 END")
            ->orderBy('order_index')
            ->get()
            ->toArray();

        broadcast(new QueueUpdated($queue, $fullQueue));
    }
}
