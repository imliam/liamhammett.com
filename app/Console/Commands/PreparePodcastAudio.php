<?php

namespace App\Console\Commands;

use App\Models\PodcastEpisode;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PreparePodcastAudio extends PodcastCommand
{
    protected $signature = 'podcast:prepare-audio {guid} {file}';

    protected $description = 'Copy a local MP3 to persistent public storage and measure its metadata';

    protected function runPodcastCommand(): int
    {
        $episode = PodcastEpisode::find($this->safeGuid($this->argument('guid')));
        if (! $episode) {
            throw new RuntimeException('Episode GUID not found.');
        }
        $source = realpath($this->argument('file'));
        if ($source === false) {
            throw new RuntimeException('Local audio file not found.');
        }
        $metadata = $this->probe($source);
        $path = 'podcasts/'.$this->safeSlug($episode->podcast_slug).'/'.$episode->guid.'.mp3';
        $this->mediaPath($path);
        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            if (hash_file('sha256', $source) !== hash_file('sha256', $this->mediaPath($path))) {
                throw new RuntimeException('Destination already exists with different audio. Refusing to overwrite it.');
            }
        } else {
            $stream = fopen($source, 'rb');
            try {
                if (! $disk->put($path, $stream)) {
                    throw new RuntimeException('Could not copy audio to public storage.');
                }
            } finally {
                fclose($stream);
            }
            if (hash_file('sha256', $source) !== hash_file('sha256', $this->mediaPath($path))) {
                $disk->delete($path);
                throw new RuntimeException('Copied audio checksum mismatch.');
            }
        }
        $episode->forceFill($metadata + ['audio_path' => $path])->save();
        $this->info("Prepared {$path}: {$metadata['audio_bytes']} bytes, {$metadata['duration']} seconds, {$metadata['audio_type']}.");

        return self::SUCCESS;
    }
}
