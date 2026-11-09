<x-page title="Podcasts">
    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')
            <main class="jelly-wrap podcast-main">
                <header class="podcast-index-head">
                    <h1 class="jelly-article-title">Podcasts</h1>
                    <p class="podcast-intro">Thoughts on code, tech and everything surrounding it. Best enjoyed with headphones.</p>
                </header>
                @forelse ($podcasts as $podcast)
                    <article class="podcast-show-summary">
                        <a href="{{ $podcast->getUrl() }}" tabindex="-1" aria-hidden="true">
                            <img class="podcast-cover" src="{{ $podcast->getArtworkUrl() }}" alt="" width="1400" height="1400">
                        </a>
                        <div>
                            <h2 class="podcast-show-title"><a href="{{ $podcast->getUrl() }}">{{ $podcast->title }}</a></h2>
                            <p class="podcast-meta">{{ $podcast->author }} · {{ $podcast->episodes_count }} {{ Str::plural('episode', $podcast->episodes_count) }}</p>
                            <p class="podcast-description">{{ $podcast->description }}</p>
                            <a class="jelly-btn" href="{{ $podcast->getUrl() }}">Listen to the podcast <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @empty
                    <p>There are no podcasts here yet. Check back soon.</p>
                @endforelse
            </main>
            @include('jelly.footer')
        </div>
    </div>
</x-page>
