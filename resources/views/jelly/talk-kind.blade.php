<span class="jelly-talk-kind jelly-talk-kind--{{ $event['kind'] }}">{{ $event['kind'] === 'conference' ? 'Conference' : 'Meetup' }}</span>
@isset($event['video'])
    <span class="jelly-talk-play" title="Has a video"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 5.5v13l11-6.5z"/></svg><span class="sr-only">Has a video</span></span>
@endisset
