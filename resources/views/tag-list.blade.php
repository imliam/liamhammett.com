<x-page title="Tags">
    <x-container style="view-transition-name:main">
        <div class="max-w-3xl mx-auto space-y-8 text-base leading-7 text-center text-gray-700">
            <h1 class="text-3xl font-bold tracking-wide text-gray-900 font-title sm:text-6xl text-shadow-sq shadow-orange-500">Tag List</h1>
        </div>

        <div class="px-6 mx-auto max-w-7xl lg:px-8">
            <div class="max-w-2xl mx-auto prose">
                <ul class="pt-10 mt-10 space-y-4 sm:mt-16 sm:pt-16">
                    @foreach ($tags as $tag)
                        <li><a href="{{ $tag->getUrl() }}">{{ $tag->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-container>
</x-page>
