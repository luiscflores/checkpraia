<?php

namespace App\Jobs;

use App\Models\FlagReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PurgePreciseLocations implements ShouldQueue
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
        // Null out precise coordinates for all reports created before today
        FlagReport::where('created_at', '<', now()->startOfDay())
            ->where(function ($query) {
                $query->whereNotNull('latitude')
                    ->orWhereNotNull('longitude');
            })
            ->update([
                'latitude' => null,
                'longitude' => null,
            ]);
    }
}
