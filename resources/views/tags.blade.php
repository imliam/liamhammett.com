<x-page :title="$tag->name">
    <x-container style="view-transition-name:main">
        <div class="text-center max-w-3xl mx-auto text-base leading-7 text-gray-700 space-y-8">
            <h1 class="text-3xl font-title font-bold tracking-wide text-gray-900 sm:text-6xl text-shadow-sq shadow-orange-500">{{ $tag->name }}</h1>
        </div>

        <div class="px-6 mx-auto max-w-7xl lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="pt-10 mt-10 space-y-16 sm:mt-16 sm:pt-16">
                    @foreach ($articlesByYear as $year => $articles)
                        <x-divider>{{ $year }}</x-divider>
                        <div class="grid gap-8">
                            @foreach ($articles as $article)
                                <x-short-listing :article="$article" />
                                {{-- <x-divider /> --}}
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-container>
</x-page>
