<x-page :title="$tag->name">
    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')

            <header class="jelly-page-head jelly-wrap">
                <a href="{{ url('/tags') }}" class="jelly-back"><span aria-hidden="true">←</span> All tags</a>
                <p class="jelly-kicker"><span class="jelly-dot"></span> Tagged</p>
                <h1 class="jelly-article-title">{{ $tag->name }}</h1>
                <p class="jelly-page-lede">{{ $count = $articlesByYear->flatten()->count() }} {{ str('post')->plural($count) }} and counting.</p>
            </header>

            <section class="jelly-section jelly-wrap">
                @include('jelly.archive', ['articlesByYear' => $articlesByYear])
            </section>

            @include('jelly.footer')
        </div>
    </div>
</x-page>
