@props(['name', 'avatarUrl', 'title'])

<figure class="not-prose mx-auto {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    <blockquote class="font-semibold text-gray-900">
        {!! $slot !!}
    </blockquote>
    <figcaption class="flex items-center justify-center mt-6 gap-x-4">
        @isset ($avatarUrl)
            <img class="flex-none w-6 h-6 rounded-full bg-gray-50 no-shadow"
                src="{{ $avatarUrl }}"
                alt="{{ $name }}"
                data-nofigure
            />
        @endisset

        <cite class="text-sm leading-6">
            @isset ($name)
                <strong class="font-semibold text-gray-900 font-handwritten">
                    {{ $name }}
                </strong>
            @endisset
            @isset ($title)
                &mdash;
                @isset($sourceUrl)
                    <a class="text-gray-500 hover:text-gray-700" href="{{ $sourceUrl }}" rel="nofollow noopener noreferrer" target="_blank">
                        {{ $title }}
                    </a>
                @else
                    {{ $title }}
                @endisset
            @endisset
        </cite>
    </figcaption>
</figure>
