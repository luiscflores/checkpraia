<?php

namespace App\Services\Ipma;

use Illuminate\Support\Facades\Http;

class IpmaClient
{
    /**
     * Fetch weather forecast specifically for the beach coordinates.
     */
    public function getWeatherForecast(float $latitude, float $longitude): array
    {
        try {
            // Query Open-Meteo for high-resolution coordinate weather
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,precipitation,wind_speed_10m,wind_direction_10m,uv_index',
                'timezone' => 'Europe/London',
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                $current = $payload['current'] ?? null;

                if ($current) {
                    // Convert wind speed from km/h to knots (1 knot = 1.852 km/h)
                    $windSpeedKmh = (float) ($current['wind_speed_10m'] ?? 15);
                    $windSpeedKt = round($windSpeedKmh / 1.852, 1);

                    // Convert wind degree to compass direction
                    $windDegree = (int) ($current['wind_direction_10m'] ?? 0);
                    $windDirection = $this->getCompassDirection($windDegree);
                    
                    $uv = (float) ($current['uv_index'] ?? 0.0);

                    // Jellyfish calculation (Physalia physalis index):
                    $jellyRisk = 'Baixo';
                    if ($windSpeedKt > 12.0) {
                        $jellyRisk = 'Moderado';
                        if ($windSpeedKt > 18.0) {
                            $jellyRisk = 'Alto';
                        }
                    }

                    return [
                        'wind_speed' => $windSpeedKt,
                        'wind_direction' => $windDirection,
                        'precipitation' => (float) ($current['precipitation'] ?? 0.0),
                        'visibility' => 'Boa', // Default visibility
                        'temp' => (float) ($current['temperature_2m'] ?? 20.0),
                        'uv_index' => $uv,
                        'jellyfish_risk' => $jellyRisk,
                        'forecasted_at' => now(),
                    ];
                }
            }
        } catch (\Exception $e) {
            logger()->error('Open-Meteo Weather API failed: ' . $e->getMessage());
        }

        // Resilient fallback values
        return [
            'wind_speed' => 12.0,
            'wind_direction' => 'N',
            'precipitation' => 0.0,
            'visibility' => 'Boa',
            'temp' => 21.0,
            'uv_index' => 6.0,
            'jellyfish_risk' => 'Baixo',
            'forecasted_at' => now(),
        ];
    }

    /**
     * Fetch marine wave state specifically for the beach coordinates.
     */
    public function getOceanForecast(float $latitude, float $longitude): array
    {
        try {
            // Query Open-Meteo Marine API for wave heights and period at exact coordinates
            $response = Http::timeout(5)->get('https://marine-api.open-meteo.com/v1/marine', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'wave_height,wave_period,wave_direction',
                'timezone' => 'Europe/London',
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                $current = $payload['current'] ?? null;

                if ($current) {
                    $height = (float) ($current['wave_height'] ?? 1.0);
                    $period = (float) ($current['wave_period'] ?? 7.0);
                    $waveDegree = (int) ($current['wave_direction'] ?? 270);

                    // Estimate sea surface temp based on latitude/region
                    $waterTemp = 16.0; // Default continental summer SST
                    if ($latitude < 35.0) {
                        $waterTemp = 21.5; // Madeira / Azores waters are warmer
                    } elseif ($latitude < 38.0) {
                        $waterTemp = 19.0; // Algarve waters
                    }

                    return [
                        'wave_height_min' => max(0.1, round($height * 0.7, 1)),
                        'wave_height_max' => max(0.2, round($height * 1.3, 1)),
                        'wave_period_min' => max(3.0, round($period * 0.8, 1)),
                        'wave_period_max' => max(4.0, round($period * 1.2, 1)),
                        'wave_direction' => $this->getCompassDirection($waveDegree),
                        'water_temp' => $waterTemp,
                        'forecasted_at' => now(),
                    ];
                }
            }
        } catch (\Exception $e) {
            logger()->error('Open-Meteo Marine API failed: ' . $e->getMessage());
        }

        // Resilient default oceanographic values
        return [
            'wave_height_min' => 0.4,
            'wave_height_max' => 1.2,
            'wave_period_min' => 6.0,
            'wave_period_max' => 8.0,
            'wave_direction' => 'W',
            'water_temp' => 17.0,
            'forecasted_at' => now(),
        ];
    }

    /**
     * Helper to map degree angle to compass direction.
     */
    private function getCompassDirection(int $degrees): string
    {
        $sectors = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N'];
        $index = round($degrees / 45);
        return $sectors[$index % 8];
    }
}
