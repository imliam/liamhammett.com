<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        config(['orbit.paths.content' => $this->directory]);
        DB::purge('orbit');
        Model::clearBootedModels();
        $this->travelTo(now()->setDate(2026, 5, 10)->setTime(12, 0));
    }

    protected function tearDown(): void
    {
        DB::purge('orbit');
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
        $this->get('/podcasts/another-show/feed.xml')->assertOk()->assertSee('other-guid')->assertDontSee('original-guid');
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
        $this->get('/podcasts/brain-dump/episodes/welcome')->assertOk()->assertSee('First show welcome')->assertDontSee('Second show welcome')->assertSee('preload="none"', false)->assertSee('download', false);
        $this->get('/podcasts/another-show/episodes/welcome')->assertOk()->assertSee('Second show welcome');
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
    }
}
