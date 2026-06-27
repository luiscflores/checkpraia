<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\WaterQualitySnapshot;
use App\Services\InfoAgua\InfoAguaClient;
use App\Domain\Community\ConsensusResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchInfoAguaData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $infoAgua = new InfoAguaClient();
        $resolver = new ConsensusResolver();

        $beaches = Beach::where('is_active', true)->get();

        foreach ($beaches as $beach) {
            $class = $infoAgua->getWaterQuality($beach->external_id ?: (string)$beach->id);

            WaterQualitySnapshot::create([
                'beach_id' => $beach->id,
                'quality_class' => $class,
                'sampled_at' => now(),
            ]);

            // Re-resolve status in case quality changed to Poor (red flag) or improved
            $resolver->resolveCurrentStatus($beach);
        }
    }
}
