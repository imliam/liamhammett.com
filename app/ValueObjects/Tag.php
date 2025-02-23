<?php

namespace App\ValueObjects;

use App\Models\Article;
use Illuminate\Support\Collection;
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
        return url('/tags/' . $this->getSlug());
    }

    public static function all(): Collection
    {
        $tags = [];

        foreach (Article::query()->published()->get() as $article) {
            foreach ($article->tags as $tag) {
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        return collect($tags)
            ->filter()
            ->sort()
            ->map(fn (string $tagName) => new Tag($tagName));
    }
}
