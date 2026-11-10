<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class ValidatePodcasts extends PodcastCommand
{
    protected $signature = 'podcast:validate {podcast?}';

    protected $description = 'Validate podcast metadata and locally stored published or scheduled media';

    protected function runPodcastCommand(): int
    {
        $slug = $this->argument('podcast');
        $shows = $slug ? Podcast::where('slug', $this->safeSlug($slug))->get() : Podcast::all();
        if ($shows->isEmpty()) {
            throw new RuntimeException('No matching podcasts found.');
        }
        $errors = [];
        $seenGuids = [];
        foreach ($shows as $show) {
            try {
                $this->safeSlug($show->slug);
                $this->safeGuid($show->guid);
                $this->validateFields($show->toArray(), [
                    'title' => 'required|string', 'description' => 'required|string',
                    'author' => 'required|string', 'owner_email' => 'required|email',
                    'language' => ['required', 'regex:/^[a-z]{2,3}(-[A-Za-z]{2,4})?$/'],
                    'category' => 'required|string', 'explicit' => 'required|boolean',
                    'type' => 'required|in:episodic,serial', 'links' => 'nullable|array',
                ]);
                foreach ($show->links ?? [] as $label => $url) {
                    if (! is_string($label) || trim($label) === '' || ! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
                        throw new RuntimeException('Directory links need labels and HTTPS URLs.');
                    }
                }
                if (isset($seenGuids[$show->guid]) || Podcast::where('guid', $show->guid)->count() > 1) {
                    throw new RuntimeException('Duplicate show GUID.');
                }
                $seenGuids[$show->guid] = true;
                $showDated = $show->published_at !== null;
                if ($showDated) {
                    $this->checkArtwork($show->artwork ?? '');
                }
                $slugs = [];
                foreach ($show->episodes()->get() as $episode) {
                    try {
                        $this->safeGuid($episode->guid);
                        $this->safeSlug($episode->slug);
                        if (isset($slugs[$episode->slug])) {
                            throw new RuntimeException('Duplicate episode slug.');
                        }
                        $slugs[$episode->slug] = true;
                        $this->validateFields($episode->toArray(), [
                            'title' => 'required|string', 'episode_type' => 'required|in:full,trailer,bonus',
                            'episode_number' => 'nullable|integer|min:1', 'season_number' => 'nullable|integer|min:1',
                            'explicit' => 'nullable|boolean',
                            'transcript' => 'nullable|string',
                            'chapters' => 'nullable|array|list',
                            'chapters.*' => 'required|array:startTime,title',
                            'chapters.*.startTime' => 'required|numeric|min:0',
                            'chapters.*.title' => 'required|string',
                        ]);
                        $previous = -1;
                        foreach ($episode->chapters ?? [] as $chapter) {
                            $start = $chapter['startTime'];
                            if ((! is_int($start) && ! is_float($start)) || ! is_finite((float) $start) || $start <= $previous || ($episode->duration > 0 && $start >= $episode->duration)) {
                                throw new RuntimeException('Chapter startTime must be a number in seconds, strictly increasing and before the end of the episode.');
                            }
                            $previous = $start;
                        }
                        if ($showDated && $episode->published_at !== null) {
                            if (trim($episode->summary ?? '') === '') {
                                throw new RuntimeException('Published or scheduled episode needs a summary.');
                            }
                            $actual = $this->probe($this->mediaPath($episode->audio_path ?? ''));
                            if ($actual['audio_bytes'] !== $episode->audio_bytes || $actual['audio_type'] !== $episode->audio_type || abs($actual['duration'] - $episode->duration) > 1 || $episode->duration <= 0) {
                                throw new RuntimeException('Stored audio bytes, type or duration do not match the local MP3.');
                            }
                            if ($episode->artwork) {
                                $this->checkArtwork($episode->artwork);
                            }
                        }
                    } catch (Throwable $exception) {
                        $errors[] = "{$show->slug}/{$episode->guid}: {$exception->getMessage()}";
                    }
                }
            } catch (Throwable $exception) {
                $errors[] = "{$show->slug}: {$exception->getMessage()}";
            }
        }
        if (! $slug) {
            foreach (PodcastEpisode::all() as $episode) {
                if (! $shows->contains('slug', $episode->podcast_slug)) {
                    $errors[] = "{$episode->guid}: Unknown podcast {$episode->podcast_slug}.";
                }
            }
        }
        foreach ($errors as $error) {
            $this->error($error);
        }
        if ($errors) {
            return self::FAILURE;
        }
        $this->info('Podcast metadata and published/scheduled media are valid. Draft media placeholders were not checked.');

        return self::SUCCESS;
    }

    private function validateFields(array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new RuntimeException(implode(' ', $validator->errors()->all()));
        }
    }
}
