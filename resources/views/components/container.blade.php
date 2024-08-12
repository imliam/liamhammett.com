<div class="container mx-auto {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    {{ $slot }}
</div>
