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
            // Query Open-Meteo for weather, with 3 retries (150ms gap) and 6s timeout
            $response = Http::retry(3, 150)->timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,precipitation,wind_speed_10m,wind_direction_10m,uv_index,visibility',
                'wind_speed_unit' => 'kn',
                'timezone' => 'Europe/London',
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                $current = $payload['current'] ?? null;

                if ($current) {
                    $windSpeedKt = (float) ($current['wind_speed_10m'] ?? 0.0);

                    // Convert wind degree to compass direction
                    $windDegree = (int) ($current['wind_direction_10m'] ?? 0);
                    $windDirection = $this->getCompassDirection($windDegree);
                    
                    $uv = (float) ($current['uv_index'] ?? 0.0);

                    // Visibility classification
                    $visibilityMeters = (float) ($current['visibility'] ?? 10000.0);
                    $visibility = 'Boa';
                    if ($visibilityMeters < 2000.0) {
                        $visibility = 'Fraca';
                    } elseif ($visibilityMeters < 10000.0) {
                        $visibility = 'Moderada';
                    }

                    return [
                        'wind_speed' => $windSpeedKt,
                        'wind_direction' => $windDirection,
                        'precipitation' => (float) ($current['precipitation'] ?? 0.0),
                        'visibility' => $visibility,
                        'temp' => (float) ($current['temperature_2m'] ?? 20.0),
                        'uv_index' => $uv,
                        'jellyfish_risk' => null,
                        'forecasted_at' => now(),
                    ];
                }
            }
            throw new \Exception('Weather forecast data is missing in the API response.');
        } catch (\Exception $e) {
            logger()->error('Open-Meteo Weather API failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch marine wave state specifically for the beach coordinates.
     */
    public function getOceanForecast(float $latitude, float $longitude): array
    {
        try {
            // Query Open-Meteo Marine API with 3 retries (150ms gap) and 6s timeout
            $response = Http::retry(3, 150)->timeout(6)->get('https://marine-api.open-meteo.com/v1/marine', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'wave_height,wave_period,wave_direction,sea_surface_temperature',
                'timezone' => 'Europe/London',
            ]);

            if ($response->successful()) {
                $payload = $response->json();
                $current = $payload['current'] ?? null;

                if ($current) {
                    $height = (float) ($current['wave_height'] ?? 0.0);
                    $period = (float) ($current['wave_period'] ?? 0.0);
                    $waveDegree = (int) ($current['wave_direction'] ?? 270);
                    $waterTemp = (float) ($current['sea_surface_temperature'] ?? 0.0);

                    return [
                        'wave_height_min' => $height,
                        'wave_height_max' => $height,
                        'wave_period_min' => $period,
                        'wave_period_max' => $period,
                        'wave_direction' => $this->getCompassDirection($waveDegree),
                        'water_temp' => $waterTemp,
                        'forecasted_at' => now(),
                    ];
                }
            }
            throw new \Exception('Ocean forecast data is missing in the API response.');
        } catch (\Exception $e) {
            logger()->error('Open-Meteo Marine API failed: ' . $e->getMessage());
            throw $e;
        }
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
