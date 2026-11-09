<nav class="jelly-nav jelly-wrap" aria-label="Main">
    <a href="{{ url('/') }}" class="jelly-logo">
        <span class="jelly-logo-blob">L</span>
        <span class="jelly-logo-word">liam<span>.</span></span>
    </a>
    <div class="jelly-nav-links">
        <a href="{{ url('/') }}#writing">Writing</a>
        <a href="{{ url('/') }}#videos">Videos</a>
        <a href="{{ url('/') }}#talks">Talks</a>
        <a href="{{ route('podcasts.index') }}" @if(request()->is('podcasts*')) aria-current="{{ request()->is('podcasts') ? 'page' : 'true' }}" @endif>Podcasts</a>
    </div>
</nav>
