<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Carbon\CarbonImmutable;
use DOMDocument;
use DOMNode;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;

class ImportTransistorPodcast extends PodcastCommand
{
    protected $signature = 'podcast:import-transistor {podcast=brain-dump}';

    protected $description = 'Import the known Brain Dump Transistor feed without overwriting existing records or media';

    private const FEED = 'https://feeds.transistor.fm/dump';

    private const HOSTS = ['feeds.transistor.fm', 'media.transistor.fm', 'audio.transistor.fm', 'img.transistorcdn.com', 'img-upload-production.transistor.fm'];

    private const ITUNES = 'http://www.itunes.com/dtds/podcast-1.0.dtd';

    protected function runPodcastCommand(): int
    {
        $slug = $this->safeSlug($this->argument('podcast'));
        $temporary = $this->download(self::FEED);
        try {
            $xml = file_get_contents($temporary);
        } finally {
            unlink($temporary);
        }
        if (stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
            throw new RuntimeException('Feed DOCTYPE and entity declarations are not allowed.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $feed = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
            if ($feed === false || ! isset($feed->channel)) {
                throw new RuntimeException('Invalid podcast RSS XML.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $channel = $feed->channel;
        $itunes = $channel->children(self::ITUNES);
        $guid = trim((string) $channel->children('https://podcastindex.org/namespace/1.0')->guid);
        $this->safeGuid($guid);
        $show = Podcast::find($slug);
        if (($show && $show->guid !== $guid) || Podcast::where('guid', $guid)->where('slug', '!=', $slug)->exists()) {
            throw new RuntimeException('Show GUID already belongs to another slug, or target slug belongs to another show.');
        }
        $backup = storage_path('app/podcast-imports/'.$slug);
        File::ensureDirectoryExists($backup);
        $checksum = hash('sha256', $xml);
        File::put($backup.'/feed-'.$checksum.'.xml', $xml);
        $manifest = ['source' => self::FEED, 'podcast_guid' => $guid, 'feed_sha256' => $checksum, 'files' => []];
        $artUrl = (string) $itunes->image->attributes()?->href ?: (string) $channel->image->url;
        $artPath = $show?->artwork ?: "podcasts/{$slug}/artwork.jpg";
        $manifest['files'][] = $this->importMedia($artUrl, $artPath);
        $this->checkArtwork($artPath);
        if (! $show) {
            $firstPublished = null;
            foreach ($channel->item as $item) {
                $published = $this->date((string) $item->pubDate);
                if ($firstPublished === null || $published->lt($firstPublished)) {
                    $firstPublished = $published;
                }
            }
            $show = new Podcast;
            $show->forceFill([
                'slug' => $slug, 'guid' => $guid, 'title' => trim((string) $channel->title),
                'description' => trim((string) $channel->description),
                'author' => trim((string) $itunes->author), 'owner_email' => trim((string) $itunes->owner->email),
                'language' => trim((string) $channel->language), 'category' => (string) $itunes->category->attributes()?->text,
                'explicit' => $this->explicit((string) $itunes->explicit), 'type' => (string) $itunes->type ?: 'episodic',
                'artwork' => $artPath, 'published_at' => $firstPublished,
                'copyright' => trim((string) $channel->copyright), 'links' => [], 'content' => '',
            ])->save();
        }
        $seen = [];
        foreach ($channel->item as $item) {
            $id = $this->safeGuid(trim((string) $item->guid));
            if (isset($seen[$id])) {
                throw new RuntimeException("Duplicate source GUID: {$id}");
            }
            $seen[$id] = true;
            $existing = PodcastEpisode::find($id);
            if ($existing && $existing->podcast_slug !== $slug) {
                throw new RuntimeException("Episode GUID belongs to another podcast: {$id}");
            }
            $sourceBytes = filter_var((string) $item->enclosure['length'], FILTER_VALIDATE_INT);
            if ($sourceBytes === false || $sourceBytes <= 0 || (string) $item->enclosure['type'] !== 'audio/mpeg') {
                throw new RuntimeException("Invalid source enclosure metadata: {$id}");
            }
            $path = $existing?->audio_path ?: "podcasts/{$slug}/{$id}.mp3";
            $published = $this->date((string) $item->pubDate);
            $manifest['files'][] = $this->importMedia((string) $item->enclosure['url'], $path, $sourceBytes) + [
                'guid' => $id, 'published_at' => $published->toIso8601String(),
            ];
            if ($existing) {
                $this->line("Kept existing episode {$id}.");

                continue;
            }
            $it = $item->children(self::ITUNES);
            $episodeSlug = Str::slug(basename(parse_url((string) $item->link, PHP_URL_PATH) ?: (string) $item->title));
            $episodeSlug = $this->safeSlug($episodeSlug);
            if (PodcastEpisode::where('podcast_slug', $slug)->where('slug', $episodeSlug)->exists()) {
                throw new RuntimeException("Episode slug collision: {$episodeSlug}");
            }
            $html = (string) $item->children('http://purl.org/rss/1.0/modules/content/')->encoded;
            $notes = $this->markdown($html ?: (string) $item->description);
            $metadata = $this->probe($this->mediaPath($path));
            $sourceDuration = $this->duration((string) $it->duration);
            if ($sourceDuration !== null) {
                if (abs($metadata['duration'] - $sourceDuration) > 1) {
                    throw new RuntimeException("Source duration differs from the local MP3 by more than one second: {$id}");
                }
                $metadata['duration'] = $sourceDuration;
            }
            $episodeArtwork = null;
            $episodeArtUrl = (string) $it->image->attributes()?->href;
            if ($episodeArtUrl !== '') {
                $episodeArtwork = "podcasts/{$slug}/{$id}.jpg";
                $manifest['files'][] = $this->importMedia($episodeArtUrl, $episodeArtwork);
                $this->checkArtwork($episodeArtwork);
            }
            $episode = new PodcastEpisode;
            $episode->forceFill($metadata + [
                'guid' => $id, 'podcast_slug' => $slug, 'slug' => $episodeSlug,
                'title' => trim((string) $item->title), 'published_at' => $published,
                'summary' => trim(strip_tags((string) $item->description)), 'content' => $notes,
                'audio_path' => $path, 'episode_number' => (string) $it->episode !== '' ? (int) $it->episode : null,
                'season_number' => (string) $it->season !== '' ? (int) $it->season : null,
                'episode_type' => (string) $it->episodeType ?: 'full',
                'explicit' => (string) $it->explicit !== '' ? $this->explicit((string) $it->explicit) : null,
                'artwork' => $episodeArtwork,
            ])->save();
            $this->line("Imported {$id}.");
            File::put($backup.'/manifest-'.$checksum.'.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }
        File::put($backup.'/manifest-'.$checksum.'.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $this->info('Import complete. Source feed and SHA-256 manifest saved in '.$backup);

        return self::SUCCESS;
    }

    private function date(string $date): CarbonImmutable
    {
        if (trim($date) === '') {
            throw new RuntimeException('Missing source publication date.');
        }

        return CarbonImmutable::parse($date)->utc();
    }

    private function explicit(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['yes', 'true', 'explicit', '1'], true);
    }

    private function duration(string $value): ?int
    {
        $value = trim($value);
        if (ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }
        if (preg_match('/\A(\d+):([0-5]\d):([0-5]\d)\z/', $value, $parts)) {
            $seconds = (int) $parts[1] * 3600 + (int) $parts[2] * 60 + (int) $parts[3];

            return $seconds > 0 ? $seconds : null;
        }

        return null;
    }

    private function download(string $url, ?int $expected = null): string
    {
        $temporary = tempnam(sys_get_temp_dir(), 'podcast-');
        try {
            for ($redirects = 0; $redirects <= 5; $redirects++) {
                $parts = parse_url($url);
                if (! $parts || ($parts['scheme'] ?? '') !== 'https' || ! in_array(strtolower($parts['host'] ?? ''), self::HOSTS, true) || isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && $parts['port'] !== 443)) {
                    throw new RuntimeException("Unapproved download URL: {$url}");
                }
                $response = Http::withOptions(['allow_redirects' => false, 'stream' => true, 'decode_content' => false])->connectTimeout(20)->timeout(300)->get($url);
                if (in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                    $location = $response->header('Location');
                    if (! $location) {
                        throw new RuntimeException('Redirect is missing Location.');
                    }
                    $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));

                    continue;
                }
                $response->throw();
                if ($response->status() !== 200) {
                    throw new RuntimeException('Download must return HTTP 200.');
                }
                $source = $response->toPsrResponse()->getBody();
                if ($source->isSeekable()) {
                    $source->rewind();
                }
                $destination = fopen($temporary, 'wb');
                try {
                    while (! $source->eof()) {
                        $chunk = $source->read(1024 * 1024);
                        if (fwrite($destination, $chunk) !== strlen($chunk)) {
                            throw new RuntimeException('Could not write the downloaded temporary file.');
                        }
                    }
                } finally {
                    fclose($destination);
                }
                clearstatcache(true, $temporary);
                $length = $response->header('Content-Length');
                if (($length !== '' && (! ctype_digit($length) || filesize($temporary) !== (int) $length)) || ($expected !== null && filesize($temporary) !== $expected)) {
                    throw new RuntimeException('Downloaded byte length does not match source metadata.');
                }

                return $temporary;
            }
            throw new RuntimeException('Too many download redirects.');
        } catch (\Throwable $exception) {
            unlink($temporary);
            throw $exception;
        }
    }

    private function importMedia(string $url, string $path, ?int $expected = null): array
    {
        $local = $this->mediaPath($path);
        $disk = Storage::disk('public');
        if ($disk->exists($path) && $expected !== null) {
            if ($disk->size($path) !== $expected) {
                throw new RuntimeException("Existing local size differs from source: {$path}");
            }
        } else {
            $temporary = $this->download($url, $expected);
            try {
                if ($disk->exists($path)) {
                    if ($disk->size($path) !== filesize($temporary)) {
                        throw new RuntimeException("Existing local size differs from source: {$path}");
                    }
                } else {
                    $stream = fopen($temporary, 'rb');
                    try {
                        if (! $disk->put($path, $stream)) {
                            throw new RuntimeException("Could not store {$path}");
                        }
                    } finally {
                        fclose($stream);
                    }
                    if (hash_file('sha256', $temporary) !== hash_file('sha256', $local)) {
                        $disk->delete($path);
                        throw new RuntimeException("Stored checksum mismatch: {$path}");
                    }
                }
            } finally {
                unlink($temporary);
            }
        }

        return ['source' => $url, 'path' => $path, 'bytes' => $disk->size($path), 'sha256' => hash_file('sha256', $local)];
    }

    private function markdown(string $html): string
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
        $render = function (DOMNode $node) use (&$render): string {
            if ($node->nodeType === XML_TEXT_NODE) {
                return preg_replace('/([\\\\`*_\[\]<>])/', '\\\\$1', $node->textContent);
            }
            $text = '';
            foreach ($node->childNodes as $child) {
                $text .= $render($child);
            }

            return match (strtolower($node->nodeName)) {
                'script', 'style' => '',
                'p', 'div', 'ul', 'ol' => trim($text)."\n\n",
                'br' => "  \n",
                'em', 'i' => '*'.$text.'*',
                'strong', 'b' => '**'.$text.'**',
                'li' => '- '.trim($text)."\n",
                'a' => preg_match('~^https?://~i', $node->getAttribute('href')) ? '['.$text.'](<'.str_replace(['<', '>'], ['%3C', '%3E'], $node->getAttribute('href')).'>)' : $text,
                default => $text,
            };
        };

        return trim($render($document->getElementsByTagName('body')->item(0)));
    }
}
