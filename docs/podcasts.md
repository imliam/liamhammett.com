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
