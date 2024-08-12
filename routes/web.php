<?php

use App\Models\Article;
use App\ValueObjects\Tag;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $articlesByYear = Article::query()->whereNotNull('published_at')->get()->sortByDesc('published_at')->groupBy(fn (Article $article) => $article->published_at->year)->all();

    if (app()->environment('local')) {
        foreach (Article::query()->whereNull('published_at')->get() as $article) {
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

Route::get('/tags/{tag}', function (string $tag) {
    $articles = Article::whereNotNull('published_at')->get()
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

Route::get('/{article:slug}', function (Article $article) {
    return view('article', [
        'article' => $article
    ]);
});