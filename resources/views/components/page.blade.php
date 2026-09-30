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

    <link rel="icon" type="image/svg+xml" href="{{ url('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ url('favicon.png') }}">

    @foreach (config('feed.feeds') as $feed)
        <link rel="alternate" type="application/rss+{{ $feed['format'] }}" title="{{ $feed['title'] }}" href="{{ $feed['url'] }}" />
    @endforeach

    <!-- Assets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,400..800&family=Figtree:ital,wght@0,400..800;1,400..800&family=JetBrains+Mono:wght@500;700&display=swap">
    <link rel="preload" href="/fonts/ostrich-sans-rounded.ttf" as="font" type="font/ttf" crossorigin />
    <link rel="preload" href="/fonts/Handlee-Regular.ttf" as="font" type="font/ttf" crossorigin />
    <link rel="preload" href="/fonts/Spirax-Regular.ttf" as="font" type="font/ttf" crossorigin />
    {{-- Don't render (or snapshot for the page transition) until the whole body has arrived, or the goo reveals a half-built page --}}
    <link rel="expect" href="#page-end" blocking="render">
    @include('jelly.view-transition')
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    {{ $metaTags ?? '' }}

    @if (env('GOOGLE_ANALYTICS_ID'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ env("GOOGLE_ANALYTICS_ID") }}');
        </script>
    @endif
</head>

<body class="font-sans antialiased text-slate-950 min-h-full bg-noise before:opacity-5 before:fixed">
    {{ $slot }}

    {{-- Only shown while a page transition captures them: the goo's orange rim and its ink outline --}}
    <div class="goo-vt goo-vt--ink" aria-hidden="true"></div>
    <div class="goo-vt goo-vt--orange" aria-hidden="true"></div>
    <div id="page-end" hidden></div>
</body>

</html>
