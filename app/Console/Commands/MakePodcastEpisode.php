<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class MakePodcastEpisode extends PodcastCommand
{
    protected $signature = 'podcast:make-episode {podcast} {title}';

    protected $description = 'Create a draft podcast episode with a permanent UUID';

    protected function runPodcastCommand(): int
    {
        $show = Podcast::find($this->safeSlug($this->argument('podcast')));
        if (! $show) {
            throw new RuntimeException('Podcast not found. Create its Markdown metadata first.');
        }
        $title = trim($this->argument('title'));
        $slug = $this->safeSlug(Str::slug($title));
        if (PodcastEpisode::where('podcast_slug', $show->slug)->where('slug', $slug)->exists()) {
            throw new RuntimeException("Episode slug already exists: {$slug}");
        }
        $episode = new PodcastEpisode;
        $episode->forceFill([
            'guid' => (string) Str::uuid(), 'podcast_slug' => $show->slug,
            'slug' => $slug, 'title' => $title, 'published_at' => null,
            'summary' => 'Add a short episode summary.', 'audio_path' => '', 'audio_bytes' => 0,
            'audio_type' => 'audio/mpeg', 'duration' => 0,
            'episode_number' => null, 'season_number' => null,
            'episode_type' => 'full', 'explicit' => null, 'artwork' => null,
            'content' => 'Write episode show notes here.',
        ])->save();
        // Orbit omits nulls; make the publication switch visible to the editor.
        $path = PodcastEpisode::getOrbitalPath().'/'.$episode->guid.'.md';
        File::put($path, preg_replace('/\A---\R/', "---\npublished_at: null\n", File::get($path), 1));
        $this->info("Created draft {$episode->guid} ({$slug}). Add summary, show notes and audio before publishing.");

        return self::SUCCESS;
    }
}
