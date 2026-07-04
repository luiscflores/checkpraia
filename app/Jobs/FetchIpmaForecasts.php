<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\OceanForecast;
use App\Models\Setting;
use App\Models\WeatherForecast;
use App\Services\Ipma\IpmaClient;
use App\Domain\Forecasting\PredictionEngine;
use App\Domain\Community\ConsensusResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchIpmaForecasts implements ShouldQueue
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

    public function handle(): void
    {
        if ($this->beach) {
            $this->processBeach($this->beach);
        } else {
            Setting::set('last_ipma_sync', now()->toIso8601String());
            Beach::where('is_active', true)->chunkById(50, function ($beaches) {
                foreach ($beaches as $beach) {
                    self::dispatch($beach);
                }
            });
        }
    }

    /**
     * Process forecast update for a single beach.
     */
    private function processBeach(Beach $beach): void
    {
        $ipma = new IpmaClient();
        $engine = new PredictionEngine();
        $resolver = new ConsensusResolver();

        // 1. Fetch and store weather forecast
        $weatherData = $ipma->getWeatherForecast($beach->latitude, $beach->longitude);
        WeatherForecast::create([
            'beach_id' => $beach->id,
            'wind_speed' => $weatherData['wind_speed'],
            'wind_direction' => $weatherData['wind_direction'],
            'precipitation' => $weatherData['precipitation'],
            'visibility' => $weatherData['visibility'],
            'temp' => $weatherData['temp'],
            'uv_index' => $weatherData['uv_index'] ?? null,
            'forecasted_at' => $weatherData['forecasted_at'],
        ]);

        // 2. Fetch and store ocean forecast
        $oceanData = $ipma->getOceanForecast($beach->latitude, $beach->longitude);
        OceanForecast::create([
            'beach_id' => $beach->id,
            'wave_height_min' => $oceanData['wave_height_min'],
            'wave_height_max' => $oceanData['wave_height_max'],
            'wave_period_min' => $oceanData['wave_period_min'],
            'wave_period_max' => $oceanData['wave_period_max'],
            'wave_direction' => $oceanData['wave_direction'],
            'water_temp' => $oceanData['water_temp'],
            'forecasted_at' => $oceanData['forecasted_at'],
        ]);


        // 2.5. Fetch and store tide forecast
        $tideClient = new \App\Services\Tides\TideClient();
        $tides = $tideClient->getTideForecasts($beach);

        // Atomically replace tide forecasts for this station
        \Illuminate\Support\Facades\DB::transaction(function () use ($beach, $tides) {
            \App\Models\TideForecast::where('tide_station_id', $beach->tide_station_id)->delete();

            foreach ($tides as $tideData) {
                \App\Models\TideForecast::create([
                    'tide_station_id' => $tideData['tide_station_id'],
                    'tide_time' => $tideData['tide_time'],
                    'tide_type' => $tideData['tide_type'],
                    'tide_height' => $tideData['tide_height'],
                    'moon_phase' => $tideData['moon_phase'] ?? null,
                ]);
            }
        });

        // 3. Recalculate automatic prediction
        $prediction = $engine->calculate($beach);
        $prediction->save();

        // 4. Update the cached current status (combining prediction, community, and alerts)
        $resolver->resolveCurrentStatus($beach);
    }
}
