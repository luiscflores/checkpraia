<?php

namespace App\Jobs;

use App\Models\Beach;
use App\Models\BeachHourlySnapshot;
use App\Models\WaterQualitySnapshot;
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
        $now = now();
        $capturedAt = $now->copy()->startOfHour();

        // Process in chunks of 50 to avoid loading all 570 beaches into memory at once
        Beach::where('is_active', true)
            ->with(['currentStatus', 'latestOceanForecast', 'latestWeatherForecast'])
            ->chunk(50, function ($beaches) use ($now, $capturedAt) {
                if ($beaches->isEmpty()) {
                    return;
                }

                $beachIds = $beaches->pluck('id');

                // Bulk eager-load latest quality snapshot per beach in 1 query
                $latestQualities = WaterQualitySnapshot::whereIn('beach_id', $beachIds)
                    ->whereRaw('id IN (SELECT id FROM water_quality_snapshots wqs2 WHERE wqs2.beach_id = water_quality_snapshots.beach_id ORDER BY sampled_at DESC, id DESC LIMIT 1)')
                    ->get()
                    ->keyBy('beach_id');

                $snapshots = [];

                foreach ($beaches as $beach) {
                    $status = $beach->currentStatus;
                    $ocean = $beach->latestOceanForecast;
                    $weather = $beach->latestWeatherForecast;
                    $latestQuality = $latestQualities->get($beach->id);

                    $snapshots[] = [
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
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                BeachHourlySnapshot::upsert(
                    $snapshots,
                    ['beach_id', 'captured_at'],
                    ['flag', 'source', 'confidence', 'wave_height', 'wind_speed', 'water_temp', 'air_temp', 'water_quality', 'updated_at']
                );
            });

        BeachHourlySnapshot::where('captured_at', '<', $now->copy()->startOfDay())->delete();
    }
}
