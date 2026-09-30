<div class="jelly-archive">
    @foreach ($articlesByYear as $year => $articles)
        <div class="jelly-year">
            <div class="jelly-year-label"><span>{{ $year }}</span></div>
            <ul class="jelly-list">
                @foreach ($articles as $article)
                    <li>
                        <a href="{{ $article->getUrl() }}" class="jelly-row">
                            <span class="jelly-row-icon">{!! svg('article-types.' . ($article->type ?? 'article')) !!}</span>
                            <span class="jelly-row-title">{{ $article->title }}</span>
                            <span class="jelly-row-meta">
                                @foreach (array_slice($article->getTags(), 0, 2) as $tag)
                                    <span class="jelly-chip">{{ $tag->name }}</span>
                                @endforeach
                                @isset($article->published_at)
                                    <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('M j') }}</time>
                                @endisset
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
