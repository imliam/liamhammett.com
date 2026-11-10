<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Utilities\PodcastDownloadAnalytics;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PodcastController extends Controller
{
    public function index(): View
    {
        return view('podcasts.index', [
            'podcasts' => Podcast::published()->withCount(['episodes' => fn ($query) => $query->published()])->orderBy('title')->get(),
        ]);
    }

    public function show(string $podcast): View
    {
        $podcast = Podcast::published()->where('slug', $podcast)->firstOrFail();

        return view('podcasts.show', [
            'podcast' => $podcast,
            'episodes' => $podcast->episodes()->published()->orderByDesc('published_at')->get(),
        ]);
    }

    public function episode(string $podcast, string $episode): View
    {
        $episode = $this->publishedEpisode($podcast, $episode);

        return view('podcasts.episode', [
            'podcast' => $episode->podcast,
            'episode' => $episode,
        ]);
    }

    public function transcript(string $podcast, string $episode): View
    {
        $episode = $this->publishedEpisode($podcast, $episode);
        abort_unless($episode->hasTranscript(), 404);

        return view('podcasts.transcript', ['episode' => $episode, 'podcast' => $episode->podcast]);
    }

    public function chapters(string $podcast, string $episode): JsonResponse
    {
        $episode = $this->publishedEpisode($podcast, $episode);
        abort_unless($episode->chapters, 404);

        return response()->json(['version' => '1.2.0', 'chapters' => $episode->chapters])
            ->header('Content-Type', 'application/json+chapters')
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function embed(string $podcast, string $episode): Response
    {
        $episode = $this->publishedEpisode($podcast, $episode);

        return response()->view('podcasts.embed', ['episode' => $episode, 'podcast' => $episode->podcast])
            ->header('Content-Security-Policy', 'frame-ancestors *');
    }

    private function publishedEpisode(string $podcast, string $episode): PodcastEpisode
    {
        $podcast = Podcast::published()->where('slug', $podcast)->firstOrFail();

        return $podcast->episodes()->published()->where('slug', $episode)->firstOrFail()->setRelation('podcast', $podcast);
    }

    public function feed(Request $request, string $podcast): Response
    {
        $podcast = Podcast::published()->where('slug', $podcast)->firstOrFail();
        $episodes = $podcast->episodes()->published()->orderByDesc('published_at')->get();
        abort_if($episodes->isEmpty(), 404);

        $response = response(view('podcasts.feed', compact('podcast', 'episodes'))->render())
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=60, must-revalidate');

        // Hash the actual published feed so edits and scheduled releases change its validator.
        $response->setEtag(hash('sha256', $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    public function audio(Request $request, string $episode, PodcastDownloadAnalytics $analytics): Response
    {
        abort_unless(config('podcasts.x_accel_redirect'), 503, 'Podcast audio handoff is not enabled.');
        $episode = PodcastEpisode::published()->whereHas('podcast', fn ($query) => $query->published())
            ->whereKey($episode)->firstOrFail();

        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        $file = realpath($disk->path($episode->audio_path));
        abort_unless($root && $file && str_starts_with($file, $root.DIRECTORY_SEPARATOR)
            && is_file($file) && is_readable($file) && str_ends_with($file, '.mp3'), 404);

        try {
            $analytics->record($request, $episode, filesize($file));
        } catch (QueryException $exception) {
            // A reporting outage must not stop someone downloading an episode.
            report($exception);
        }

        $path = implode('/', array_map('rawurlencode', explode(DIRECTORY_SEPARATOR, substr($file, strlen($root) + 1))));

        return response('', 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'private, no-store',
            'X-Accel-Expires' => '0',
            'X-Accel-Redirect' => '/_podcast_media/'.$path,
        ]);
    }
}
