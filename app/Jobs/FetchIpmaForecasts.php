<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\OceanForecast;
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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ipma = new IpmaClient();
        $engine = new PredictionEngine();
        $resolver = new ConsensusResolver();

        $beaches = Beach::where('is_active', true)->get();

        foreach ($beaches as $beach) {
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
                'jellyfish_risk' => $weatherData['jellyfish_risk'] ?? null,
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

            // 3. Recalculate automatic prediction
            $prediction = $engine->calculate($beach);
            $prediction->save();

            // 4. Update the cached current status (combining prediction, community, and alerts)
            $resolver->resolveCurrentStatus($beach);
        }
    }
}
