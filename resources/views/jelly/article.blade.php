
<div class="jelly-page">
    @include('jelly.nav')

    <article class="jelly-article">
        <header class="jelly-article-head jelly-wrap">
            <a href="{{ url('/') }}#writing" class="jelly-back"><span aria-hidden="true">←</span> All writing</a>
            @if ($article->strapline)
                <p class="jelly-kicker"><span class="jelly-dot"></span> {{ $article->strapline }}</p>
            @endif
            <h1 class="jelly-article-title">{{ $article->getAlternateTitle() }}</h1>
            <div class="jelly-article-meta">
                <span class="jelly-type-badge">
                    {!! svg('article-types.' . ($article->type ?? 'article')) !!}
                    {{ ucfirst($article->type ?? 'article') }}
                </span>
                @isset ($article->published_at)
                    <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
                @endisset
                @isset ($article->updated_at)
                    <span class="jelly-updated">updated {{ $article->updated_at->format('j M Y') }}</span>
                @endisset
                @foreach ($article->getTags() as $tag)
                    <a href="{{ $tag->getUrl() }}" class="jelly-chip jelly-chip--link">{{ $tag->name }}</a>
                @endforeach
            </div>
        </header>

        <div class="jelly-article-body">
            <div class="prose jelly-prose">
                {!! $article->render() !!}
            </div>
        </div>

        <aside class="jelly-wrap jelly-article-end">
            <div class="jelly-author">
                <img src="{{ url('images/avatar.jpg') }}" alt="Photo of Liam Hammett" class="jelly-author-img">
                <div>
                    <p class="jelly-author-label">Written by</p>
                    <p class="jelly-author-name">Liam Hammett</p>
                    <p class="jelly-author-bio">PHP &amp; Laravel developer who talks about code and stuff. Makes videos, gives talks, breaks things on purpose.</p>
                </div>
                <a href="https://www.youtube.com/@imliamhammett" class="jelly-btn jelly-btn--primary jelly-author-btn">Subscribe</a>
            </div>

            <nav class="jelly-pager" aria-label="More articles">
                @if ($article->hasPreviousArticle())
                    <a href="{{ $article->getPreviousArticle()->getUrl() }}" class="jelly-pager-card">
                        <span class="jelly-pager-label">← Previous</span>
                        <span class="jelly-pager-title">{{ $article->getPreviousArticle()->getAlternateTitle() }}</span>
                    </a>
                @else
                    <span></span>
                @endif
                @if ($article->hasNextArticle())
                    <a href="{{ $article->getNextArticle()->getUrl() }}" class="jelly-pager-card jelly-pager-card--next">
                        <span class="jelly-pager-label">Next →</span>
                        <span class="jelly-pager-title">{{ $article->getNextArticle()->getAlternateTitle() }}</span>
                    </a>
                @endif
            </nav>
        </aside>
    </article>

    @include('jelly.footer')
</div>
