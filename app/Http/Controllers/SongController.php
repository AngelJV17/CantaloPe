<?php
namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Iniciamos la consulta (Query Builder)
        $query = Song::query();

        // 2. Aplicamos el filtro de búsqueda si existe
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('youtube_id', 'like', '%' . $request->search . '%');
        }

        // 3. Obtenemos las canciones paginadas
        // Usamos withQueryString() para que al cambiar de página se mantenga el filtro de búsqueda
        $songs = $query->orderBy('created_at', 'desc') // Los últimos registrados primero
            ->paginate($request->input('perPage', 10))
            ->withQueryString();

        return Inertia::render('Songs/Index', [
            'songs'    => $songs,
            'filters'  => $request->only(['search', 'perPage']), // Devolvemos los filtros para que el input no se borre
            'settings' => $user ? $user->settings : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
        ]);

        $url = $request->youtube_url;

        // 1. Extraer el ID de YouTube usando una expresión regular
        preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?v=|embed\/|v\/))([^\?&\"'>]+)/", $url, $matches);

        if (! isset($matches[1])) {
            return back()->with('error', 'El enlace de YouTube no es válido.');
        }

        $videoId = $matches[1];

        // 2. Obtener el título del video (Truco rápido sin API Key compleja)
        // Usamos el servicio oembed de YouTube que es gratuito y público
        $response = Http::get("https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v={$videoId}&format=json");

        if ($response->failed()) {
            return back()->with('error', 'No pudimos obtener información del video.');
        }

        $videoData = $response->json();

        // 3. Guardar en la base de datos
        Song::create([
            'title'        => $videoData['title'],
            'youtube_id'   => $videoId,
            'youtube_url'  => $url,
            'times_played' => 0,
        ]);

        return redirect()->route('songs.index')->with('message', '¡Hit añadido a la cola!');
    }

    public function destroy(Song $song)
    {
        $song->delete();
        return back()->with('message', 'Canción eliminada de la lista.');
    }
}
