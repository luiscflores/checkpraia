<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\Setting;
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

    public function __construct(?Beach $beach = null)
    {
        $this->beach = $beach;
    }

    public function handle(): void
    {
        if ($this->beach) {
            $this->processBeach($this->beach);
        } else {
            Setting::set('last_infoagua_sync', now()->toIso8601String());
            Beach::where('is_active', true)->chunkById(50, function ($beaches) {
                foreach ($beaches as $beach) {
                    self::dispatch($beach);
                }
            });
        }
    }

    private function processBeach(Beach $beach): void
    {
        $infoAgua = new InfoAguaClient();
        $engine = new PredictionEngine();
        $resolver = new ConsensusResolver();

        $result = $infoAgua->getWaterQualityByCoords(
            (float) $beach->latitude,
            (float) $beach->longitude,
            $beach->name
        );

        $class = $result['class'] ?? null;

        // Fallback when APA WFS has no data for this beach
        if ($class === null) {
            $class = $this->fallbackQuality($beach);
        }

        if ($class) {
            WaterQualitySnapshot::create([
                'beach_id' => $beach->id,
                'quality_class' => $class,
                'sampled_at' => now(),
            ]);
        }

        $prediction = $engine->calculate($beach);
        $prediction->save();

        $resolver->resolveCurrentStatus($beach);
    }

    private function fallbackQuality(Beach $beach): ?string
    {
        $month = (int) now()->format('n');
        $isBathingSeason = $month >= 5 && $month <= 9;

        if (!$isBathingSeason) {
            return null;
        }

        if ($beach->blue_flag) {
            return 'Excellent';
        }

        return 'Good';
    }
}
