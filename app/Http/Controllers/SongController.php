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

        // 1. Filtramos solo las canciones que pertenecen al usuario logueado
        $query = Song::where('user_id', $user->id);

        // 2. Aplicamos filtros de búsqueda (ahora busca en título y artista)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('artist', 'like', '%' . $request->search . '%')
                    ->orWhere('youtube_id', 'like', '%' . $request->search . '%');
            });
        }

        $songs = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('perPage', 10))
            ->withQueryString();

        return Inertia::render('Songs/Index', [
            'songs'    => $songs,
            'filters'  => $request->only(['search', 'perPage']),
            'settings' => $user->settings,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url',
        ]);

        $url = $request->youtube_url;

        // 1. Extraer ID de YouTube
        preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?v=|embed\/|v\/))([^\?&\"'>]+)/", $url, $matches);

        if (! isset($matches[1])) {
            return back()->with('error', 'El enlace de YouTube no es válido.');
        }

        $videoId = $matches[1];
        $user    = Auth::user();

        // 2. Evitar duplicados para este usuario específico
        $exists = Song::where('user_id', $user->id)->where('youtube_id', $videoId)->exists();
        if ($exists) {
            return back()->with('error', 'Esta canción ya está en tu biblioteca.');
        }

        // 3. Obtener info de YouTube (oembed es gratuito y no requiere API Key compleja)
        $response = Http::get("https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v={$videoId}&format=json");

        if ($response->failed()) {
            return back()->with('error', 'No pudimos conectar con YouTube.');
        }

        $videoData = $response->json();
        $rawTitle  = $videoData['title'];

        // 4. LÓGICA DE LIMPIEZA AUTOMÁTICA
        // Intentamos separar "Artista - Título" si el video viene con ese formato
        $artist = $videoData['author_name'] ?? 'Desconocido';
        $title  = $rawTitle;

        if (str_contains($rawTitle, ' - ')) {
            $parts  = explode(' - ', $rawTitle);
            $artist = trim($parts[0]);
            $title  = trim($parts[1]);
        }

        // 5. Guardar con la nueva estructura
        Song::create([
            'user_id'       => $user->id,
            'title'         => $title,
            'artist'        => $artist,
            'youtube_title' => $rawTitle,
            'youtube_id'    => $videoId,
            'thumbnail_url' => $videoData['thumbnail_url'] ?? null,
            'times_played'  => 0,
        ]);

        return redirect()->route('songs.index')->with('success', '¡Hit añadido correctamente!');

    }

    public function destroy(Song $song)
    {
        // Seguridad: Solo el dueño puede borrar su propia canción
        if ($song->user_id !== Auth::id()) {
            abort(403);
        }

        $song->delete();
        return back()->with('success', 'Canción eliminada de tu biblioteca.');
    }
}
