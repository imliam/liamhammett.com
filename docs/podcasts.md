# Publishing podcasts

Podcast metadata lives in `content/podcasts/<slug>.md`. Episodes live in the flat `content/podcast_episodes/<guid>.md` folder. Commit Markdown, MP3s and artwork together. Podcast media is tracked under `public/media/podcasts/`; application storage remains ignored. MP3s use ordinary Git binary files, not Git LFS. GUIDs are permanent, including when you change a title or slug.

## Requirements

Use PHP 8.5 with XML, DOM, fileinfo and SQLite, and install `ffprobe` from FFmpeg on any machine running the audio commands or validation. The focused tests also use `ffmpeg` and PHP GD to generate fixtures.

```sh
ffprobe -version
```

The `public` disk writes directly to `public/media`. A Git checkout includes the committed podcast media, served at `/media/podcasts/<show>/<file>`. No `public/storage` symlink, shared-storage copy step or PHP streaming controller is needed. `APP_URL` must be the configured canonical public HTTPS origin, not a preview hostname. Feed and enclosure URLs must use that origin consistently. Audio URLs must stay stable. Retain previously published media when deploying an older release.

## Import Brain Dump

The repository already includes Brain Dump's imported metadata and media. To resume or repeat the import, back up content and storage first, then run:

```sh
php artisan podcast:import-transistor brain-dump
php artisan podcast:validate brain-dump
```

This command only imports `https://feeds.transistor.fm/dump`. It is not a general RSS fetcher. It follows HTTPS redirects only through the approved Transistor feed, audio and image hosts. The current source has seven episodes. The command imports every item in that feed, keeping the original show and episode GUIDs, publication instants, explicit settings, category and episode types. Dates are normalized to UTC. Simple HTML notes become Markdown with links, paragraphs, emphasis and lists retained. No Blade executes in podcast notes.

New shows use the earliest episode publication instant, not the channel's last-update date. Valid source `itunes:duration` values in seconds or `HH:MM:SS` are retained. They must agree with the probed duration within one second; otherwise the import fails. Missing or invalid source durations fall back to the measured duration.

Imports are resumable, not transactional. Existing GUIDs keep their edited metadata and notes. Existing audio files must match the source enclosure byte length; artwork must match the current source byte length. A mismatch fails instead of overwriting. Identical size does not prove identical content, so use the recorded SHA-256 checksums for backups. Files download to temporary storage and must match declared byte lengths before copying. A failure can leave successfully imported records and media behind; fix the reported problem and rerun. Do not delete or regenerate GUIDs to retry.

Original XML snapshots and file checksums live in `storage/app/podcast-imports/<slug>/feed-<sha256>.xml` and `manifest-<sha256>.json`. Audio manifest entries include the original episode GUID and UTC publication timestamp for migration comparisons. This folder is ignored by Git via `storage/app/.gitignore`. Include it in backups. Repeated runs against the same XML refresh the matching manifest, not existing content. A different source feed checksum creates a new snapshot.

The import does not change any Transistor or directory account. Do not redirect or cancel Transistor until a separately approved cutover. Confirm the new feed, all seven GUIDs and enclosure lengths, artwork, and production media delivery first. Then separately update Apple Podcasts, Spotify and Amazon Music using their owner tools. Add verified directory HTTPS URLs to the show's `links` map. Do not guess directory IDs.

## Create an episode

```sh
php artisan podcast:make-episode brain-dump 'A new episode'
# Use the UUID printed by the command, not the episode slug:
php artisan podcast:prepare-audio YOUR-EPISODE-UUID /absolute/path/episode.mp3
```

The first command writes a draft with an explicit `published_at: null`, editable summary/show-notes placeholders and empty audio defaults. Later Orbit saves may omit null fields; an absent publication field is still a draft. Duplicate episode slugs within a show fail. The audio command copies to `public/media/podcasts/<show>/<guid>.mp3`, records the exact bytes and MIME type, and rounds `ffprobe` duration to whole seconds, with a minimum of one second. It accepts MP3 audio only. Rerunning with identical bytes is safe; different bytes at an existing destination fail. Review any replacement deliberately rather than silently changing a published enclosure. Commit the generated MP3, any artwork and the episode Markdown together. Draft pages are hidden, but files under `public/` and tracked Git history are not private.

Edit the episode Markdown's summary, show notes and optional episode/season numbers. Keep `episode_type` as `full`, `trailer` or `bonus`. `explicit: null` inherits the show setting. Optional artwork overrides the show artwork. Artwork must be square JPEG or PNG, at least 1400 pixels on each side. Use a timestamp with an explicit UTC offset, for example `published_at: '2026-11-10T09:00:00+00:00'`. An omitted/null publication date is a draft; a future date is scheduled. Storage and feed timestamps use UTC, while human-readable dates display in `Europe/London`, including daylight saving time.

```sh
php artisan podcast:validate brain-dump
php artisan podcast:validate
```

