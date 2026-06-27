<?php

namespace App\Jobs;

use App\Models\FlagReport;
use App\Domain\Community\ConsensusResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseConsensusWindows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resolver = new ConsensusResolver();
        
        // Find reports that were submitted over 60 minutes ago and are still pending
        $matureReports = FlagReport::where('status', 'pending')
            ->where('reported_at', '<=', now()->subMinutes(60))
            ->get();

        foreach ($matureReports as $report) {
            $resolver->resolveReport($report);
        }

        // Trigger updates to current statuses for beaches that had reports resolved
        $affectedBeachIds = $matureReports->pluck('beach_id')->unique();
        foreach ($affectedBeachIds as $beachId) {
            $beach = \App\Models\Beach::find($beachId);
            if ($beach) {
                $resolver->resolveCurrentStatus($beach);
            }
        }
    }
}
