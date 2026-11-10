<x-page :title="'Transcript: '.$episode->title">
    <x-slot name="metaTags">
        <link rel="canonical" href="{{ $episode->getTranscriptUrl() }}">
        <meta name="robots" content="noindex">
    </x-slot>
    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')
            <main class="jelly-wrap podcast-main podcast-episode-page">
                <a class="jelly-back" href="{{ $episode->getUrl() }}">Back to the episode</a>
                <header class="podcast-episode-head">
                    <h1 class="podcast-show-title">{{ $episode->title }}: transcript</h1>
                    <p class="podcast-meta">{{ $podcast->title }} · {{ $podcast->author }}</p>
                </header>
                <div class="prose jelly-prose">{!! $episode->renderTranscript() !!}</div>
            </main>
            @include('jelly.footer')
        </div>
    </div>
</x-page>
