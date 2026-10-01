<?php

use App\Models\Article;
use App\Utilities\JellyOpengraphImage;
use App\ValueObjects\Tag;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $articlesByYear = Article::query()->published()->orderByDesc('published_at')->get()->groupBy(fn (Article $article) => $article->published_at->year)->all();

    if (app()->environment('local')) {
        foreach (Article::query()->unpublished()->orderByDesc('published_at')->get() as $article) {
            if (!isset($articlesByYear['Draft'])) {
                $articlesByYear = ['Draft' => collect()] + $articlesByYear;
            }

            $articlesByYear['Draft'][] = $article;
        }
    }

    return view('home', [
        'articlesByYear' => $articlesByYear,
        'talks' => config('talks'),
        'videos' => config('videos'),
    ]);
});

Route::view('/cv', 'cv');
Route::redirect('.well-known/avatar', '/images/avatar.jpg');

Route::get('/tags', function () {
    return view('tag-list', [
        'tags' => Tag::all(),
    ]);
});

Route::get('/tags/{tag}', function (string $tag) {
    $articles = Article::query()->published()->get()
        ->filter(
            fn (Article $article) => collect($article->getTags())->contains(
                fn (Tag $articleTag) => $articleTag->getSlug() === $tag
            )
        );

    if ($articles->isEmpty()) {
        abort(404);
    }

    $tag = collect($articles->first()->getTags())->first(fn (Tag $articleTag) => $articleTag->getSlug() === $tag);

    return view('tags', [
        'tag' => $tag,
        'articlesByYear' => $articles->sortByDesc('published_at')->groupBy(fn (Article $article) => $article->published_at->year)
    ]);
});

Route::feeds();

require_once __DIR__ . '/redirects.php';

Route::get('/opengraph.png', function () {
    $path = public_path('images/opengraph/jelly/_site.png');

    if (! app()->environment('local') && file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    $png = (new JellyOpengraphImage(
        title: "Hi, I'm Liam",
        kicker: 'PHP & Laravel developer',
        description: 'I talk about code and stuff - writing, videos and conference talks.',
        sticker: '<?php',
    ))->save($path);

    return response($png)->header('Content-Type', 'image/png');
});

Route::get('/{article:slug}.png', function (Article $article) {
    $path = public_path($article->getOpengraphImageLocalPath());

    if (! app()->environment('local') && file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    $png = (new JellyOpengraphImage(
        title: $article->getAlternateTitle(),
        kicker: $article->type ?: 'article',
        date: $article->published_at?->format('j F Y'),
        description: $article->synopsis ?: null,
        sticker: collect($article->getTags())->first()?->name,
    ))->save($path);

    return response($png)->header('Content-Type', 'image/png');
});

Route::get('/{article:slug}.html', function (Article $article) {
    return $article->render();
});

Route::get('/{article:slug}.txt', function (Article $article) {
    // strip tags from $article->render() but make any <a> tags into the format "text (link)" in plaintext
    $renderedContent = $article->render();

    $renderedContent = preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1[^>]*>(.*?)<\/a>/i', function ($matches) {
        $linkText = $matches[3];
        $linkUrl = $matches[2];
        return "$linkText ($linkUrl)";
    }, $renderedContent);

    $renderedContent = strip_tags($renderedContent);

    return response($renderedContent)->header('Content-Type', 'text/plain');
});

Route::get('/{article:slug}', function (Article $article) {
    return view('article', [
        'article' => $article
    ]);
});
