<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrunePodcastAnalytics extends Command
{
    protected $signature = 'podcast:prune-analytics';

    protected $description = 'Remove expired podcast listener fingerprints and aggregates older than 13 months';

    public function handle(): int
    {
        $windows = DB::table('podcast_download_windows')->where('counted_at', '<', now()->timestamp - 3 * 86400)->delete();
        $counts = DB::table('podcast_download_counts')->where('date', '<', now()->utc()->subMonthsNoOverflow(13)->toDateString())->delete();
        $this->info("Removed {$windows} expired fingerprints and {$counts} old daily counts.");

        return self::SUCCESS;
    }
}
