<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Orbit\Concerns\Orbital;

class PodcastEpisode extends Model
{
    use Orbital;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'guid';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $dateFormat = 'U';

    public static function schema(Blueprint $table): void
    {
        $table->string('guid')->primary();
        $table->string('podcast_slug');
        $table->string('slug');
        $table->string('title');
        $table->unsignedBigInteger('published_at')->nullable();
        $table->text('summary')->default('');
        $table->string('audio_path')->default('');
        $table->unsignedBigInteger('audio_bytes')->default(0);
        $table->string('audio_type')->default('audio/mpeg');
        $table->unsignedInteger('duration')->default(0);
        $table->unsignedInteger('episode_number')->nullable();
        $table->unsignedInteger('season_number')->nullable();
        $table->string('episode_type')->default('full');
        $table->boolean('explicit')->nullable();
        $table->string('artwork')->nullable();
        $table->text('transcript')->default('');
        $table->json('chapters')->nullable();
        $table->unique(['podcast_slug', 'slug']);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'explicit' => 'boolean',
            'audio_bytes' => 'integer',
            'duration' => 'integer',
            'episode_number' => 'integer',
            'season_number' => 'integer',
            'chapters' => 'array',
        ];
    }

    public function setAttribute($key, $value)
    {
        // Orbit's bulk import bypasses the constructor that initialises Eloquent casts.
        if ($key === 'published_at' && $value !== null && ! is_numeric($value)) {
            $value = Carbon::parse($value)->timestamp;
        }
        if ($key === 'chapters' && is_array($value)) {
            $this->attributes[$key] = json_encode($value, JSON_THROW_ON_ERROR);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class, 'podcast_slug', 'slug');
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now()->timestamp);
    }

    public function getUrl(): string
    {
        return rtrim(config('app.url'), '/').route('podcasts.episodes.show', ['podcast' => $this->podcast_slug, 'episode' => $this->slug], false);
    }

    public function getAudioUrl(bool $track = true): string
    {
        if ($track && config('podcasts.x_accel_redirect')) {
            return rtrim(config('app.url'), '/').route('podcasts.audio', ['episode' => $this->guid], false);
        }

        return url(Storage::disk('public')->url($this->audio_path));
    }

    public function getArtworkUrl(): string
    {
        return $this->artwork
            ? url(Storage::disk('public')->url($this->artwork))
            : $this->podcast->getArtworkUrl();
    }

    public function getTranscriptUrl(): string
    {
        return $this->getUrl().'/transcript';
    }

    public function getChaptersUrl(): string
    {
        return $this->getUrl().'/chapters.json';
    }

    public function getEmbedUrl(): string
    {
        return $this->getUrl().'/embed';
    }

    public function hasTranscript(): bool
    {
        return trim($this->transcript ?? '') !== '';
    }

    public function renderTranscript(): string
    {
        return Str::markdown($this->transcript ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function formattedDuration(): string
    {
        return $this->duration >= 3600
            ? sprintf('%d:%02d:%02d', intdiv($this->duration, 3600), intdiv($this->duration % 3600, 60), $this->duration % 60)
            : sprintf('%d:%02d', intdiv($this->duration, 60), $this->duration % 60);
    }

    public function render(): string
    {
        return Str::markdown($this->content ?: $this->summary, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
