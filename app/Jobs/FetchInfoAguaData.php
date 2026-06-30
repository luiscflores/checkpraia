<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\WaterQualitySnapshot;
use App\Services\InfoAgua\InfoAguaClient;
use App\Domain\Forecasting\PredictionEngine;
use App\Domain\Community\ConsensusResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchInfoAguaData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?Beach $beach;

    /**
     * Create a new job instance.
     */
    public function __construct(?Beach $beach = null)
    {
        $this->beach = $beach;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->beach) {
            $this->processBeach($this->beach);
        } else {
            $beaches = Beach::where('is_active', true)->get();
            foreach ($beaches as $beach) {
                self::dispatch($beach);
            }
        }
    }

    /**
     * Process water quality for a single beach.
     */
    private function processBeach(Beach $beach): void
    {
        $infoAgua = new InfoAguaClient();
        $engine = new PredictionEngine();
        $resolver = new ConsensusResolver();

        $class = $infoAgua->getWaterQuality($beach->external_id ?: (string)$beach->id);

        WaterQualitySnapshot::create([
            'beach_id' => $beach->id,
            'quality_class' => $class,
            'sampled_at' => now(),
        ]);

        // Recalculate automatic prediction using the new water quality data
        $prediction = $engine->calculate($beach);
        $prediction->save();

        // Re-resolve status in case quality changed to Poor (red flag) or improved
        $resolver->resolveCurrentStatus($beach);
    }
}
