<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $podcast = Podcast::published()->where('slug', $podcast)->firstOrFail();

        return view('podcasts.episode', [
            'podcast' => $podcast,
            'episode' => $podcast->episodes()->published()->where('slug', $episode)->firstOrFail(),
        ]);
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
}
