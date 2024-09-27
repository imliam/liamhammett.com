<?php

use App\Models\Article;
use App\Utilities\OpengraphImageLayout;
use App\ValueObjects\Tag;
use Illuminate\Support\Facades\Route;
use SimonHamp\TheOg\BorderPosition;

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
    ]);
});

Route::view('/cv', 'cv');

Route::get('/tags/{tag}', function (string $tag) {
    $articles = Article::query()->published()->get()
        ->filter(
            fn (Article $article) => collect($article->getTags())->contains(
                fn (Tag $articleTag) => $articleTag->getSlug() === $tag
            )
        );

    $tag = collect($articles->first()->getTags())->first(fn (Tag $articleTag) => $articleTag->getSlug() === $tag);

    return view('tags', [
        'tag' => $tag,
        'articlesByYear' => $articles->sortByDesc('published_at')->groupBy(fn (Article $article) => $article->published_at->year)
    ]);
});

Route::feeds();

require_once __DIR__ . '/redirects.php';

Route::get('/{article:slug}.png', function (Article $article) {
    if (!app()->environment('local') && $article->hasOpengraphImage()) {
        return response(file_get_contents($article->getOpengraphImageLocalPath()))->header('Content-Type', 'image/png');
    }

    if (!file_exists(dirname($article->getOpengraphImageLocalPath()))) {
        mkdir(dirname($article->getOpengraphImageLocalPath()), 0777, true);
    }

    $image = (new SimonHamp\TheOg\Image())
        ->accentColor('#f97316')
        ->url($article->getUrl())
        ->title($article->getAlternateTitle())
        ->description($article->synopsis ?? '')
        ->background(new SimonHamp\TheOg\Theme\Background(storage_path('opengraph-background.png')))
        ->layout(new OpengraphImageLayout)
        ->border(BorderPosition::None)
        ->save($article->getOpengraphImageLocalPath());

    return response($image->toString())->header('Content-Type', 'image/png');
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
