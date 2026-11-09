<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

abstract class PodcastCommand extends Command
{
    public function handle(): int
    {
        try {
            // Re-read Markdown even when an older Orbit cache exists in production.
            (new Podcast)->migrate();
            (new PodcastEpisode)->migrate();

            return $this->runPodcastCommand();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    abstract protected function runPodcastCommand(): int;

    protected function safeSlug(string $slug): string
    {
        if (! preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug)) {
            throw new RuntimeException('Use a lowercase slug containing only letters, numbers and single hyphens.');
        }

        return $slug;
    }

    protected function safeGuid(string $guid): string
    {
        if (! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9_-]{0,199}\z/', $guid)) {
            throw new RuntimeException('GUID cannot safely be used as a Markdown filename.');
        }

        return $guid;
    }

    protected function mediaPath(string $path): string
    {
        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '\\') || preg_match('~(^|/)\.\.?(/|$)|:|[\x00-\x1f]~', $path)) {
            throw new RuntimeException("Unsafe public disk path: {$path}");
        }

        $local = Storage::disk('public')->path($path);
        $root = realpath(Storage::disk('public')->path(''));
        $ancestor = $local;
        while (! file_exists($ancestor) && ! is_link($ancestor) && dirname($ancestor) !== $ancestor) {
            $ancestor = dirname($ancestor);
        }
        $resolved = realpath($ancestor);
        if ($resolved === false || $root === false || ($resolved !== $root && ! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR))) {
            throw new RuntimeException("Media path escapes the public disk: {$path}");
        }

        return $local;
    }

    /** @return array{audio_bytes: int, audio_type: string, duration: int} */
    protected function probe(string $file): array
    {
        if (! is_file($file) || ! is_readable($file)) {
            throw new RuntimeException("Audio file is missing or unreadable: {$file}");
        }

        $process = new Process(['ffprobe', '-v', 'error', '-show_entries', 'format=format_name,duration:stream=codec_type,codec_name', '-of', 'json', '-i', $file]);
        $process->setTimeout(60);
        $process->mustRun();
        $data = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        $audio = array_filter($data['streams'] ?? [], fn ($stream) => ($stream['codec_type'] ?? '') === 'audio' && ($stream['codec_name'] ?? '') === 'mp3');
        $duration = (float) ($data['format']['duration'] ?? 0);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file);
        if (($data['format']['format_name'] ?? '') !== 'mp3' || ! $audio || ! is_finite($duration) || $duration <= 0 || $mime !== 'audio/mpeg') {
            throw new RuntimeException('Audio must be a readable MP3 with a positive duration and audio/mpeg MIME type.');
        }

        return ['audio_bytes' => filesize($file), 'audio_type' => $mime, 'duration' => max(1, (int) round($duration))];
    }

    protected function checkArtwork(string $path): void
    {
        $image = @getimagesize($this->mediaPath($path));
        if (! $image || $image[0] !== $image[1] || $image[0] < 1400 || ! in_array($image['mime'], ['image/jpeg', 'image/png'], true)) {
            throw new RuntimeException("Artwork must be a square JPEG or PNG at least 1400px: {$path}");
        }
    }
}
