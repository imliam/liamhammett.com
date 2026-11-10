<div data-podcast-player>
    <audio controls preload="metadata" data-playback-url="{{ $episode->getAudioUrl() }}" aria-label="Listen to {{ $episode->title }}">
        <source src="{{ $episode->getAudioUrl(track: false) }}" type="{{ $episode->audio_type }}">
        <a href="{{ $episode->getAudioUrl() }}">Download this episode</a>
    </audio>
    @if ($episode->chapters)
        <nav class="podcast-chapters" aria-label="Episode chapters">
            <ol>
                @foreach ($episode->chapters as $chapter)
                    <li>
                        <a href="{{ $episode->getAudioUrl() }}#t={{ $chapter['startTime'] }}" data-chapter-start="{{ $chapter['startTime'] }}">
                            <span class="podcast-chapter-time">{{ gmdate($chapter['startTime'] >= 3600 ? 'H:i:s' : 'i:s', (int) $chapter['startTime']) }}</span>
                            <span>{{ $chapter['title'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif
    <p class="podcast-player-status" role="status" hidden>Audio could not start. Try the play button or <a class="podcast-text-link" href="{{ $episode->getAudioUrl() }}">download the MP3</a>.</p>
</div>
