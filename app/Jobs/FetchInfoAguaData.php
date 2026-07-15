<?php

namespace App\Jobs;

use App\Domain\Community\ConsensusResolver;
use App\Domain\Forecasting\PredictionEngine;
use App\Models\Beach;
use App\Models\Setting;
use App\Models\WaterQualitySnapshot;
use App\Services\InfoAgua\InfoAguaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchInfoAguaData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public $backoff = 30;

    protected ?Beach $beach;

    public function __construct(?Beach $beach = null)
    {
        $this->beach = $beach;
    }

    public function handle(): void
    {
        if (! $this->beach) {
            Setting::set('last_infoagua_sync', now()->toIso8601String());
        }

        $infoAgua = app(InfoAguaClient::class);
        $engine = app(PredictionEngine::class);
        $resolver = app(ConsensusResolver::class);

        if ($this->beach) {
            try {
                $this->processBeach($this->beach, $infoAgua, $engine, $resolver);
            } catch (\Exception $e) {
                logger()->warning('InfoÁgua fetch failed for beach '.$this->beach->id, [
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        // Process in chunks to avoid loading all 570 beaches into memory at once
        Beach::where('is_active', true)
            ->select('id')
            ->chunk(20, function ($beaches) use ($infoAgua, $engine, $resolver) {
                foreach ($beaches as $beach) {
                    try {
                        $beach->load('translations');
                        $this->processBeach($beach, $infoAgua, $engine, $resolver);
                    } catch (\Exception $e) {
                        logger()->warning('InfoÁgua fetch failed for beach '.$beach->id, [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    private function processBeach(Beach $beach, InfoAguaClient $infoAgua, PredictionEngine $engine, ConsensusResolver $resolver): void
    {
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
                'source' => 'apa_arcgis',
                'sampled_at' => $result['sampled_at'] ?? now(),
            ]);
        }

        $payload = $engine->calculateWithPayload($beach);
        $payload['prediction']->save();

        $resolver->resolveCurrentStatus($beach, $payload);
    }
}
