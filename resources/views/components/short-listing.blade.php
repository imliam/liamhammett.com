<article class="rounded-2xl hover:bg-gray-100 px-8 py-4 relative -mx-8 -my-4 group">
    <a href="{{ $article->getUrl() }}" class="flex flex-row items-center justify-center content-center">
        <span class="size-6 text-gray-200 mr-8">
            {!! svg('article-types.' . $article->type ?? 'article') !!}
        </span>

        <span class="flex-1 text-lg font-semibold text-gray-900 group-hover:text-gray-600">
            {{ $article->title }}
        </span>

        <div class="flex flex-row items-center text-xs gap-x-4 ml-4">
            <x-tag-list>
                @foreach ($article->getTags() as $tag)
                    <x-tag class="group-hover:bg-orange-500 group-hover:text-orange-50">{{ $tag->name }}</x-tag>
                @endforeach
            </x-tag-list>
            @isset ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}" class="text-gray-500">{{ $article->published_at->toFormattedDateString() }}</time>
            @endisset
        </div>
    </a>
</article>
