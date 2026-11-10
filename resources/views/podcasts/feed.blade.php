@php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; @endphp
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:podcast="https://podcastindex.org/namespace/1.0">
    <channel>
        <title>{{ $podcast->title }}</title>
        <link>{{ $podcast->getUrl() }}</link>
        <description>{{ $podcast->description }}</description>
        <language>{{ $podcast->language }}</language>
        @if ($podcast->copyright)
            <copyright>{{ $podcast->copyright }}</copyright>
        @endif
        <atom:link href="{{ $podcast->getFeedUrl() }}" rel="self" type="application/rss+xml" />
        <itunes:new-feed-url>{{ $podcast->getFeedUrl() }}</itunes:new-feed-url>
        <podcast:guid>{{ $podcast->guid }}</podcast:guid>
        <itunes:author>{{ $podcast->author }}</itunes:author>
        <itunes:owner>
            <itunes:name>{{ $podcast->author }}</itunes:name>
            <itunes:email>{{ $podcast->owner_email }}</itunes:email>
        </itunes:owner>
        <itunes:category text="{{ $podcast->category }}" />
        <itunes:type>{{ $podcast->type }}</itunes:type>
        <itunes:explicit>{{ $podcast->explicit ? 'true' : 'false' }}</itunes:explicit>
        <itunes:image href="{{ $podcast->getArtworkUrl() }}" />
        <image>
            <url>{{ $podcast->getArtworkUrl() }}</url>
            <title>{{ $podcast->title }}</title>
            <link>{{ $podcast->getUrl() }}</link>
        </image>
        @foreach ($episodes as $episode)
            <item>
                <title>{{ $episode->title }}</title>
                <guid isPermaLink="false">{{ $episode->guid }}</guid>
                <link>{{ $episode->getUrl() }}</link>
                <pubDate>{{ $episode->published_at->toRssString() }}</pubDate>
                <description>{{ $episode->summary }}</description>
                <content:encoded>{{ $episode->render() }}</content:encoded>
                <enclosure url="{{ $episode->getAudioUrl() }}" length="{{ $episode->audio_bytes }}" type="{{ $episode->audio_type }}" />
                <itunes:duration>{{ $episode->duration }}</itunes:duration>
                @if ($episode->hasTranscript())
                    <podcast:transcript url="{{ $episode->getTranscriptUrl() }}" type="text/html" />
                @endif
                @if ($episode->chapters)
                    <podcast:chapters url="{{ $episode->getChaptersUrl() }}" type="application/json+chapters" />
                @endif
                <itunes:episodeType>{{ $episode->episode_type }}</itunes:episodeType>
                <itunes:explicit>{{ ($episode->explicit ?? $podcast->explicit) ? 'true' : 'false' }}</itunes:explicit>
                @if ($episode->episode_number !== null)
                    <itunes:episode>{{ $episode->episode_number }}</itunes:episode>
                @endif
                @if ($episode->season_number !== null)
                    <itunes:season>{{ $episode->season_number }}</itunes:season>
                @endif
                @if ($episode->artwork)
                    <itunes:image href="{{ $episode->getArtworkUrl() }}" />
                @endif
            </item>
        @endforeach
    </channel>
</rss>
