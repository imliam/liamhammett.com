<div {{ $attributes->merge(['class' => 'text-2xl shadow-2xl lined-paper']) }}>
    <div class="lined-paper-contents">
        {{ $slot }}
    </div>
</div>
