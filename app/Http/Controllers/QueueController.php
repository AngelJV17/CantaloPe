<?php
namespace App\Http\Controllers;

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
            ->whereIn('status', ['pending', 'ready', 'playing'])
            ->orderBy('order_index', 'asc')
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

            Queue::create([
                'user_id'          => Auth::id(), // ✔ forma recomendada
                'service_table_id' => $validated['service_table_id'],
                'song_id'          => $validated['song_id'],
                'customer_name'    => $validated['customer_name'],
                'type'             => 'song',
                'is_vip'           => $validated['is_vip'] ?? false,
                'amount_paid'      => $validated['amount_paid'] ?? 0,
                'status'           => 'pending',
            ]);

            $this->reorderQueue();
        });

        return back()->with('success', 'Canción agregada a la cola');
    }

    public function updateStatus(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready,playing,played',
        ]);

        DB::transaction(function () use ($queue, $validated) {

            // Si se está reproduciendo una canción
            if ($validated['status'] === 'playing') {

                // terminar cualquier otra que esté reproduciéndose
                Queue::where('status', 'playing')
                    ->update(['status' => 'played']);
            }

            $queue->update([
                'status' => $validated['status'],
            ]);

            // Reordenar la cola después de cambios
            $this->reorderQueue();
        });

        return back();
    }

    protected function reorderQueue()
    {
        $items = Queue::whereIn('status', ['pending', 'ready'])
            ->orderByDesc('is_vip')
            ->orderByDesc('amount_paid')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($items as $index => $item) {
            $item->update([
                'order_index' => $index + 1,
            ]);
        }
    }

    public function stage()
    {
        $current = Queue::with('song')
            ->where('status', 'playing')
            ->first();

        return Inertia::render('Stage/Show', [
            'current' => $current,
        ]);
    }
}
