
<div class="jelly-page">
    @include('jelly.nav')

    <header class="jelly-hero jelly-wrap">
        <div class="jelly-hero-copy">
            <p class="jelly-kicker"><span class="jelly-dot"></span> PHP &amp; Laravel developer, UK</p>
            <h1 class="jelly-hero-title">
                Hi, I'm <span class="jelly-hero-name">Liam<svg class="jelly-squiggle" viewBox="0 0 220 24" preserveAspectRatio="none" aria-hidden="true"><path d="M4 16 C 28 4, 44 4, 62 14 S 98 24, 118 12 S 156 2, 176 14 S 206 20, 216 8" /></svg></span>.
                <span class="jelly-hero-sub">I talk about code and&nbsp;stuff.</span>
            </h1>
            <p class="jelly-hero-lede">
                I write about PHP, Laravel and the little tools that make developers' days better. I make videos, give talks at conferences, and occasionally ship something silly on purpose.
            </p>
            <div class="jelly-hero-actions">
                <a href="#writing" class="jelly-btn jelly-btn--primary">Read my blog</a>
                <a href="https://www.youtube.com/@imliamhammett" class="jelly-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 5.5v13l11-6.5z"/></svg>
                    Watch my videos
                </a>
            </div>
        </div>

        <div class="jelly-hero-art" aria-hidden="true">
            <svg class="jelly-goo-defs" width="0" height="0">
                @foreach (['jelly-goo' => [-20, 140], 'jelly-goo-wide' => [-170, 440]] as $id => [$offset, $size])
                    {{-- The wide region is only swapped in while the blob is reaching, since filters get expensive over big areas --}}
                    <filter id="{{ $id }}" x="{{ $offset }}%" y="{{ $offset }}%" width="{{ $size }}%" height="{{ $size }}%" color-interpolation-filters="sRGB">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="14" result="blur"/>
                        <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 26 -11" result="goo"/>
                        <feMorphology in="goo" operator="dilate" radius="2.5" result="fat"/>
                        <feFlood flood-color="#2a1810"/>
                        <feComposite in2="fat" operator="in" result="outline"/>
                        <feOffset in="outline" dx="6" dy="6" result="shadow"/>
                        <feMerge><feMergeNode in="shadow"/><feMergeNode in="outline"/><feMergeNode in="goo"/></feMerge>
                    </filter>
                @endforeach
            </svg>
            <div class="jelly-goo">
                <span class="jelly-goo-b jelly-goo-b1"></span>
                <span class="jelly-goo-b jelly-goo-b2"></span>
                <span class="jelly-goo-b jelly-goo-b3"></span>
                <span class="jelly-goo-b jelly-goo-b4"></span>
            </div>
            <img src="{{ url('images/avatar.jpg') }}" alt="" class="jelly-hero-avatar">
            <span class="jelly-sticker jelly-sticker--yellow jelly-sticker--a">&lt;?php</span>
            <span class="jelly-sticker jelly-sticker--pink jelly-sticker--b">writes real human words</span>
            <span class="jelly-sticker jelly-sticker--mint jelly-sticker--c">likes orange</span>
        </div>
    </header>

    <section id="videos" class="jelly-section jelly-wrap">
        <div class="jelly-section-head">
            <h2 class="jelly-h2">Fresh off the <span class="jelly-hl">tube</span></h2>
            <a href="https://www.youtube.com/@imliamhammett" class="jelly-link-arrow">All videos <span aria-hidden="true">→</span></a>
        </div>
        <div class="jelly-videos">
            @foreach ($videos as $video)
                <a href="{{ $video['url'] }}" class="jelly-video {{ $loop->first ? 'jelly-video--feature' : '' }}">
                    <span class="jelly-video-thumb">
                        @if ($loop->first)
                            <span class="jelly-video-new">New!</span>
                        @endif
                        <img src="{{ $video['thumbnail'] }}" alt="" loading="lazy">
                        <span class="jelly-play" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M8 5.5v13l11-6.5z"/></svg></span>
                    </span>
                    <span class="jelly-video-body">
                        @if ($loop->first)
                            <span class="jelly-video-kicker">Latest upload</span>
                        @endif
                        <span class="jelly-video-title">{{ $video['title'] }}</span>
                        @if ($loop->first)
                            <span class="jelly-video-watch">Watch now →</span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section id="talks" class="jelly-section jelly-wrap">
        <div class="jelly-section-head">
            <h2 class="jelly-h2">On <span class="jelly-hl jelly-hl--pink">stage</span></h2>
            <p class="jelly-section-note jelly-talk-legend">
                <span class="jelly-talk-kind jelly-talk-kind--conference">Conference</span>
                <span class="jelly-talk-kind jelly-talk-kind--meetup">Meetup</span>
                <span class="jelly-talk-legend-video"><span class="jelly-talk-play" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M8 5.5v13l11-6.5z"/></svg></span> Has a video</span>
            </p>
        </div>
        <div class="jelly-talks" data-drag-scroll>
            @foreach ($talks as $talk)
                @php
                    $headline = $talk['events'][0];
                    $link = isset($talk['article']) ? url($talk['article']) : ($headline['video'] ?? $headline['url'] ?? null);
                    $tint = 'jelly-tint-' . $loop->index % 4;
                @endphp
                <div class="jelly-talk-stack">
                    <a href="{{ $link }}" class="jelly-talk {{ $tint }}">
                        <span class="jelly-talk-meta">
                            <span class="jelly-talk-year">{{ $headline['year'] }}</span>
                            @include('jelly.talk-kind', ['event' => $headline])
                        </span>
                        <span class="jelly-talk-body">
                            <span class="jelly-talk-title">{{ $talk['title'] }}</span>
                            <span class="jelly-talk-event">{{ $headline['name'] }}</span>
                        </span>
                        <span class="jelly-talk-mic" aria-hidden="true">
                            {!! svg('article-types.talk') !!}
                        </span>
                    </a>

                    {{-- Other times it's been given hang off the card like drips of goo --}}
                    @foreach (array_slice($talk['events'], 1) as $event)
                        @php($href = $event['video'] ?? $event['url'] ?? null)
                        <{{ $href ? 'a' : 'span' }} @if ($href) href="{{ $href }}" @endif class="jelly-talk-drip {{ $tint }}">
                            <svg class="jelly-talk-neck" viewBox="0 0 40 24" preserveAspectRatio="none" aria-hidden="true">
                                <path class="jelly-talk-neck-fill" d="M0 -6H40V0C27 0 27 24 40 24V30H0V24C13 24 13 0 0 0Z"/>
                                <path class="jelly-talk-neck-edge" d="M0 0C13 0 13 24 0 24M40 0C27 0 27 24 40 24"/>
                            </svg>
                            @include('jelly.talk-kind', ['event' => $event])
                            <span class="jelly-talk-drip-name">{{ $event['name'] }}</span>
                            <span class="jelly-talk-drip-year">{{ $event['year'] }}</span>
                        </{{ $href ? 'a' : 'span' }}>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>

    <section id="writing" class="jelly-section jelly-wrap">
        <div class="jelly-section-head">
            <h2 class="jelly-h2">The <span class="jelly-hl jelly-hl--yellow">writing</span> pile</h2>
            <a href="{{ url('/tags') }}" class="jelly-link-arrow">Browse by tag <span aria-hidden="true">→</span></a>
        </div>

        @include('jelly.archive', ['articlesByYear' => $articlesByYear])
    </section>

    @include('jelly.footer')
</div>
