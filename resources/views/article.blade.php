<x-page title="{{ $article->title }}">
    <x-slot name="metaTags">
        <!-- SEO -->
        @isset ($article->synopsis)
            <meta name="description" content="{{ $article->synopsis }}">
        @endisset
        <link rel="canonical" href="{{ $article->getUrl() }}">
        <meta name="keywords" content="{{ implode(',', $article->tags) }}">

        @if ($article->hasNextArticle())
            <link rel="next" href="{{ $article->getNextArticle()->getUrl() }}" >
        @endif

        @if ($article->hasPreviousArticle())
            <link rel="prev" href="{{ $article->getPreviousArticle()->getUrl() }}" >
        @endif

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $article->title }}">
        @isset($article->synopsis)
            <meta name="twitter:description" content="{{ $article->synopsis }}">
        @endisset
        <meta name="twitter:image" content="{{ $article->getOpengraphImageUrl() }}">

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $article->title }}">
        @isset($article->synopsis)
            <meta property="og:description" content="Theres really a lot of great stuff in here...">
        @endisset
        <meta property="og:image" content="{{ $article->getOpengraphImageUrl() }}">
        <meta property="og:url" content="{{ $article->getUrl() }}">

        <!-- JSON-LD -->
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "WebPage",
                "name": "{{ $article->title }}",
                @isset($article->synopsis)
                    "description": "{{ $article->synopsis }}",
                @endisset
                {{-- "image": [
                    "https://mywebsite.com/images/blog-1/cover-image.webp",
                    "https://mywebsite.com/images/blog-1/another-image.webp"
                ], --}}
                "url": "{{ $article->getUrl() }}"
            }
        </script>
    </x-slot>

    <x-container>
        <div class="px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-base leading-7 text-gray-700 space-y-8 relative">
                <span class="size-80 text-gray-50 absolute right-0 rotate-[35deg] -z-10 -mt-16">
                    {!! svg('article-types.' . $article->type ?? 'article') !!}
                </span>

                <div>
                    @if ($article->strapline)
                        <p class="uppercase text-sm font-semibold tracking-wide leading-7 text-orange-500">{{ $article->strapline }}</p>
                    @endif
                    <h1 class="text-3xl font-title font-bold text-balance tracking-wide text-gray-900 sm:text-6xl text-shadow-sq shadow-orange-500">{{ $article->getAlternateTitle() }}</h1>
                    <p class="mt-4">
                        @isset ($article->published_at)
                            Published on <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->toFormattedDateString() }}</time>
                        @endisset
                        @isset ($article->updated_at)
                            &mdash; last updated at <time datetime="{{ $article->updated_at->toDateString() }}">{{ $article->updated_at->toFormattedDateString() }}</time>
                        @endisset
                    </p>
                    <x-tag-list class="mt-4 text-sm">
                        @foreach ($article->getTags() as $tag)
                            <x-tag url="{{ $tag->getUrl() }}">{{ $tag->name }}</x-tag>
                        @endforeach
                    </x-tag-list>
                </div>
                <div class="prose max-w-[65ch] mx-auto">
                    {!! $article->render() !!}
                </div>
                <x-divider class="flex flex-col gap-4 items-center justify-center bg-noise before:opacity-25">
                    <img src="https://res.cloudinary.com/liam/image/upload/v1675208772/liamhammett.com/avatar.jpg" alt="Photo of Liam Hammett" class="rounded-full size-32 {{ random_rotation() }}" />
                    <div class="text-center">
                        <div class="text-sm font-light">written by</div>
                        <div class="font-bold text-lg -mt-2">Liam Hammett</div>
                    </div>
                </x-divider>
                <div class="grid grid-cols-2 gap-2">
                    <div class="text-left">
                        @if ($article->hasPreviousArticle())
                            <a href="{{ $article->getPreviousArticle()->getUrl() }}" class="h-full border border-1 rounded-2xl px-6 py-4 flex gap-8 items-center justify-center group hover:bg-orange-500 hover:text-orange-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-right text-gray-600 group-hover:text-orange-200">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 9-3 3m0 0 3 3m-3-3h7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <div class="flex flex-col grow gap-2">
                                    <span class="font-semibold text-sm text-gray-700 uppercase tracking-wide group-hover:text-orange-100">
                                        Previous
                                        {{ match($article->type) {
                                            'podcast' => 'episode',
                                            'video' => 'video',
                                            default => 'article',
                                        } }}
                                    </span>
                                    <span class="grow">{{ $article->getPreviousArticle()->getAlternateTitle() }}</span>
                                </div>
                            </a>
                        @endif
                    </div>
                    <div class="text-right">
                        @if ($article->hasNextArticle())
                            <a href="{{ $article->getNextArticle()->getUrl() }}" class="h-full border border-1 rounded-2xl px-6 py-4 flex gap-8 items-center justify-center group hover:bg-orange-500 hover:text-orange-50">
                                <div class="flex flex-col grow gap-2">
                                    <span class="font-semibold text-sm text-gray-700 uppercase tracking-wide group-hover:text-orange-100">
                                        Next
                                        {{ match($article->type) {
                                            'podcast' => 'episode',
                                            'video' => 'video',
                                            default => 'article',
                                        } }}
                                    </span>
                                    <span class="grow">{{ $article->getNextArticle()->getAlternateTitle() }}</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-left text-gray-600 group-hover:text-orange-200">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="text-center">
                    <x-contribute :slug="$article->slug" />
                </div>
            </div>
        </div>
    </x-container>
</x-page>
