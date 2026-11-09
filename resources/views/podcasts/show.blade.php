<x-page :title="$podcast->title">
    <x-slot name="metaTags">
        @include('podcasts.meta', ['title' => $podcast->title, 'description' => $podcast->description, 'canonical' => $podcast->getUrl(), 'artwork' => $podcast->getArtworkUrl()])
    </x-slot>
    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')
            <main class="jelly-wrap podcast-main">
                <a class="jelly-back" href="{{ route('podcasts.index') }}"><span aria-hidden="true">←</span> All podcasts</a>
                <header class="podcast-show-summary podcast-show-header">
                    <img class="podcast-cover" src="{{ $podcast->getArtworkUrl() }}" alt="{{ $podcast->title }} cover art" width="1400" height="1400">
                    <div>
                        <h1 class="podcast-show-title">{{ $podcast->title }}</h1>
                        <p class="podcast-meta">With {{ $podcast->author }}</p>
                        <div class="prose jelly-prose podcast-description">{!! $podcast->render() !!}</div>
                        @include('podcasts.subscribe')
                    </div>
                </header>
                <section class="podcast-episodes" aria-labelledby="episodes-heading">
                    <div class="podcast-section-head">
                        <h2 class="jelly-h2" id="episodes-heading">All episodes</h2>
                        <span class="podcast-meta">{{ $episodes->count() }} {{ Str::plural('episode', $episodes->count()) }}</span>
                    </div>
                    @forelse ($episodes as $episode)
                        <article class="podcast-episode-row">
                            <div class="podcast-meta">
                                @if ($episode->episode_number !== null)<span>Episode {{ $episode->episode_number }}</span>@endif
                                <time datetime="{{ $episode->published_at->toIso8601String() }}">{{ $episode->published_at->copy()->timezone('Europe/London')->format('j F Y') }}</time>
                                <span>{{ $episode->formattedDuration() }}</span>
                            </div>
                            <h3><a href="{{ $episode->getUrl() }}">{{ $episode->title }} <span aria-hidden="true">↗</span></a></h3>
                            <p>{{ $episode->summary }}</p>
                            <audio controls preload="none" aria-label="Listen to {{ $episode->title }}">
                                <source src="{{ $episode->getAudioUrl() }}" type="{{ $episode->audio_type }}">
                                <a href="{{ $episode->getAudioUrl() }}">Download {{ $episode->title }}</a>
                            </audio>
                        </article>
                    @empty
                        <p>The first episode is on its way. Check back soon.</p>
                    @endforelse
                </section>
            </main>
            @include('jelly.footer')
        </div>
    </div>
</x-page>