Validation checks metadata for drafts too. Any dated show needs valid artwork, and any dated episode under a dated show needs valid audio before deployment, including future scheduled releases. A null-dated show's media and a null-dated episode's empty audio path, zero bytes and zero duration are intentional draft placeholders. Validation checks local files, not HTTP delivery. Commands rebuild the two Orbit model tables from Markdown before working, so manual edits are read even with a stale production cache. Run one publishing command at a time.

## Transcripts and chapters

Both are optional episode frontmatter fields. The Markdown body remains the show notes. For example, add this inside an episode's existing frontmatter:

```yaml
transcript: |
  **Liam:** Welcome to the show.

  This is the next paragraph of the transcript.
chapters:
  - startTime: 0
    title: Introduction
  - startTime: 63.5
    title: The main discussion
```

Write transcripts as Markdown, with speaker names where useful. They appear below the episode's show notes and at `/podcasts/<show>/episodes/<episode>/transcript`. Raw HTML and unsafe links are stripped. RSS advertises this HTML page with [`podcast:transcript`](https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/tags/transcript.md). This is an untimed transcript, not synchronized captions. There is no automatic transcription, and existing Brain Dump episodes have no transcript until one is supplied and reviewed.

Chapter `startTime` values are unquoted numbers of seconds from the start, including fractions. Titles must be nonempty, times nonnegative and strictly increasing, and each chapter must start before the episode ends. `podcast:validate` checks these rules, including for drafts with a known duration. The first chapter need not start at zero. Version one supports titles and times only, not chapter images or links.

Chapter links seek and start the native audio player. The current chapter is highlighted as playback progresses, including after seeking backward. Without JavaScript, the links open the MP3 at the chapter's media-fragment time. RSS links to `/podcasts/<show>/episodes/<episode>/chapters.json` using [`podcast:chapters`](https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/tags/chapters.md); the endpoint returns Podcasting 2.0 chapter JSON, version 1.2.0. Podcast-app support varies. Empty transcript/chapters fields create no RSS tags or resource pages. Drafts and scheduled episodes cannot be accessed through these endpoints before publication.

## Embed an episode

Open an episode page, expand **Embed this episode**, and copy the iframe HTML into another website. The public `/podcasts/<show>/episodes/<episode>/embed` page has a compact player, chapter navigation when available, and a transcript link. It does not load the site's navigation, Google Analytics or account/session middleware. It never autoplays. Episode and embed players use `preload="metadata"` on the direct media URL so browsers can show the duration before playback without recording a download. On the first Play, JavaScript switches to the tracked URL, preserving any chapter or seek position. Simply loading an iframe does not count a download. Browsers may decline metadata preloading, especially in data-saving modes, and playback without JavaScript uses the untracked media URL. The show listing retains `preload="none"` to avoid preloading every episode at once.

The default iframe is full-width and 400 pixels high. Adjust its height to suit the host website and long episode titles. Chapter lists scroll within the player. Keep the iframe `title` for accessibility. If your website adds `sandbox`, it must allow the player's scripts and same-origin resources for chapter controls to work.

Only the embed response sends `Content-Security-Policy: frame-ancestors *`, permitting cross-origin framing. Modern browsers use this directive instead of `X-Frame-Options: SAMEORIGIN`, so the site's other pages retain their existing frame protection. If the production web server or CDN adds another CSP with a restrictive `frame-ancestors`, make an exception for the `/podcasts/<show>/episodes/<episode>/embed` route only. Multiple CSP headers restrict together, rather than overriding each other. Do not remove frame protection site-wide. After deploying, check the actual headers and play an episode inside an iframe on a different origin; a direct visit to the embed URL does not prove embedding works.

## Add another show

Create `content/podcasts/another-show.md`, using a new permanent UUID. No database migration or code change is needed:

```yaml
---
slug: another-show
guid: 46a99090-9910-42cd-91cb-a55341e2a89c
title: Another show
description: Conversations about building software.
author: Liam Hammett
owner_email: liam@liamhammett.com
language: en
category: Technology
explicit: false
type: episodic
artwork: podcasts/another-show/artwork.jpg
published_at: null
copyright: Copyright Liam Hammett
links: {}
---
Notes about this show, written in Markdown.
```

Place artwork on the public disk, then use `podcast:make-episode another-show 'First episode'`. Set the show's publication date only when it is ready. Slugs use lowercase letters, numbers and single hyphens. The importer cannot import a different feed into this second show.

## Deploy and verify delivery

Deploy the application, Markdown and tracked `public/media/podcasts/` files from the same commit. No separate Transistor download or storage link is needed for a fresh checkout. The initial `/storage/podcasts/` layout was replaced because it depended on deployment-specific storage links. Current page and feed URLs use `/media/podcasts/`. Set production `APP_URL=https://liamhammett.com`, refresh cached filesystem configuration, rebuild Orbit's cache and validate:

```sh
php artisan config:cache
php artisan orbit:clear
php artisan orbit:cache
php artisan podcast:validate
```

Use your actual public feed and enclosure URLs for these checks:

