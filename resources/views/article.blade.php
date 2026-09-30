<x-page :title="$article->title">
    <x-slot name="metaTags">
        <!-- SEO -->
        @isset ($article->synopsis)
            <meta name="description" content="{{ $article->synopsis }}">
        @endisset
        <link rel="canonical" href="{{ $article->canonical ?? $article->getUrl() }}">
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
            <meta property="og:description" content="{{ $article->synopsis }}">
        @endisset
        <meta property="og:image" content="{{ $article->getOpengraphImageUrl() }}">
        <meta property="og:url" content="{{ $article->getUrl() }}">

        <!-- JSON-LD -->
        <script type="application/ld+json">
            {
                "@@context": "https://schema.org",
                "@@type": "WebPage",
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

    <div class="contents dir-jelly">
        @include('jelly.article')
    </div>
</x-page>
