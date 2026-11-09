<x-page :title="$episode->title">
    <x-slot name="metaTags">
        @include('podcasts.meta', ['title' => $episode->title, 'description' => $episode->summary, 'canonical' => $episode->getUrl(), 'artwork' => $episode->getArtworkUrl()])
    </x-slot>
    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')
            <main class="jelly-wrap podcast-main podcast-episode-page">
                <a class="jelly-back" href="{{ $podcast->getUrl() }}"><span aria-hidden="true">←</span> {{ $podcast->title }}</a>
                <header class="podcast-episode-head">
                    <h1 class="jelly-article-title">{{ $episode->title }}</h1>
                    <div class="podcast-meta">
                        @if ($episode->episode_number !== null)<span>Episode {{ $episode->episode_number }}</span>@endif
                        <time datetime="{{ $episode->published_at->toIso8601String() }}">{{ $episode->published_at->copy()->timezone('Europe/London')->format('j F Y') }}</time>
                        <span>{{ $episode->formattedDuration() }}</span>
                        <span>With {{ $podcast->author }}</span>
                    </div>
                </header>
                <div class="podcast-listening">
                    <img src="{{ $episode->getArtworkUrl() }}" alt="{{ $podcast->title }} cover art" width="1400" height="1400">
                    <div>
                        <p class="podcast-listen-label">Listen to this episode</p>
                        <audio controls preload="none" aria-label="Listen to {{ $episode->title }}">
                            <source src="{{ $episode->getAudioUrl() }}" type="{{ $episode->audio_type }}">
                            <a href="{{ $episode->getAudioUrl() }}">Download this episode</a>
                        </audio>
                        <a class="podcast-text-link podcast-download" href="{{ $episode->getAudioUrl() }}" download>Download MP3 <span>({{ number_format($episode->audio_bytes / 1000000, 1) }} MB)</span></a>
                    </div>
                </div>
                <section class="podcast-notes" aria-labelledby="notes-heading">
                    <h2 class="jelly-h2" id="notes-heading">Show notes</h2>
                    <div class="prose jelly-prose">{!! $episode->render() !!}</div>
                </section>
                <aside class="podcast-follow" aria-label="Follow the podcast">
                    <h2 class="podcast-show-title">More {{ $podcast->title }}</h2>
                    <p>Listen in your favourite podcast app, or subscribe with the RSS feed.</p>
                    @include('podcasts.subscribe')
                    <a class="podcast-text-link" href="{{ $podcast->getUrl() }}">Browse all episodes <span aria-hidden="true">→</span></a>
                </aside>
            </main>
            @include('jelly.footer')
        </div>
    </div>
</x-page>
