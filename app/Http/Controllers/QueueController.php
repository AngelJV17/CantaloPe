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
            ->orderBy('order_index')
            ->get();

        return Inertia::render('Queues/Index', [
            'queues' => $queues,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_table_id' => 'required|exists:service_tables,id',
            'song_id'          => 'nullable|exists:songs,id',
            'customer_name'    => 'required|string',
            'is_vip'           => 'boolean',
            'amount_paid'      => 'numeric',
        ]);

        DB::transaction(function () use ($validated) {
            $queue = Queue::create([
                'user_id'          => Auth::id(),
                'service_table_id' => $validated['service_table_id'],
                'song_id'          => $validated['song_id'],
                'customer_name'    => $validated['customer_name'],
                'type'             => 'song',
                'is_vip'           => $validated['is_vip'] ?? false,
                'amount_paid'      => $validated['amount_paid'] ?? 0,
                'status'           => 'pending',
            ]);

            $this->reorderQueue();
            $this->broadcastQueue($queue);
        });

        return back();
    }

    public function updateStatus(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready,playing,played',
        ]);

        DB::transaction(function () use ($queue, $validated) {

            if ($validated['status'] === 'playing') {
                Queue::where('status', 'playing')
                    ->update(['status' => 'played']);

            }

            $queue->update([
                'status' => $validated['status'],
            ]);
            $this->reorderQueue();
            $this->broadcastQueue($queue);
        });

        return back();
    }

    protected function reorderQueue()
    {
        $items = Queue::where('user_id', Auth::id())
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

    protected function broadcastQueue($queue)
    {
        $fullQueue = Queue::with(['song', 'serviceTable'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'ready', 'playing'])
            ->orderBy('order_index')
            ->get()
            ->toArray();

        broadcast(new QueueUpdated($queue, $fullQueue));
    }
}
