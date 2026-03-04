<?php
namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\ServiceTable;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function showMenu($identifier)
    {
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();
        $owner = User::find($table->user_id);

        // 1. Contamos el total de registros de esta mesa (pendientes + sonando)
        // Filtramos por tipo 'song' para el dashboard de karaoke
        $mySongsCount = Queue::where('service_table_id', $table->id)
            ->whereIn('status', ['pending', 'playing'])
            ->where('type', 'song')
            ->count();

        // 2. Obtenemos la cola global de pendientes del local para calcular posiciones
        $globalQueue = Queue::where('user_id', $table->user_id)
            ->where('status', 'pending')
            ->where('type', 'song')
            ->orderBy('order_index', 'asc')
            ->get();

        // 3. Obtenemos los pedidos de esta mesa cargando la relación 'song'
        // para poder acceder al título y artista que están en la otra tabla
        $myActiveSongs = Queue::with('song')
            ->where('service_table_id', $table->id)
            ->where('status', 'pending')
            ->where('type', 'song')
            ->orderBy('order_index', 'asc')
            ->get()
            ->map(function ($queueItem) use ($globalQueue) {
                // Calculamos posición real
                $position = $globalQueue->where('order_index', '<', $queueItem->order_index)->count() + 1;

                return [
                    'id'       => $queueItem->id,
                    // Accedemos a los datos a través de la relación 'song'
                    // Si por algún motivo no hay canción (null), ponemos un fallback
                    'title'    => $queueItem->song
                        ? $queueItem->song->youtube_title
                        : 'Pedido: ' . $queueItem->customer_name,
                    'position' => $position,
                    'customer_name' => $queueItem->customer_name,
                ];
            });

        return Inertia::render('Customer/Menu', [
            'table'         => $table,
            'brandSettings' => $owner->settings,
            'myActiveSongs' => $myActiveSongs,
            'mySongsCount'  => $mySongsCount,
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
            return response()->json(['local' => [], 'youtube' => []]);
        }

        // Primero identificamos la mesa y el dueño
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();

                                                              // 1. Buscar SOLO en las canciones de ESTE local (user_id)
        $localSongs = Song::where('user_id', $table->user_id) // <-- Filtro de seguridad
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('artist', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

        $youtubeResults = [];

        // 2. Si hay poco resultado local, usamos YouTube
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
                $youtubeResults = $response->json()['items'];
            }
        }

        return response()->json([
            'local'   => $localSongs,
            'youtube' => $youtubeResults,
        ]);
    }

    public function storeSong(Request $request, $identifier)
    {
        $table = ServiceTable::where('identifier', $identifier)->firstOrFail();

        // 1. Buscamos si el local ya tiene esta canción o la creamos
        // Usamos youtube_id y user_id para respetar tu restricción UNIQUE
        $song = Song::firstOrCreate(
            [
                'user_id'    => $table->user_id,
                'youtube_id' => $request->youtube_id,
            ],
            [
                'title'         => $request->title,
                'artist'        => $request->artist,
                'youtube_title' => $request->youtube_title, // Guardamos el título real de YT
                'thumbnail_url' => $request->thumbnail_url, // Guardamos la miniatura
                'times_played'  => 0,
            ]
        );

        // 2. Registramos la petición en la cola (Queue)
        Queue::create([
            'user_id'          => $table->user_id,
            'song_id'          => $song->id,
            'service_table_id' => $table->id,
            'customer_name'    => $request->customer_name,
            'status'           => 'pending',
            'order_index'      => Queue::where('user_id', $table->user_id)->max('order_index') + 1,
        ]);

        return redirect()->route('customer.menu', $identifier)
            ->with('success', '¡Tu canción ha sido añadida a la cola!');

    }
}
