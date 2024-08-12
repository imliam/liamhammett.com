<div class="relative">
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-gray-300"></div>
    </div>
    @isset($slot)
        <div class="relative flex justify-center">
            <span class="px-3 text-base font-semibold leading-6 text-gray-900 bg-white rounded-2xl">{{ $slot }}</span>
        </div>
    @endisset
</div>
