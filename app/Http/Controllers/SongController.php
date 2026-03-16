<?php
namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Song::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('artist', 'like', '%' . $search . '%')
                    ->orWhere('youtube_title', 'like', '%' . $search . '%')
                    ->orWhere('channel_title', 'like', '%' . $search . '%')
                    ->orWhere('youtube_id', 'like', '%' . $search . '%');
            });
        }

        $songs = $query->orderBy('title', 'asc')
            ->paginate((int) $request->input('perPage', 10))
            ->withQueryString();

        return Inertia::render('Songs/Index', [
            'songs'    => $songs,
            'filters'  => $request->only(['search', 'perPage']),
            'settings' => $user->settings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'youtube_url' => ['required', 'url'],
        ]);

        $videoId = $this->extractYoutubeVideoId($validated['youtube_url']);

        if (! $videoId) {
            return back()->with('error', 'El enlace de YouTube no es válido.');
        }

        $exists = Song::where('youtube_id', $videoId)->exists();

        if ($exists) {
            return back()->with('error', 'Esta canción ya existe en el catálogo.');
        }

        $videoData = $this->fetchYoutubeVideoData($videoId);

        if (! $videoData) {
            return back()->with('error', 'No pudimos obtener los datos del video desde YouTube.');
        }

        $attributes = $this->buildSongAttributesFromYoutube($videoId, $videoData);

        Song::create($attributes);

        return redirect()
            ->route('songs.index')
            ->with('success', '¡Canción añadida correctamente al catálogo!');
    }

    public function update(Request $request, Song $song)
    {
        $validated = $request->validate([
            'title'  => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
        ]);

        $song->update([
            'title'  => $validated['title'],
            'artist' => $validated['artist'] ?? null,
        ]);

        return back()->with('success', 'Canción actualizada correctamente.');
    }

    public function destroy(Song $song)
    {
        $song->delete();

        return back()->with('success', 'Canción eliminada del catálogo.');
    }

    protected function extractYoutubeVideoId(string $url): ?string
    {
        preg_match(
            "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?v=|embed\/|v\/))([^\?&\"'>]+)/",
            $url,
            $matches
        );

        return $matches[1] ?? null;
    }

    protected function fetchYoutubeVideoData(string $videoId): ?array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'part' => 'snippet,contentDetails,status',
            'id'   => $videoId,
            'key'  => config('services.youtube.key'),
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('items.0');
    }

    protected function buildSongAttributesFromYoutube(string $videoId, array $videoData): array
    {
        $snippet        = $videoData['snippet'] ?? [];
        $contentDetails = $videoData['contentDetails'] ?? [];
        $status         = $videoData['status'] ?? [];

        $youtubeTitle = $snippet['title'] ?? 'Sin título';
        $channelTitle = $snippet['channelTitle'] ?? null;

        [$artist, $title] = $this->normalizeArtistAndTitle($youtubeTitle, $channelTitle);

        return [
            'youtube_id'           => $videoId,
            'youtube_title'        => $youtubeTitle,
            'title'                => $title,
            'artist'               => $artist,
            'channel_title'        => $channelTitle,
            'thumbnail_url'        => $this->resolveThumbnailUrl($snippet),
            'duration_seconds'     => $this->parseYoutubeDuration($contentDetails['duration'] ?? null),
            'category_id'          => $snippet['categoryId'] ?? null,
            'tags'                 => $snippet['tags'] ?? null,
            'youtube_published_at' => $snippet['publishedAt'] ?? null,
            'is_embeddable'        => (bool) ($status['embeddable'] ?? true),
            'privacy_status'       => $status['privacyStatus'] ?? null,
            'definition'           => $contentDetails['definition'] ?? null,
            'has_caption'          => ($contentDetails['caption'] ?? 'false') === 'true',
        ];
    }

    protected function normalizeArtistAndTitle(string $youtubeTitle, ?string $channelTitle = null): array
    {
        $cleanTitle = $this->cleanYoutubeTitle($youtubeTitle);

        $artist = $channelTitle ?: 'Desconocido';
        $title  = $cleanTitle;

        if (str_contains($cleanTitle, ' - ')) {
            $parts = explode(' - ', $cleanTitle, 2);

            $artist = trim($parts[0]) ?: $artist;
            $title  = trim($parts[1]) ?: $cleanTitle;
        }

        return [$artist, $title];
    }

    protected function cleanYoutubeTitle(string $title): string
    {
        $patterns = [
            '/\bkaraoke\b/i',
            '/\blyrics?\b/i',
            '/\blyric video\b/i',
            '/\bletra\b/i',
            '/\bpista\b/i',
            '/\binstrumental\b/i',
            '/\bversión\b/i',
            '/\bversion\b/i',
            '/\bofficial video\b/i',
            '/\bvideo oficial\b/i',
            '/\baudio oficial\b/i',
            '/\bcon letra\b/i',
            '/\bsin voz\b/i',
            '/\bfull hd\b/i',
            '/\b4k\b/i',
            '/\bhd\b/i',
        ];

        $title = preg_replace($patterns, '', $title);

        $title = preg_replace('/\(\s*\)/', '', $title);
        $title = preg_replace('/\[\s*\]/', '', $title);
        $title = preg_replace('/\(([-\s]*)\)/', '', $title);
        $title = preg_replace('/\[([-\s]*)\]/', '', $title);
        $title = preg_replace('/\s{2,}/', ' ', $title);
        $title = preg_replace('/\s*-\s*-\s*/', ' - ', $title);
        $title = preg_replace('/\(\s+/', '(', $title);
        $title = preg_replace('/\s+\)/', ')', $title);

        $title = trim($title);
        $title = trim($title, '-');
        $title = preg_replace('/\s{2,}/', ' ', $title);

        return trim($title);
    }

    protected function resolveThumbnailUrl(array $snippet): ?string
    {
        return $snippet['thumbnails']['high']['url'] ?? $snippet['thumbnails']['medium']['url'] ?? $snippet['thumbnails']['default']['url'] ?? null;
    }

    protected function parseYoutubeDuration(?string $duration): ?int
    {
        if (! $duration) {
            return null;
        }

        try {
            $interval = new \DateInterval($duration);

            return ($interval->d * 86400)
             + ($interval->h * 3600)
             + ($interval->i * 60)
             + $interval->s;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
