<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Orbit\Concerns\Orbital;

class Podcast extends Model
{
    use Orbital;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $dateFormat = 'U';

    public static function schema(Blueprint $table): void
    {
        $table->string('slug')->primary();
        $table->string('guid')->unique();
        $table->string('title');
        $table->text('description');
        $table->string('author');
        $table->string('owner_email');
        $table->string('language')->default('en');
        $table->string('category')->default('Technology');
        $table->boolean('explicit')->default(false);
        $table->string('type')->default('episodic');
        $table->string('artwork');
        $table->unsignedBigInteger('published_at')->nullable();
        $table->string('copyright')->nullable();
        $table->json('links')->nullable();
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'explicit' => 'boolean', 'links' => 'array'];
    }

    public function setAttribute($key, $value)
    {
        // Orbit inserts the raw attributes into its SQLite cache in bulk.
        if ($key === 'published_at' && $value !== null && ! is_numeric($value)) {
            $value = Carbon::parse($value)->timestamp;
        }

        if ($key === 'links' && is_array($value)) {
            $this->attributes[$key] = json_encode($value, JSON_THROW_ON_ERROR);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class, 'podcast_slug', 'slug');
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now()->timestamp);
    }

    public function getUrl(): string
    {
        return rtrim(config('app.url'), '/').route('podcasts.show', ['podcast' => $this->slug], false);
    }

    public function getFeedUrl(): string
    {
        return rtrim(config('app.url'), '/').route('podcasts.feed', ['podcast' => $this->slug], false);
    }

    public function getArtworkUrl(): string
    {
        return url(Storage::disk('public')->url($this->artwork));
    }

    public function render(): string
    {
        return Str::markdown($this->content ?: $this->description, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
