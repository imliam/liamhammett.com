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
        return Article::query()
            ->published()
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $tagName) => new Tag($tagName));
    }
}
