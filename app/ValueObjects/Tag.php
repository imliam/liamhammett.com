<?php

namespace App\ValueObjects;

use App\Models\Article;
use Illuminate\Support\Str;

class Tag
{
    public function __construct(public string $name) {}

    public function getSlug(): string
    {
        return Str::slug($this->name);
    }

    public function getUrl(): string
    {
        return url('/tags/' . Str::slug($this->name));
    }

    /** @return Tag[] */
    public static function all(): array
    {
        $tags = [];

        Article::all()->each(function (Article $article) use ($tags) {
            foreach ($article->tags as $tag) {
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        });

        return array_map(fn (string $tagName) => new Tag($tagName), $tags);
    }
}
