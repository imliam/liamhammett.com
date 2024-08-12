<x-page :homepage="true">
    <x-slot name="metaTags">
        <!-- SEO -->
        <meta name="description" content="I talk about code and stuff">
        <link rel="canonical" href="{{ url('/') }}">
        {{-- <meta name="keywords" content=""> --}}

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Liam Hammett">
        <meta name="twitter:description" content="I talk about code and stuff">
        {{-- <meta name="twitter:image" content="https://mywebsite.com/images/blog-1/cover-image.webp"> --}}

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="Liam Hammett">
        <meta property="og:description" content="I talk about code and stuff">
        {{-- <meta property="og:image" content="https://mywebsite.com/images/blog-1/cover-image.webp"> --}}
        {{-- <meta property="og:image" content="https://mywebsite.com/images/blog-1/another-image.webp"> --}}
        <meta property="og:url" content="{{ url('/') }}">

        <!-- JSON-LD -->
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "WebPage",
                "name": "Liam Hammett",
                "description": "I talk about code and stuff",
                {{-- "image": [
                    "https://mywebsite.com/images/blog-1/cover-image.webp",
                    "https://mywebsite.com/images/blog-1/another-image.webp"
                ], --}}
                "url": "{{ url('/') }}"
            }
        </script>
    </x-slot>

    <x-container>
        <div class="py-24 sm:py-32">
            <div class="px-6 mx-auto max-w-7xl lg:px-8">
                <div class="max-w-2xl mx-auto">
                    <div class="pt-10 mt-10 space-y-16 sm:mt-16 sm:pt-16">
                        @foreach ($articlesByYear as $year => $articles)
                            <x-divider>{{ $year }}</x-divider>
                            <div class="grid gap-16 sm:gap-8">
                                @foreach ($articles as $article)
                                    <x-short-listing :article="$article" />
                                    {{-- <x-divider /> --}}
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </x-container>
</x-page>
