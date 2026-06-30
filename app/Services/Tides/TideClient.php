<?php

namespace App\Services\Tides;

use Illuminate\Support\Facades\Http;
use App\Models\Beach;
use Carbon\Carbon;

class TideClient
{
    /**
     * Get tide forecasts for the beach's station, either from the OGC API or fallback generator.
     */
    public function getTideForecasts(Beach $beach): array
    {
        $lat = (float) $beach->latitude;
        $lon = (float) $beach->longitude;

        // Define a small bounding box (approx. 15km) around the beach coordinates
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
                            $observations[] = [
                                'height' => (float) $props['sea_surface_height'],
                                'time' => Carbon::parse($props['date_time']),
                            ];
                        }
                    }

                    // Sort observations chronologically
                    usort($observations, fn($a, $b) => $a['time']->timestamp <=> $b['time']->timestamp);

                    if (count($observations) >= 3) {
                        return $this->extrapolateTides($observations, $beach->tide_station_id);
                    }
                }
            }
        } catch (\Exception $e) {
            logger()->warning('Tide API call failed, using fallback generator: ' . $e->getMessage());
        }

        // Fallback: Deterministic tide simulation if API fails or has no data
        return $this->generateFallbackTides($beach->tide_station_id);
    }

    /**
     * Extrapolate high and low tides based on recent observed peaks and troughs.
     */
    private function extrapolateTides(array $observations, string $stationId): array
    {
        $extrema = [];
        for ($i = 1; $i < count($observations) - 1; $i++) {
            $prev = $observations[$i - 1]['height'];
            $curr = $observations[$i]['height'];
            $next = $observations[$i + 1]['height'];

            if ($curr > $prev && $curr > $next) {
                // Local maximum (High Tide)
                $extrema[] = ['type' => 'high', 'height' => $curr, 'time' => $observations[$i]['time']];
            } elseif ($curr < $prev && $curr < $next) {
                // Local minimum (Low Tide)
                $extrema[] = ['type' => 'low', 'height' => $curr, 'time' => $observations[$i]['time']];
            }
        }

        if (empty($extrema)) {
            return $this->generateFallbackTides($stationId);
        }

        // Sort extrema chronologically
        usort($extrema, fn($a, $b) => $a['time']->timestamp <=> $b['time']->timestamp);

        // Take the latest extremum as our reference point
        $latestExtremum = end($extrema);
        $refTime = $latestExtremum['time'];
        $refType = $latestExtremum['type'];

        // Determine average high/low heights
        $highs = array_filter($extrema, fn($e) => $e['type'] === 'high');
        $lows = array_filter($extrema, fn($e) => $e['type'] === 'low');
        
        $avgHigh = !empty($highs) ? array_sum(array_column($highs, 'height')) / count($highs) : 3.0;
        $avgLow = !empty($lows) ? array_sum(array_column($lows, 'height')) / count($lows) : 0.5;

        // Generate forecasts for the next 48 hours starting from the reference point
        $forecasts = [];
        $currentTime = Carbon::now()->startOfDay()->subDay(); // Start from yesterday to cover all today's tides
        $endTime = Carbon::now()->addDays(2); // Project 2 days into the future

        $tideTime = clone $refTime;
        $tideType = $refType;

        // Adjust tideTime backward to cover our start window
        while ($tideTime->gt($currentTime)) {
            $tideTime->subMinutes(372); // Move back 6 hours and 12 minutes
            $tideType = $tideType === 'high' ? 'low' : 'high';
        }

        // Now move forward and collect predictions in our window
        while ($tideTime->lt($endTime)) {
            $tideTime->addMinutes(372); // Move forward 6 hours and 12 minutes
            $tideType = $tideType === 'high' ? 'low' : 'high';

            if ($tideTime->between($currentTime, $endTime)) {
                $height = $tideType === 'high' ? $avgHigh : $avgLow;
                // Add minor variation based on time
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

    /**
     * Fallback deterministic tide generator for Portugal (semidiurnal).
     */
    private function generateFallbackTides(string $stationId): array
    {
        $hash = crc32($stationId);
        $offsetMinutes = abs($hash) % 720; // Offset between 0 and 12 hours

        $forecasts = [];
        $startTime = Carbon::now()->startOfDay()->subDay();
        $endTime = Carbon::now()->addDays(2);

        $tideIntervalMinutes = 372; // 6h 12m
        $highHeight = 3.2 + (sin($hash) * 0.3);
        $lowHeight = 0.6 + (cos($hash) * 0.2);

        $tideTime = clone $startTime;
        $tideTime->addMinutes($offsetMinutes);
        $tideType = 'high';

        while ($tideTime->lt($endTime)) {
            $forecasts[] = [
                'tide_station_id' => $stationId,
                'tide_time' => clone $tideTime,
                'tide_type' => $tideType,
                'tide_height' => round($tideType === 'high' ? $highHeight : $lowHeight, 2),
            ];

            $tideTime->addMinutes($tideIntervalMinutes);
            $tideType = $tideType === 'high' ? 'low' : 'high';
        }

        return $forecasts;
    }
}
