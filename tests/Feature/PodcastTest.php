<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class PodcastTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/podcast-web-'.Str::uuid();
        File::ensureDirectoryExists($this->directory.'/podcasts');
        File::ensureDirectoryExists($this->directory.'/podcast_episodes');
        config(['orbit.paths.content' => $this->directory, 'podcasts.x_accel_redirect' => false]);
        DB::purge('orbit');
        Model::clearBootedModels();
        $this->travelTo(now()->setDate(2026, 5, 10)->setTime(12, 0));
    }

    protected function tearDown(): void
    {
        DB::purge('orbit');
        DB::purge('podcast_test');
        Model::clearBootedModels();
        File::deleteDirectory($this->directory);
        $this->travelBack();
        parent::tearDown();
    }

    public function test_each_show_has_a_separate_feed_with_stable_identity_and_valid_xml(): void
    {
        config([
            'app.url' => 'https://podcasts.example.com/',
            'filesystems.disks.public.url' => 'https://podcasts.example.com/media',
        ]);
        $this->show('brain-dump', ['explicit' => true]);
        $this->show('another-show');
        $this->episode('original-guid', 'brain-dump', [
            'title' => 'Soup & cookies 🧠',
            'published_at' => '2025-07-23T16:00:00+01:00',
            'summary' => 'A < B & C > D',
            'explicit' => false,
            'episode_number' => 6,
            'season_number' => 2,
        ], 'Notes with **emphasis**, & and ]]> plus <script>alert(1)</script>.');
        $this->episode('other-guid', 'another-show', ['title' => 'Other show only']);

        $response = $this->get('/podcasts/brain-dump/feed.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $this->assertFalse($response->headers->has('Set-Cookie'));
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertCount(1, $xml->channel->item);
        $item = $xml->channel->item;
        $this->assertSame('original-guid', (string) $item->guid);
        $this->assertSame('false', (string) $item->guid['isPermaLink']);
        $this->assertSame('Soup & cookies 🧠', (string) $item->title);
        $this->assertSame('A < B & C > D', (string) $item->description);
        $this->assertSame('Wed, 23 Jul 2025 15:00:00 +0000', (string) $item->pubDate);
        $this->assertSame('https://podcasts.example.com/media/podcasts/brain-dump/original-guid.mp3', (string) $item->enclosure['url']);
        $this->assertSame('123456', (string) $item->enclosure['length']);
        $this->assertSame('audio/mpeg', (string) $item->enclosure['type']);
        $itunes = $item->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
        $this->assertSame('false', (string) $itunes->explicit);
        $this->assertSame('6', (string) $itunes->episode);
        $this->assertSame('2', (string) $itunes->season);
        $this->assertSame('full', (string) $itunes->episodeType);
        $this->assertSame('125', (string) $itunes->duration);
        $notes = (string) $item->children('http://purl.org/rss/1.0/modules/content/')->encoded;
        $this->assertStringContainsString('<strong>emphasis</strong>', $notes);
        $this->assertStringNotContainsString('<script>', $notes);
        $this->assertSame('show-brain-dump', (string) $xml->channel->children('https://podcastindex.org/namespace/1.0')->guid);
        $this->assertSame('https://podcasts.example.com/podcasts/brain-dump/feed.xml', (string) $xml->channel->children('http://www.itunes.com/dtds/podcast-1.0.dtd')->{'new-feed-url'});
        $this->assertCount(0, $item->children('https://podcastindex.org/namespace/1.0')->transcript);
        $this->assertCount(0, $item->children('https://podcastindex.org/namespace/1.0')->chapters);
        $this->get('/podcasts/another-show/feed.xml')->assertOk()->assertSee('other-guid')->assertDontSee('original-guid');
    }

    public function test_markdown_transcripts_and_fractional_chapters_are_available_on_the_site_and_in_rss(): void
    {
        config(['app.url' => 'https://podcasts.example.com']);
        $this->show('brain-dump');
        $chapters = [['startTime' => 0, 'title' => 'Introduction'], ['startTime' => 63.5, 'title' => 'Soup & cookies']];
        $this->episode('one', 'brain-dump', [
            'transcript' => "**Liam:** Hello there.\n\n<script>alert(1)</script> [unsafe](javascript:alert(1))",
            'chapters' => $chapters,
        ]);
        $base = 'https://podcasts.example.com/podcasts/brain-dump/episodes/welcome';
        $xml = simplexml_load_string($this->get('/podcasts/brain-dump/feed.xml')->assertOk()->getContent());
        $metadata = $xml->channel->item->children('https://podcastindex.org/namespace/1.0');
        $this->assertSame($base.'/transcript', (string) $metadata->transcript->attributes()['url']);
        $this->assertSame('text/html', (string) $metadata->transcript->attributes()['type']);
        $this->assertSame($base.'/chapters.json', (string) $metadata->chapters->attributes()['url']);
        $this->assertSame('application/json+chapters', (string) $metadata->chapters->attributes()['type']);
        $this->assertFalse(isset($metadata->transcript->attributes()['rel'])); // Untimed prose is not captions.

        $this->get('/podcasts/brain-dump/episodes/welcome')->assertOk()
            ->assertSee('<strong>Liam:</strong> Hello there.', false)
            ->assertSee('data-chapter-start="63.5"', false)->assertSee('01:03')
            ->assertDontSee('<script>alert', false)->assertDontSee('href="javascript:', false);
        $this->get('/podcasts/brain-dump/episodes/welcome/transcript')->assertOk()
            ->assertSee('<strong>Liam:</strong> Hello there.', false)->assertDontSee('<script>alert', false)
            ->assertDontSee('href="javascript:', false);
        $response = $this->get('/podcasts/brain-dump/episodes/welcome/chapters.json')->assertOk()
            ->assertHeader('Content-Type', 'application/json+chapters')->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertExactJson(['version' => '1.2.0', 'chapters' => $chapters]);
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }

    public function test_embeds_have_safe_copyable_markup_and_use_the_same_tracked_audio_without_site_navigation(): void
    {
        $this->enableAnalytics();
        config(['filesystems.disks.public.url' => 'https://podcasts.example.com/media']);
        $this->show('brain-dump');
        $this->episode('one', 'brain-dump', ['title' => 'A "quoted" & <special> title']);
        $page = $this->get('/podcasts/brain-dump/episodes/welcome')->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($page->getContent());
        $markup = $document->getElementById('episode-embed-code')->textContent;
        $iframeDocument = new \DOMDocument;
        @$iframeDocument->loadHTML($markup);
        $iframe = $iframeDocument->getElementsByTagName('iframe')->item(0);
        $this->assertSame('Listen to A "quoted" & <special> title', $iframe->getAttribute('title'));
        $this->assertSame('https://podcasts.example.com/podcasts/brain-dump/episodes/welcome/embed', $iframe->getAttribute('src'));
        $response = $this->get('/podcasts/brain-dump/episodes/welcome/embed')->assertOk()
            ->assertHeader('Content-Security-Policy', 'frame-ancestors *')
            ->assertSee('https://podcasts.example.com/podcast-audio/one', false)
            ->assertSee('preload="metadata"', false)->assertDontSee('autoplay', false)
            ->assertDontSee('jelly-nav', false)->assertDontSee('googletagmanager', false);
        $this->assertFalse($response->headers->has('Set-Cookie'));
        foreach ([$page, $response] as $playerPage) {
            $playerDocument = new \DOMDocument;
            @$playerDocument->loadHTML($playerPage->getContent());
            $audio = $playerDocument->getElementsByTagName('audio')->item(0);
            $this->assertSame('metadata', $audio->getAttribute('preload'));
            $this->assertSame('https://podcasts.example.com/media/podcasts/brain-dump/one.mp3', $audio->getElementsByTagName('source')->item(0)->getAttribute('src'));
            $this->assertSame('https://podcasts.example.com/podcast-audio/one', $audio->getAttribute('data-playback-url'));
        }
        $this->assertSame(0, DB::table('podcast_download_counts')->count());
    }

    public function test_public_pages_scope_episodes_to_their_show_and_hide_unpublished_content(): void
    {
        $this->show('brain-dump');
        $this->show('another-show');
        $this->show('secret-show', ['published_at' => null]);
        $this->show('future-show', ['published_at' => '2027-01-01T00:00:00Z']);
        $this->episode('one', 'brain-dump', ['title' => 'First show welcome']);
        $this->episode('two', 'another-show', ['title' => 'Second show welcome']);
        $this->episode('draft', 'brain-dump', ['slug' => 'draft', 'published_at' => null]);
        $this->episode('future', 'brain-dump', ['slug' => 'future', 'published_at' => '2026-05-10T12:01:00Z']);
        $this->episode('private', 'secret-show');

        $this->get('/podcasts')->assertOk()->assertSee('brain-dump')->assertSee('another-show')->assertDontSee('secret-show')->assertDontSee('future-show');
        $this->get('/podcasts/brain-dump')->assertOk()->assertSee('First show welcome')->assertDontSee('Second show welcome')->assertDontSee('/episodes/draft')->assertDontSee('/episodes/future');
        $this->get('/podcasts/brain-dump/episodes/welcome')->assertOk()->assertSee('First show welcome')->assertDontSee('Second show welcome')->assertSee('preload="metadata"', false)->assertSee('download', false);
        $this->get('/podcasts/another-show/episodes/welcome')->assertOk()->assertSee('Second show welcome');
        foreach (['transcript', 'chapters.json', 'embed'] as $suffix) {
            foreach (['secret-show/episodes/welcome', 'brain-dump/episodes/draft', 'brain-dump/episodes/future', 'another-show/episodes/draft', 'missing/episodes/welcome'] as $path) {
                $this->get('/podcasts/'.$path.'/'.$suffix)->assertNotFound();
            }
        }
        $this->get('/podcasts/brain-dump/episodes/welcome/transcript')->assertNotFound();
        $this->get('/podcasts/brain-dump/episodes/welcome/chapters.json')->assertNotFound();
        foreach (['/podcasts/secret-show', '/podcasts/secret-show/feed.xml', '/podcasts/secret-show/episodes/welcome', '/podcasts/future-show', '/podcasts/brain-dump/episodes/draft', '/podcasts/brain-dump/episodes/future', '/podcasts/missing/feed.xml'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    public function test_feed_validators_change_when_an_episode_reaches_its_publication_time(): void
    {
        $this->show('brain-dump');
        $this->episode('published', 'brain-dump');
        $this->episode('scheduled', 'brain-dump', ['slug' => 'scheduled', 'published_at' => '2026-05-10T12:01:00Z']);

        $response = $this->get('/podcasts/brain-dump/feed.xml')->assertOk()->assertDontSee('scheduled');
        $etag = $response->headers->get('ETag');
        $this->assertNotEmpty($etag);
        $this->withHeader('If-None-Match', $etag)->get('/podcasts/brain-dump/feed.xml')->assertStatus(304);
        $this->travel(1)->minutes();
        $this->withHeader('If-None-Match', $etag)->get('/podcasts/brain-dump/feed.xml')->assertOk()->assertSee('scheduled');
    }

    public function test_empty_shows_have_a_useful_page_but_no_submittable_feed(): void
    {
        $this->show('empty');
        $this->get('/podcasts/empty')->assertOk()->assertSee('The first episode is on its way.');
        $this->get('/podcasts/empty/feed.xml')->assertNotFound();
    }

    public function test_tracking_switches_feed_player_and_download_urls_and_hands_audio_to_nginx(): void
    {
        $this->enableAnalytics();
        $this->show('brain-dump');
        $this->episode('original-guid', 'brain-dump');
        $url = 'https://podcasts.example.com/podcast-audio/original-guid';
        $this->get('/podcasts/brain-dump/feed.xml')->assertOk()->assertSee($url, false)
            ->assertSee('<guid isPermaLink="false">original-guid</guid>', false);
        $this->get('/podcasts/brain-dump/episodes/welcome')->assertOk()->assertSee($url, false);
        $response = $this->get('/podcast-audio/original-guid')->assertOk()
            ->assertHeader('Content-Type', 'audio/mpeg')
            ->assertHeader('X-Accel-Redirect', '/_podcast_media/podcasts/brain-dump/original-guid.mp3')
            ->assertHeader('X-Accel-Expires', '0');
        $this->assertSame('', $response->getContent());
        $this->assertFalse($response->headers->has('Set-Cookie'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->head('/podcast-audio/original-guid')->assertOk()->assertHeader('X-Accel-Redirect');
        $this->assertSame(1, DB::table('podcast_download_counts')->sum('requests'));
    }

    public function test_download_windows_are_rolling_and_separate_for_each_episode_and_client(): void
    {
        $this->enableAnalytics();
        $this->show('brain-dump');
        $this->show('second-show');
        $this->episode('one', 'brain-dump');
        $this->episode('two', 'second-show');
        $this->travelTo(now()->setTime(23, 59, 30));
        $this->get('/podcast-audio/one')->assertOk();
        $this->travel(60)->seconds(); // Crossing midnight is not a new rolling window.
        $this->withHeader('Range', 'bytes=50-')->get('/podcast-audio/one')->assertOk();
        $this->travel(86339)->seconds();
        $this->get('/podcast-audio/one')->assertOk(); // 24 hours minus one second.
        $this->assertSame(1, DB::table('podcast_download_counts')->sum('requests'));
        $this->travel(1)->seconds();
        $this->get('/podcast-audio/one')->assertOk();
        $this->assertSame(2, DB::table('podcast_download_counts')->sum('requests'));
        $this->get('/podcast-audio/two')->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.2'])->get('/podcast-audio/one')->assertOk();
        $this->withHeader('User-Agent', 'Different Podcast App/2')->get('/podcast-audio/one')->assertOk();
        $this->assertSame(5, DB::table('podcast_download_counts')->sum('requests'));

        $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:abcd:1234::1'])->get('/podcast-audio/one')->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:abcd:1234::abcd'])->get('/podcast-audio/one')->assertOk();
        $this->assertSame(6, DB::table('podcast_download_counts')->sum('requests'));
        $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:abcd:1235::1'])->get('/podcast-audio/one')->assertOk();
        $this->assertSame(7, DB::table('podcast_download_counts')->sum('requests'));
        $this->assertCount(6, DB::table('podcast_download_windows')->get());
        foreach (DB::table('podcast_download_windows')->get() as $window) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $window->fingerprint);
        }
    }

    public function test_checks_bots_invalid_ranges_and_conditional_requests_do_not_count(): void
    {
        $this->enableAnalytics();
        $this->show('brain-dump');
        $this->episode('one', 'brain-dump');
        $this->head('/podcast-audio/one')->assertOk();
        foreach ([
            ['User-Agent' => ''], ['User-Agent' => 'Googlebot/1'], ['User-Agent' => 'curl/8.0'],
            ['Range' => 'bytes=0-1'], ['Range' => 'bytes=30-30'], ['Range' => 'bytes=-2'],
            ['Range' => 'bytes=999-'], ['Range' => 'bytes=20-10'], ['Range' => 'bytes=-'],
            ['Range' => 'invalid'], ['Range' => 'bytes=0-10,20-30'],
            ['If-None-Match' => '"some-etag"'], ['If-Modified-Since' => 'Mon, 04 May 2026 12:00:00 GMT'],
        ] as $headers) {
            $this->flushHeaders()->withHeaders($headers + ['User-Agent' => 'Pocket Casts/1'])
                ->get('/podcast-audio/one')->assertOk();
        }
        $this->assertSame(0, DB::table('podcast_download_counts')->count());
        foreach (['bytes=0-2', 'bytes=-3', 'bytes=125-', 'bytes=0-999'] as $index => $range) {
            $this->flushHeaders()->withHeaders(['User-Agent' => 'Pocket Casts/'.$index, 'Range' => $range])
                ->get('/podcast-audio/one')->assertOk();
        }
        $this->assertSame(4, DB::table('podcast_download_counts')->sum('requests'));
    }

    public function test_audio_rejects_unpublished_missing_and_outside_disk_files_but_survives_analytics_failure(): void
    {
        $this->enableAnalytics();
        $this->show('brain-dump');
        $this->show('private', ['published_at' => null]);
        $this->show('scheduled', ['published_at' => '2027-01-01T00:00:00Z']);
        $this->episode('draft', 'brain-dump', ['slug' => 'draft', 'published_at' => null]);
        $this->episode('future', 'brain-dump', ['slug' => 'future', 'published_at' => '2027-01-01T00:00:00Z']);
        $this->episode('hidden', 'private');
        $this->episode('scheduled', 'scheduled');
        $this->episode('escape', 'brain-dump', ['slug' => 'escape', 'audio_path' => '../outside.mp3']);
        File::put($this->directory.'/outside.mp3', 'not a public MP3');
        $this->episode('symlink', 'brain-dump', ['slug' => 'symlink']);
        Storage::disk('public')->delete('podcasts/brain-dump/symlink.mp3');
        symlink($this->directory.'/outside.mp3', Storage::disk('public')->path('podcasts/brain-dump/symlink.mp3'));
        $this->episode('missing-file', 'brain-dump', ['slug' => 'missing-file']);
        Storage::disk('public')->delete('podcasts/brain-dump/missing-file.mp3');
        $this->episode('one', 'brain-dump');
        foreach (['draft', 'future', 'hidden', 'scheduled', 'escape', 'symlink', 'missing-file', 'unknown'] as $guid) {
            $this->get('/podcast-audio/'.$guid)->assertNotFound();
        }
        $this->assertSame(0, DB::table('podcast_download_counts')->count());
        Schema::drop('podcast_download_counts');
        $this->get('/podcast-audio/one')->assertOk()->assertHeader('X-Accel-Redirect');
        $this->assertSame(0, DB::table('podcast_download_windows')->count()); // The claim rolled back too.
    }

    public function test_stats_filter_by_show_and_date_and_pruning_preserves_current_windows(): void
    {
        $this->enableAnalytics();
        DB::table('podcast_download_counts')->insert([
            ['podcast_slug' => 'brain-dump', 'episode_guid' => 'one', 'date' => '2026-05-10', 'requests' => 3],
            ['podcast_slug' => 'brain-dump', 'episode_guid' => 'one', 'date' => '2026-05-09', 'requests' => 7],
            ['podcast_slug' => 'other', 'episode_guid' => 'two', 'date' => '2026-05-10', 'requests' => 19],
            ['podcast_slug' => 'brain-dump', 'episode_guid' => 'old', 'date' => '2025-04-09', 'requests' => 100],
            ['podcast_slug' => 'brain-dump', 'episode_guid' => 'boundary', 'date' => '2025-04-10', 'requests' => 2],
        ]);
        $this->artisan('podcast:stats', ['podcast' => 'brain-dump', '--days' => 1])->expectsOutput('Total: 3')->assertSuccessful();
        $this->artisan('podcast:stats', ['--days' => 2])->expectsOutput('Total: 29')->assertSuccessful();
        $this->artisan('podcast:stats', ['--days' => 0])->assertFailed();
        DB::table('podcast_download_windows')->insert([
            ['fingerprint' => 'old', 'counted_at' => now()->timestamp - 3 * 86400 - 1],
            ['fingerprint' => 'boundary', 'counted_at' => now()->timestamp - 3 * 86400],
            ['fingerprint' => 'recent', 'counted_at' => now()->timestamp - 86400],
        ]);
        $this->artisan('podcast:prune-analytics')->assertSuccessful();
        $this->assertSame(['boundary', 'recent'], DB::table('podcast_download_windows')->orderBy('fingerprint')->pluck('fingerprint')->all());
        $this->assertSame(4, DB::table('podcast_download_counts')->count());
    }

    private function enableAnalytics(): void
    {
        config([
            'podcasts.x_accel_redirect' => true,
            'app.url' => 'https://podcasts.example.com',
            'filesystems.disks.public.root' => $this->directory.'/media',
            'database.connections.podcast_test' => ['driver' => 'sqlite', 'database' => ':memory:'],
        ]);
        Storage::forgetDisk('public');
        DB::setDefaultConnection('podcast_test');
        (require database_path('migrations/2026_11_10_000000_create_podcast_download_analytics_tables.php'))->up();
        $this->withHeaders(['User-Agent' => 'Pocket Casts/1']);
    }

    private function show(string $slug, array $attributes = []): void
    {
        $data = array_replace([
            'slug' => $slug, 'guid' => 'show-'.$slug, 'title' => $slug,
            'description' => 'A podcast about code.', 'author' => 'Liam Hammett',
            'owner_email' => 'podcast@example.com', 'artwork' => 'podcasts/'.$slug.'/cover.jpg',
            'published_at' => '2024-01-01T00:00:00Z', 'links' => ['Apple Podcasts' => 'https://podcasts.apple.com/example'],
        ], $attributes);
        File::put($this->directory.'/podcasts/'.$slug.'.md', "---\n".Yaml::dump($data)."---\n\nA podcast about code.\n");
    }

    private function episode(string $guid, string $podcast, array $attributes = [], string $notes = 'Episode notes.'): void
    {
        $data = array_replace([
            'guid' => $guid, 'podcast_slug' => $podcast, 'slug' => 'welcome', 'title' => 'Welcome',
            'published_at' => '2024-10-14T19:00:15+01:00', 'summary' => 'An introduction.',
            'audio_path' => 'podcasts/'.$podcast.'/'.$guid.'.mp3', 'audio_bytes' => 123456,
            'duration' => 125,
        ], $attributes);
        File::put($this->directory.'/podcast_episodes/'.$guid.'.md', "---\n".Yaml::dump($data)."---\n\n".$notes."\n");
        File::ensureDirectoryExists($this->directory.'/media/podcasts/'.$podcast);
        File::put($this->directory.'/media/podcasts/'.$podcast.'/'.$guid.'.mp3', str_repeat('a', 128));
    }
}
