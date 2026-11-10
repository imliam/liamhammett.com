<!doctype html>
<html lang="{{ $podcast->language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="referrer" content="no-referrer">
    <title>{{ $episode->title }} · {{ $podcast->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/podcast-player.js'])
</head>
<body class="dir-jelly">
    <main class="jelly-page podcast-embed">
        <header class="podcast-embed-head">
            <img src="{{ $episode->getArtworkUrl() }}" alt="" width="1400" height="1400">
            <div>
                <h1><a href="{{ $episode->getUrl() }}" target="_blank" rel="noopener">{{ $episode->title }}</a></h1>
                <a href="{{ $podcast->getUrl() }}" target="_blank" rel="noopener" class="podcast-text-link">{{ $podcast->title }}</a>
            </div>
        </header>
        @include('podcasts.player')
        @if ($episode->hasTranscript())
            <a class="podcast-text-link" href="{{ $episode->getTranscriptUrl() }}" target="_blank" rel="noopener">Read transcript</a>
        @endif
    </main>
</body>
</html>
