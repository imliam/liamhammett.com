@isset($url)
    <a href="{{ $url }}" class="relative z-10 rounded-full bg-gray-200 px-3 py-1.5 font-medium text-gray-600 hover:bg-orange-500 hover:text-orange-50 {{ $class ?? '' }}">{{ $slot }}</a>
@else
    <span class="relative z-10 rounded-full bg-gray-200 px-3 py-1.5 font-medium text-gray-600 hover:bg-orange-500 hover:text-orange-50 {{ $class ?? '' }}">{{ $slot }}</span>
@endisset