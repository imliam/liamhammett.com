<?php

namespace App\Utilities;

use App\Models\PodcastEpisode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PodcastDownloadAnalytics
{
    public function record(Request $request, PodcastEpisode $episode, int $bytes): void
    {
        $agent = trim($request->userAgent() ?? '');
        if (! $request->isMethod('GET') || $agent === '' || preg_match('/bot|crawl|spider|headless|monitor|uptime|validator|curl\/|wget\/|python-requests\//i', $agent)) {
            return;
        }

        // PHP cannot know whether Nginx will answer a conditional request with 304.
        if ($request->hasHeader('If-None-Match') || $request->hasHeader('If-Modified-Since') || ! $this->countableRange($request->header('Range'), $bytes)) {
            return;
        }

        $ip = @inet_pton($request->ip() ?? '');
        if ($ip === false) {
            return;
        }
        if (strlen($ip) === 16) {
            $ip = substr($ip, 0, 8); // IPv6 privacy addresses share a /64 identity.
        }
        $fingerprint = hash_hmac('sha256', $episode->guid."\0".bin2hex($ip)."\0".$agent, config('app.key'));
        $now = now()->utc();

        DB::transaction(function () use ($episode, $fingerprint, $now) {
            DB::table('podcast_download_windows')->insertOrIgnore([
                'fingerprint' => $fingerprint, 'counted_at' => 0,
            ]);
            // The conditional write claims the window atomically, including concurrent ranges.
            $claimed = DB::table('podcast_download_windows')->where('fingerprint', $fingerprint)
                ->where('counted_at', '<=', $now->timestamp - 86400)
                ->update(['counted_at' => $now->timestamp]);
            if (! $claimed) {
                return;
            }

            $key = ['podcast_slug' => $episode->podcast_slug, 'episode_guid' => $episode->guid, 'date' => $now->toDateString()];
            DB::table('podcast_download_counts')->insertOrIgnore($key + ['requests' => 0]);
            DB::table('podcast_download_counts')->where($key)->increment('requests');
        }, 3);
    }

    private function countableRange(?string $range, int $bytes): bool
    {
        if ($range === null) {
            return true;
        }
        if (! preg_match('/\Abytes=(\d*)-(\d*)\z/', trim($range), $parts) || ($parts[1] === '' && $parts[2] === '')) {
            return false;
        }
        if ($parts[1] === '') {
            return min((int) $parts[2], $bytes) > 2;
        }

        $start = (int) $parts[1];
        $end = $parts[2] === '' ? $bytes - 1 : min((int) $parts[2], $bytes - 1);

        return $start < $bytes && $end - $start + 1 > 2;
    }
}
