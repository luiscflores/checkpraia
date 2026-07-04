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
        $beaches = $this->beach ? collect([$this->beach]) : Beach::where('is_active', true)->get();

        if (!$this->beach) {
            Setting::set('last_infoagua_sync', now()->toIso8601String());
        }

        foreach ($beaches as $beach) {
            try {
                $this->processBeach($beach);
            } catch (\Exception $e) {
                logger()->warning('InfoÁgua fetch failed for beach ' . $beach->id, [
                    'error' => $e->getMessage(),
                ]);
            }
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

        if ($class) {
            WaterQualitySnapshot::create([
                'beach_id' => $beach->id,
                'quality_class' => $class,
                'sampled_at' => $result['sampled_at'] ?? now(),
            ]);
        }

        $prediction = $engine->calculate($beach);
        $prediction->save();

        $resolver->resolveCurrentStatus($beach);
    }
}
