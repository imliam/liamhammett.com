<a href="{{ $url }}" class="flex flex-col md:flex-row rounded-2xl p-4 border border-gray-400 font-sans text-sm bg-white hover:bg-gray-100 no-markup justify-center items-center no-underline" target="_blank">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" class="my-0 md:mr-8 h-32 rounded object-contain no-shadow" alt="{{ $siteName }}">
    @endif

    <div class="flex flex-col justify-center">
        @if ($title)
            <p class="text-black mb-2">{{ $title }}</p>
        @endif

        @if ($description)
            <p class="text-gray-700 font-normal mb-2">{{ $description }}</p>
        @endif

        @if ($domainName)
            <p class="text-gray-700 font-light text-xs mt-2">{{ $domainName }}</p>
        @endif
    </div>
</a>
