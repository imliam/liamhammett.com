<x-page title="404">
    <div class="contents dir-jelly">
        <div class="jelly-page">
            @include('jelly.nav')

            <main class="jelly-404 jelly-wrap">
                <h1 class="jelly-404-title">4<span class="jelly-hl">0</span>4</h1>
                <p class="jelly-page-lede">Looks like you've found&hellip; nothing. It must have slid off somewhere.</p>
                <a href="{{ url('/') }}" class="jelly-btn jelly-btn--primary">Take me home</a>
            </main>

            @include('jelly.footer')
        </div>
    </div>
</x-page>
