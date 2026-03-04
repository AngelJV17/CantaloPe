<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
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
            'queues' => $queues
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_table_id' => 'required|exists:service_tables,id',
            'song_id' => 'nullable|exists:songs,id',
            'customer_name' => 'required|string',
            'is_vip' => 'boolean',
            'amount_paid' => 'numeric',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Crear el nuevo registro
            $newItem = Queue::create([
                'user_id' => auth()->id(),
                'service_table_id' => $validated['service_table_id'],
                'song_id' => $validated['song_id'],
                'customer_name' => $validated['customer_name'],
                'type' => 'song',
                'is_vip' => $validated['is_vip'] ?? false,
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'status' => 'pending',
            ]);

            // 2. REORDENAR TODA LA COLA PENDIENTE
            $this->reorderQueue();
        });

        return back()->with('success', 'Canción agregada a la cola');
    }

    protected function reorderQueue()
    {
        // Obtenemos todos los pendientes
        $items = Queue::whereIn('status', ['pending', 'ready'])
            ->orderByDesc('is_vip') // Primero los VIP
            ->orderByDesc('amount_paid') // Luego los que pagaron más
            ->orderBy('created_at', 'asc') // Finalmente por orden de llegada
            ->get();

        // Actualizamos su order_index secuencialmente
        foreach ($items as $index => $item) {
            $item->update(['order_index' => $index + 1]);
        }
    }
}
