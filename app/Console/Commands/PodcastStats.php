<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PodcastStats extends Command
{
    protected $signature = 'podcast:stats {podcast? : Limit to one podcast slug} {--days=30 : Number of UTC calendar days, including today}';

    protected $description = 'Show estimated podcast download requests per episode';

    public function handle(): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);
        if ($days === false || $days < 1) {
            $this->error('--days must be a positive integer.');

            return self::FAILURE;
        }

        $from = now()->utc()->subDays($days - 1)->toDateString();
        $rows = DB::table('podcast_download_counts')
            ->whereBetween('date', [$from, now()->utc()->toDateString()])
            ->when($this->argument('podcast'), fn ($query, $slug) => $query->where('podcast_slug', $slug))
            ->selectRaw('podcast_slug, episode_guid, SUM(requests) AS requests')
            ->groupBy('podcast_slug', 'episode_guid')->orderBy('podcast_slug')->orderByDesc('requests')->get();

        $this->info('Estimated download requests, '.$from.' to '.now()->utc()->toDateString().' (UTC)');
        $this->table(['Podcast', 'Episode GUID', 'Requests'], $rows->map(fn ($row) => (array) $row)->all());
        $this->line('Total: '.$rows->sum('requests'));

        return self::SUCCESS;
    }
}
