<div class="container mx-auto overflow-x-hidden {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    {{ $slot }}
</div>
