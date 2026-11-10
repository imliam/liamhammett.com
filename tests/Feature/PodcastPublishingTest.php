<?php

namespace Tests\Feature;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PodcastPublishingTest extends TestCase
{
    private string $temporary;

    public function createApplication()
    {
        $this->temporary = sys_get_temp_dir().'/podcast-publishing-'.bin2hex(random_bytes(8));
        mkdir($this->temporary.'/framework/views', 0777, true);
        mkdir($this->temporary.'/public/media', 0777, true);
        mkdir($this->temporary.'/app/public', 0777, true);
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $app->useStoragePath($this->temporary);
        $app->usePublicPath($this->temporary.'/public');
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['orbit.paths.content' => $this->temporary.'/content', 'orbit.paths.cache' => $this->temporary.'/orbit']);
        Podcast::clearBootedModels();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        File::deleteDirectory($this->temporary);
    }

    public function test_draft_creation_is_safe_and_preserves_a_unique_permanent_guid(): void
    {
        $this->show();
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => 'A title: with YAML # characters'])->assertSuccessful();
        $episode = PodcastEpisode::firstOrFail();
        $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $episode->guid);
        $this->assertNull($episode->published_at);
        $this->assertSame('', $episode->audio_path);
        $this->assertSame('A title: with YAML # characters', $episode->title);
        $this->assertFileExists($this->temporary.'/content/podcast_episodes/'.$episode->guid.'.md');
        $this->assertStringContainsString('published_at: null', file_get_contents($this->temporary.'/content/podcast_episodes/'.$episode->guid.'.md'));
        $this->assertNotEmpty($episode->summary);
        $this->assertNotEmpty($episode->content);
        $this->artisan('podcast:validate', ['podcast' => 'test-show'])->assertSuccessful();
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => $episode->title])->assertFailed();
        $this->assertSame(1, PodcastEpisode::count());
        $this->artisan('podcast:make-episode', ['podcast' => '../escape', 'title' => 'No'])->assertFailed();
        $this->artisan('podcast:make-episode', ['podcast' => 'missing', 'title' => 'No'])->assertFailed();
    }

    public function test_prepare_audio_measures_real_mp3_and_refuses_to_replace_different_audio(): void
    {
        $this->show();
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => 'Episode'])->assertSuccessful();
        $episode = PodcastEpisode::firstOrFail();
        $file = $this->mp3();
        $arguments = ['guid' => $episode->guid, 'file' => $file];
        $this->artisan('podcast:prepare-audio', $arguments)->assertSuccessful();
        $episode->refresh();
        $this->assertSame(filesize($file), $episode->audio_bytes);
        $this->assertSame('audio/mpeg', $episode->audio_type);
        $this->assertSame(2, $episode->duration);
        $this->assertSame(file_get_contents($file), Storage::disk('public')->get($episode->audio_path));
        $this->artisan('podcast:prepare-audio', $arguments)->assertSuccessful();
        file_put_contents($this->temporary.'/not-audio.mp3', 'not audio');
        $this->artisan('podcast:prepare-audio', ['guid' => $episode->guid, 'file' => $this->temporary.'/not-audio.mp3'])->assertFailed();
        $different = $this->mp3('different.mp3', 3);
        $this->artisan('podcast:prepare-audio', ['guid' => $episode->guid, 'file' => $different])->assertFailed();
        $this->assertSame(file_get_contents($file), Storage::disk('public')->get($episode->audio_path));
    }

    public function test_validation_checks_dated_media_and_detects_tampering(): void
    {
        $show = $this->show();
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => 'Episode'])->assertSuccessful();
        $episode = PodcastEpisode::firstOrFail();
        $episode->forceFill(['published_at' => now()->subDay(), 'summary' => 'Summary'])->save();
        $this->artisan('podcast:validate')->assertSuccessful(); // Private show, no public media yet.
        $show->published_at = now()->subDay();
        $show->save();
        $this->artisan('podcast:validate')->assertFailed();
        Storage::disk('public')->put('podcasts/test-show/art.jpg', $this->art());
        $this->artisan('podcast:prepare-audio', ['guid' => $episode->guid, 'file' => $this->mp3()])->assertSuccessful();
        $this->artisan('podcast:validate')->assertSuccessful();
        $episode->refresh();
        Storage::disk('public')->put($episode->audio_path, 'truncated');
        $this->artisan('podcast:validate')->assertFailed();
    }

    public function test_prepared_media_is_directly_public_without_a_storage_symlink(): void
    {
        $show = $this->show();
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => 'Episode'])->assertSuccessful();
        $episode = PodcastEpisode::firstOrFail();
        $source = $this->mp3();
        $this->artisan('podcast:prepare-audio', ['guid' => $episode->guid, 'file' => $source])->assertSuccessful();
        Storage::disk('public')->put($show->artwork, $this->art());
        $episode->refresh();

        // A static web server resolves these URLs beneath public/, not shared storage.
        foreach ([Storage::disk('public')->url($episode->audio_path) => file_get_contents($source), $show->getArtworkUrl() => Storage::disk('public')->get($show->artwork)] as $url => $bytes) {
            $path = public_path(ltrim(parse_url($url, PHP_URL_PATH), '/'));
            $this->assertFileExists($path);
            $this->assertSame($bytes, file_get_contents($path));
        }
        $this->assertFileDoesNotExist(public_path('storage'));
    }

    public function test_scheduled_media_must_exist_before_release_but_draft_placeholders_are_allowed(): void
    {
        $show = $this->show();
        $show->published_at = now()->addDays(2);
        $show->save();
        Storage::disk('public')->put($show->artwork, $this->art());
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => 'Scheduled episode'])->assertSuccessful();
        $this->artisan('podcast:validate')->assertSuccessful();
        $episode = PodcastEpisode::firstOrFail();
        $episode->forceFill([
            'published_at' => now()->addDays(3),
            'audio_path' => 'podcasts/test-show/missing.mp3',
        ])->save();
        $this->artisan('podcast:validate')->expectsOutputToContain('missing or unreadable')->assertFailed();
    }

    public function test_chapter_validation_requires_ordered_numeric_times_and_nonempty_titles(): void
    {
        $this->show();
        $this->artisan('podcast:make-episode', ['podcast' => 'test-show', 'title' => 'Episode'])->assertSuccessful();
        $episode = PodcastEpisode::firstOrFail();
        $episode->forceFill([
            'duration' => 90,
            'transcript' => '**Liam:** A Markdown transcript.',
            'chapters' => [['startTime' => 0, 'title' => 'Intro'], ['startTime' => 89.5, 'title' => 'Conclusion']],
        ])->save();
        $this->artisan('podcast:validate')->assertSuccessful();
        foreach ([
            [['startTime' => 90, 'title' => 'Beyond the end']],
            [['startTime' => -1, 'title' => 'Negative']],
            [['startTime' => 5, 'title' => 'First'], ['startTime' => 5, 'title' => 'Duplicate']],
            [['startTime' => 15, 'title' => 'Later'], ['startTime' => 2, 'title' => 'Earlier']],
            [['startTime' => '12.5', 'title' => 'String instead of JSON number']],
            [['startTime' => 0, 'title' => '  ']],
            [['startTime' => 0]],
            ['not a chapter'],
        ] as $chapters) {
            $episode->chapters = $chapters;
            $episode->save();
            $this->artisan('podcast:validate')->assertFailed();
        }
    }

    public function test_import_preserves_guids_instants_notes_and_existing_edits_on_repeat(): void
    {
        $audio = file_get_contents($this->mp3());
        $art = $this->art();
        Http::fake([
            'https://feeds.transistor.fm/dump' => Http::response($this->feed(strlen($audio))),
            'https://img.transistorcdn.com/art.jpg' => Http::response($art, 200, ['Content-Length' => strlen($art)]),
            'https://media.transistor.fm/audio.mp3' => Http::response('', 302, ['Location' => 'https://audio.transistor.fm/audio.mp3']),
            'https://audio.transistor.fm/audio.mp3' => Http::response($audio, 200, ['Content-Length' => strlen($audio)]),
        ]);
        $this->artisan('podcast:import-transistor', ['podcast' => 'test-show'])->assertSuccessful();
        $show = Podcast::firstOrFail();
        $episode = PodcastEpisode::firstOrFail();
        $this->assertSame('original-show-guid', $show->guid);
        $this->assertSame('original-episode-guid', $episode->guid);
        $this->assertSame('2025-07-23T15:00:00+00:00', $episode->published_at->toIso8601String());
        $this->assertSame($episode->published_at->timestamp, $show->published_at->timestamp);
        $this->assertSame(3, $episode->duration); // Source is one second above rounded ffprobe duration.
        $this->assertSame('bonus', $episode->episode_type);
        $this->assertTrue($episode->explicit);
        $this->assertSame('Technology', $show->category);
        $this->assertStringContainsString('[Arc](<https://arc.net/>)', $episode->content);
        $this->assertStringContainsString('**good**', $episode->content);
        $this->assertStringNotContainsString('<p>', $episode->content);
        $this->assertStringContainsString('href="https://arc.net/"', $episode->render());
        $episode->content = 'My edited notes';
        $episode->save();
        $show->title = 'Edited show';
        $show->save();
        $before = file_get_contents($this->temporary.'/content/podcast_episodes/original-episode-guid.md');
        $this->artisan('podcast:import-transistor', ['podcast' => 'test-show'])->assertSuccessful();
        $this->assertSame($before, file_get_contents($this->temporary.'/content/podcast_episodes/original-episode-guid.md'));
        $this->assertSame('Edited show', Podcast::firstOrFail()->title);
        $this->assertCount(1, glob($this->temporary.'/app/podcast-imports/test-show/manifest-*.json'));
        $manifest = json_decode(file_get_contents(glob($this->temporary.'/app/podcast-imports/test-show/manifest-*.json')[0]), true);
        $this->assertSame(hash('sha256', $audio), $manifest['files'][1]['sha256']);
        $this->assertSame($episode->guid, $manifest['files'][1]['guid']);
        $this->assertSame('2025-07-23T15:00:00+00:00', $manifest['files'][1]['published_at']);
        Storage::disk('public')->put($episode->audio_path, 'truncated');
        $this->artisan('podcast:import-transistor', ['podcast' => 'test-show'])->expectsOutputToContain('Existing local size differs')->assertFailed();
        $this->assertSame('truncated', Storage::disk('public')->get($episode->audio_path));
    }

    public function test_import_rejects_untrusted_redirects_doctypes_and_truncated_downloads(): void
    {
        Http::fakeSequence('https://feeds.transistor.fm/dump')
            ->push('', 302, ['Location' => 'https://evil.example/feed'])
            ->push('<!DOCTYPE rss [<!ENTITY x SYSTEM "file:///etc/passwd">]><rss/>')
            ->push($this->feed(100));
        Http::fake([
            'https://img.transistorcdn.com/art.jpg' => Http::response($this->art()),
            'https://media.transistor.fm/audio.mp3' => Http::response('short'),
        ]);
        $this->artisan('podcast:import-transistor')->expectsOutputToContain('Unapproved download URL')->assertFailed();
        $this->artisan('podcast:import-transistor')->expectsOutputToContain('DOCTYPE')->assertFailed();
        $this->artisan('podcast:import-transistor')->expectsOutputToContain('byte length')->assertFailed();
        Storage::disk('public')->assertMissing('podcasts/brain-dump/original-episode-guid.mp3');
        $this->assertSame(0, PodcastEpisode::count());
    }

    private function show(): Podcast
    {
        return Podcast::create([
            'slug' => 'test-show', 'guid' => 'test-show-guid', 'title' => 'Test show',
            'description' => 'A test show', 'author' => 'Test Author', 'owner_email' => 'author@example.com',
            'language' => 'en', 'category' => 'Technology', 'explicit' => false, 'type' => 'episodic',
            'artwork' => 'podcasts/test-show/art.jpg', 'published_at' => null, 'links' => [], 'content' => '',
        ]);
    }

    private function mp3(string $name = 'source.mp3', int $duration = 2): string
    {
        $file = $this->temporary.'/'.$name;
        $process = new Process(['ffmpeg', '-v', 'error', '-f', 'lavfi', '-i', 'sine=frequency=440:duration='.$duration, '-c:a', 'libmp3lame', '-y', $file]);
        $process->mustRun();

        return $file;
    }

    private function art(): string
    {
        $image = imagecreatetruecolor(1400, 1400);
        ob_start();
        imagejpeg($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    private function feed(int $bytes): string
    {
        return <<<XML
        <rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:podcast="https://podcastindex.org/namespace/1.0"><channel>
        <title>Original show</title><description>Description</description><language>en</language><pubDate>Fri, 30 Oct 2026 06:00:10 +0000</pubDate>
        <podcast:guid>original-show-guid</podcast:guid><itunes:author>Author</itunes:author><itunes:owner><itunes:email>author@example.com</itunes:email></itunes:owner>
        <itunes:category text="Technology"/><itunes:type>episodic</itunes:type><itunes:explicit>No</itunes:explicit><itunes:image href="https://img.transistorcdn.com/art.jpg"/>
        <item><guid>original-episode-guid</guid><title>Episode</title><link>https://braindump.transistor.fm/episodes/episode</link>
        <pubDate>Wed, 23 Jul 2025 16:00:00 +0100</pubDate><description><![CDATA[<p>Try <a href="https://arc.net/">Arc</a>.</p><p>It is <strong>good</strong>.</p>]]></description>
        <enclosure url="https://media.transistor.fm/audio.mp3" length="{$bytes}" type="audio/mpeg"/><itunes:duration>00:00:03</itunes:duration><itunes:episodeType>bonus</itunes:episodeType><itunes:explicit>Yes</itunes:explicit><itunes:episode>1</itunes:episode></item>
        </channel></rss>
        XML;
    }
}
