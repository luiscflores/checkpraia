<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\BeachHourlySnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaptureHourlySnapshots implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $beaches = Beach::with([
            'currentStatus',
            'latestOceanForecast',
            'latestWeatherForecast',
        ])->where('is_active', true)->get();

        $now = now();
        $capturedAt = $now->copy()->startOfHour();

        foreach ($beaches as $beach) {
            $status = $beach->currentStatus;
            $ocean = $beach->latestOceanForecast;
            $weather = $beach->latestWeatherForecast;
            $latestQuality = $beach->qualitySnapshots()
                ->latest('sampled_at')
                ->latest('id')
                ->first();

            BeachHourlySnapshot::create([
                'beach_id' => $beach->id,
                'flag' => $status?->flag ?? 'gray',
                'source' => $status?->source ?? 'prediction',
                'confidence' => $status?->confidence ?? 0,
                'wave_height' => $ocean ? (($ocean->wave_height_min + $ocean->wave_height_max) / 2) : null,
                'wind_speed' => $weather?->wind_speed,
                'water_temp' => $ocean?->water_temp,
                'air_temp' => $weather?->temp,
                'water_quality' => $latestQuality?->quality_class,
                'captured_at' => $capturedAt,
            ]);
        }

        BeachHourlySnapshot::where('captured_at', '<', $now->copy()->startOfDay())->delete();
    }
}
