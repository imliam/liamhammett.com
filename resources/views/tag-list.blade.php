<x-page title="Tags">
    @php
        $counts = \App\Models\Article::query()->published()->get()
            ->flatMap(fn ($article) => $article->getTags())
            ->countBy(fn ($tag) => $tag->name);
    @endphp

    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')

            <header class="jelly-page-head jelly-wrap">
                <a href="{{ url('/') }}#writing" class="jelly-back"><span aria-hidden="true">←</span> All writing</a>
                <h1 class="jelly-article-title">Browse by <span class="jelly-hl">tag</span></h1>
                <p class="jelly-page-lede">Everything I've written, sorted into little piles.</p>
            </header>

            <section class="jelly-section jelly-wrap">
                <ul class="jelly-tag-cloud">
                    @foreach ($tags as $tag)
                        <li>
                            <a href="{{ $tag->getUrl() }}" class="jelly-tag jelly-tint-{{ $loop->index % 4 }}">
                                {{ $tag->name }}
                                <span class="jelly-tag-count">{{ $counts[$tag->name] ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            @include('jelly.footer')
        </div>
    </div>
</x-page>
