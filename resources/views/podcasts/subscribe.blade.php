<div class="podcast-subscribe" aria-label="Subscribe to {{ $podcast->title }}">
    <a class="jelly-btn" href="{{ $podcast->getFeedUrl() }}">RSS feed</a>
    @foreach ($podcast->links ?? [] as $label => $href)
        <a class="podcast-text-link" href="{{ $href }}">{{ $label }}</a>
    @endforeach
</div>
