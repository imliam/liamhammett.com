<article class="rounded-2xl hover:bg-gray-100 px-2 sm:px-8 py-4 relative group">
    <a href="{{ $article->getUrl() }}" class="flex flex-col sm:flex-row items-center justify-center content-center">
        <span class="size-6 text-gray-200 sm:mr-8">
            {!! svg('article-types.' . $article->type ?? 'article') !!}
        </span>

        <span class="flex-1 text-lg text-center sm:text-left font-semibold text-gray-900 group-hover:text-gray-600">
            {{ $article->title }}
        </span>

        <div class="flex flex-col sm:flex-row items-center text-xs gap-4 sm:ml-4">
            <x-tag-list>
                @foreach ($article->getTags() as $tag)
                    <x-tag class="group-hover:bg-orange-500 group-hover:text-orange-50 flex items-center justify-center text-center">{{ $tag->name }}</x-tag>
                @endforeach
            </x-tag-list>
            @isset ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}" class="text-gray-500">{{ $article->published_at->toFormattedDateString() }}</time>
            @endisset
        </div>
    </a>
</article>
