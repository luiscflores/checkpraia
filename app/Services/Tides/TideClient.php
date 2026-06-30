<?php

namespace App\Services\Tides;

use Illuminate\Support\Facades\Http;
use App\Models\Beach;
use Carbon\Carbon;

class TideClient
{
    public function getTideForecasts(Beach $beach): array
    {
        $lat = (float) $beach->latitude;
        $lon = (float) $beach->longitude;

        $minLon = $lon - 0.15;
        $maxLon = $lon + 0.15;
        $minLat = $lat - 0.15;
        $maxLat = $lat + 0.15;

        try {
            $response = Http::timeout(6)->get('https://api-features.hidrografico.pt/collections/tide_obs_data_nrt_l2/items', [
                'f' => 'json',
                'bbox' => "{$minLon},{$minLat},{$maxLon},{$maxLat}",
                'limit' => 50,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $features = $data['features'] ?? [];

                if (!empty($features)) {
                    $observations = [];
                    foreach ($features as $f) {
                        $props = $f['properties'] ?? [];
                        if (isset($props['sea_surface_height']) && isset($props['date_time'])) {
                            if (isset($props['sea_surface_height_qc']) && $props['sea_surface_height_qc'] !== '1') {
                                continue;
                            }
                            $observations[] = [
                                'height' => (float) $props['sea_surface_height'],
                                'time' => Carbon::parse($props['date_time']),
                            ];
                        }
                    }

                    usort($observations, fn($a, $b) => $a['time']->timestamp <=> $b['time']->timestamp);

                    if (count($observations) >= 3) {
                        return $this->extrapolateTides($observations, $beach->tide_station_id);
                    }
                }
            }
        } catch (\Exception $e) {
            logger()->warning('Tide API call failed: ' . $e->getMessage());
        }

        return [];
    }

    private function extrapolateTides(array $observations, string $stationId): array
    {
        $extrema = [];
        for ($i = 1; $i < count($observations) - 1; $i++) {
            $prev = $observations[$i - 1]['height'];
            $curr = $observations[$i]['height'];
            $next = $observations[$i + 1]['height'];

            if ($curr > $prev && $curr > $next) {
                $extrema[] = ['type' => 'high', 'height' => $curr, 'time' => $observations[$i]['time']];
            } elseif ($curr < $prev && $curr < $next) {
                $extrema[] = ['type' => 'low', 'height' => $curr, 'time' => $observations[$i]['time']];
            }
        }

        if (empty($extrema)) {
            return [];
        }

        usort($extrema, fn($a, $b) => $a['time']->timestamp <=> $b['time']->timestamp);

        $latestExtremum = end($extrema);
        $refTime = $latestExtremum['time'];
        $refType = $latestExtremum['type'];

        $highs = array_filter($extrema, fn($e) => $e['type'] === 'high');
        $lows = array_filter($extrema, fn($e) => $e['type'] === 'low');

        $avgHigh = !empty($highs) ? array_sum(array_column($highs, 'height')) / count($highs) : 3.0;
        $avgLow = !empty($lows) ? array_sum(array_column($lows, 'height')) / count($lows) : 0.5;

        $forecasts = [];
        $currentTime = Carbon::now()->startOfDay()->subDay();
        $endTime = Carbon::now()->addDays(2);

        $tideTime = clone $refTime;
        $tideType = $refType;

        while ($tideTime->gt($currentTime)) {
            $tideTime->subMinutes(372);
            $tideType = $tideType === 'high' ? 'low' : 'high';
        }

        while ($tideTime->lt($endTime)) {
            $tideTime->addMinutes(372);
            $tideType = $tideType === 'high' ? 'low' : 'high';

            if ($tideTime->between($currentTime, $endTime)) {
                $height = $tideType === 'high' ? $avgHigh : $avgLow;
                $height += sin($tideTime->timestamp / 100000) * 0.15;

                $forecasts[] = [
                    'tide_station_id' => $stationId,
                    'tide_time' => clone $tideTime,
                    'tide_type' => $tideType,
                    'tide_height' => round($height, 2),
                ];
            }
        }

        return $forecasts;
    }
}
