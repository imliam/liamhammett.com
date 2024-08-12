<article class="rounded-2xl hover:bg-gray-100 px-8 py-4 relative -mx-8 -my-4">
    <a href="{{ $article->getUrl() }}" class="flex flex-col items-start justify-between">
        <div class="flex items-center text-xs gap-x-4">
            <time datetime="{{ $article->published_at->toDateString() }}" class="text-gray-500">{{ $article->published_at->toFormattedDateString() }}</time>
            <x-tag-list>
                @foreach ($article->getTags() as $tag)
                    <x-tag>{{ $tag->name }}</x-tag>
                @endforeach
            </x-tag-list>
        </div>

        <div class="group">
            <h3 class="mt-3 text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600">
                {{ $article->title }}
            </h3>
            <div class="mt-5 text-sm leading-6 text-gray-600 line-clamp-3 prose">
                {!! $article->getExcerpt() !!}
            </div>
        </div>
    </a>
</article>