```sh
curl -fI 'https://your-site.example/podcasts/brain-dump/feed.xml'
curl -fI 'https://your-site.example/media/podcasts/brain-dump/EPISODE-GUID.mp3'
curl -fsS -D - -o /dev/null -H 'Range: bytes=0-1023' \
  'https://your-site.example/media/podcasts/brain-dump/EPISODE-GUID.mp3'
```

Confirm HEAD returns 200, the feed has an RSS XML content type, and audio reports `audio/mpeg` and the exact `Content-Length`. The range request must return **206 Partial Content**, `Content-Range: bytes 0-1023/<total>`, and 1024 bytes. Configure the web server or CDN to serve `public/media/` directly with byte ranges and HEAD support. Avoid authentication, forced download interstitials or HTML error pages at media URLs.

Keep an off-server backup of the repository, plus the ignored `storage/app/podcast-imports/` snapshots and manifests. Check checksums and periodically restore a backup into a separate environment. Git includes committed media, but not uncommitted uploads or import snapshots. Directory submission, feed ownership verification, redirects and any Transistor cancellation remain separate follow-up work.

## PHP-side download analytics

Tracking is opt-in. With `PODCAST_X_ACCEL_REDIRECT=true`, feeds, website players and download links use the stable `/podcast-audio/<episode-guid>` endpoint. Laravel checks that the show and episode are published, records an eligible request, then sends `X-Accel-Redirect`. Nginx serves the MP3, including HEAD and byte ranges. PHP never streams the audio. GUIDs, enclosure lengths and media files do not change. Existing `/media/` URLs still work but bypass analytics, including URLs already cached by podcast apps.

### Enable on the production server

1. Back up the application database. It must persist between releases, independently of Orbit's rebuildable Markdown cache. Run `php artisan migrate --force` during deployment.
2. Add this location inside the site's existing Nginx `server` block. Replace the alias with the absolute **current release** media directory, keeping both trailing slashes. Do not replace the site's PHP-FPM configuration.

```nginx
location ^~ /_podcast_media/ {
    internal;
    alias /home/forge/liamhammett.com/current/public/media/;
}
```

3. Ensure `/podcast-audio/` reaches Laravel through the normal front controller. Do not cache that endpoint in Nginx, a reverse proxy or a CDN. Nginx must honor upstream `X-Accel-Redirect`, not ignore or strip it. Test the configuration with `nginx -t`, then reload Nginx through your server's deployment tooling.
4. Set `PODCAST_X_ACCEL_REDIRECT=true` and run `php artisan config:cache`. The default is `false`, so code-only deployments keep working without this Nginx rule. Keep the rule installed if tracking is later disabled, because apps may retain the tracked URLs. Disabling the flag makes those URLs return 503; re-enable it to restore them.
5. Check the new feed enclosure, HEAD, full GET, seeking and ranges. The public internal path `/_podcast_media/podcasts/...` must return 404. The tracked URL must return MP3 bytes, **not an empty 200** or a leaked `X-Accel-Redirect` header. A PHP development server alone cannot perform the handoff. The repository's Nginx preview supports it.
6. Run Laravel's scheduler every minute, normally `* * * * * cd /path/to/current && php artisan schedule:run >> /dev/null 2>&1`. The daily `podcast:prune-analytics` task deletes fingerprints older than 72 hours and daily counts older than 13 months. You can also run that command manually.

If PHP runs behind trusted proxies, configure Laravel's trusted proxy addresses to match the actual deployment so `Request::ip()` identifies the client. Never trust arbitrary forwarded headers from the Internet. Without correct proxy configuration many listeners can share the proxy's address, causing undercounting. The preview proxy deliberately does not trust user-supplied forwarded addresses.

### Read the counts

```sh
php artisan podcast:stats
php artisan podcast:stats brain-dump --days=90
```

The report totals estimated download requests per episode GUID across the selected UTC calendar days, including today. It is private CLI output, not a public analytics page. Database backups must include the two `podcast_download_*` tables. There is no historical backfill for audio downloaded before tracking was enabled.

A qualifying GET counts once per episode/client combination in a rolling 24-hour window. Concurrent requests claim that window with an atomic database write. The client key is an episode-specific HMAC of the IP address and user agent using `APP_KEY`; IPv6 addresses share a /64 key. No raw IP, raw user agent or durable cross-episode listener identifier is stored by this feature. Changing `APP_KEY` resets deduplication identities. Your web server or CDN may independently keep access logs; review their retention separately.

HEAD, empty user agents, obvious bots, conditional requests, malformed/multiple ranges, unsatisfiable ranges and explicit one/two-byte probes do not count. Other valid ranges can count, including a seek. Failed analytics queries are logged without blocking audio delivery. Monitor application errors so database failures do not silently leave gaps in reporting.

These are **estimated download requests**, not confirmed plays, unique people, bytes delivered, completion rates or IAB-certified downloads. PHP records the request before Nginx delivers the file, so interrupted downloads or a later Nginx failure can still count. Shared addresses, user-agent changes, bot detection and app-side caches affect the estimate. Spotify or other services that fetch once and redistribute can hide individual listeners entirely. Do not use these numbers as certified advertising measurements.
