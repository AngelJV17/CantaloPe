<?php
namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Setting;
use App\Models\User;
use Inertia\Inertia;

class StageController extends Controller
{
    /**
     * Pantalla principal del escenario (TV)
     */
    public function show(User $user)
    {

        // Configuración del karaoke
        $settings = Setting::where('user_id', $user->id)->first();

        // Canción actual
        $current = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->where('status', 'playing')
            ->orderBy('order_index')
            ->first();

        // Próxima canción
        $next = Queue::with(['song', 'serviceTable'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'ready'])
            ->orderBy('order_index')
            ->first();

        return Inertia::render('Stage/Player', [
            'current'  => $current,
            'next'     => $next,
            'settings' => $settings,
        ]);
    }

    /**
     * API para que la TV consulte la canción actual
     */
    public function current(User $user)
    {
        $current = Queue::with('song')
            ->where('user_id', $user->id)
            ->where('status', 'playing')
            ->orderBy('order_index')
            ->first();

        return response()->json($current);
    }

    /**
     * Finalizar canción actual
     * y activar la siguiente automáticamente
     */
    public function finish(Queue $queue)
    {
        $queue->update([
            'status' => 'played',
        ]);

        $next = Queue::where('user_id', $queue->user_id)
            ->where('status', 'pending')
            ->orderBy('order_index')
            ->first();

        if ($next) {
            $next->update([
                'status' => 'playing',
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
