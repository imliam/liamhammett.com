@if ($type === 'success')
    <div class="bg-green-500 text-green-50 font-semibold rounded-2xl px-8 py-2 flex items-center gap-4 shadow-depth overflow-hidden relative">
        <span class="-ml-4 inline-block text-green-400 size-16 absolute z-10">{!! svg('success') !!}</span>
        <span class="z-20 ml-8">{{ $slot }}</span>
    </div>
@elseif ($type === 'warning')
    <div class="bg-orange-500 text-orange-50 font-semibold rounded-2xl px-8 py-2 flex items-center gap-4 shadow-depth overflow-hidden relative">
        <span class="-ml-4 inline-block text-orange-400 size-16 absolute z-10">{!! svg('warning') !!}</span>
        <span class="z-20 ml-8">{{ $slot }}</span>
    </div>
@elseif ($type === 'error')
    <div class="bg-red-500 text-red-50 font-semibold rounded-2xl px-8 py-2 flex items-center gap-4 shadow-depth overflow-hidden relative">
        <span class="-ml-4 inline-block text-red-400 size-16 absolute z-10">{!! svg('error') !!}</span>
        <span class="z-20 ml-8">{{ $slot }}</span>
    </div>
@elseif ($type === 'info')
    <div class="bg-blue-500 text-blue-50 font-semibold rounded-2xl px-8 py-2 flex items-center gap-4 shadow-depth overflow-hidden relative">
        <span class="-ml-4 inline-block text-blue-400 size-16 absolute z-10">{!! svg('info') !!}</span>
        <span class="z-20 ml-8">{{ $slot }}</span>
    </div>
@endif
