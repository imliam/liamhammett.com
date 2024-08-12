<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @isset($title)
        <title>{{ $title }} - {{ config('app.name') }}</title>
    @else
        <title>{{ config('app.name') }}</title>
    @endisset

    @foreach (config('feed.feeds') as $feed)
        <link rel="alternate" type="application/rss+{{ $feed['format'] }}" title="{{ $feed['title'] }}" href="{{ $feed['url'] }}" />
    @endforeach

    <!-- Assets -->
    <link rel="preload" href="/fonts/ostrich-sans-rounded.ttf" as="font" type="font/ttf" crossorigin />
    <link rel="preload" href="/fonts/Handlee-Regular.ttf" as="font" type="font/ttf" crossorigin />
    <link rel="preload" href="/fonts/Spirax-Regular.ttf" as="font" type="font/ttf" crossorigin />
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    {{ $metaTags ?? '' }}

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SG43WE6EN7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-SG43WE6EN7');
    </script>
</head>

<body class="font-sans antialiased bg-white text-slate-950 min-h-full overflow-x-hidden bg-noise before:opacity-5 before:fixed">
    <x-header class="text-center z-10 relative pb-32" :homepage="$homepage ?? false" />

    {{ $slot }}

    <x-footer class="mb-8" />
</body>

</html>
