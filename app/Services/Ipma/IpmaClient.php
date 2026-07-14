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
            $cfg = config('services.openmeteo');
            $response = Http::retry($cfg['retry_times'], $cfg['retry_gap'])->timeout($cfg['timeout'])->get($cfg['weather_url'], [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,precipitation,wind_speed_10m,wind_direction_10m,uv_index,visibility,weather_code',
                'wind_speed_unit' => $cfg['wind_speed_unit'],
                'timezone' => $cfg['timezone'],
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

                    $airTemp = (float) ($current['temperature_2m'] ?? 20.0);

                    return [
                        'wind_speed' => $windSpeedKt,
                        'wind_direction' => $windDirection,
                        'precipitation' => (float) ($current['precipitation'] ?? 0.0),
                        'visibility' => $visibility,
                        'temp' => $airTemp,
                        'uv_index' => $uv,
                        'weather_code' => (int) ($current['weather_code'] ?? 0),
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
            $cfg = config('services.openmeteo');
            $response = Http::retry($cfg['retry_times'], $cfg['retry_gap'])->timeout($cfg['timeout'])->get($cfg['marine_url'], [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'wave_height,wave_period,wave_direction,sea_surface_temperature',
                'timezone' => $cfg['timezone'],
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
     * Fetch 7-day daily weather forecast for a beach.
     */
    public function getDailyForecast(float $latitude, float $longitude): array
    {
        try {
            $cfg = config('services.openmeteo');
            $response = Http::retry($cfg['retry_times'], $cfg['retry_gap'])->timeout($cfg['timeout'])->get($cfg['weather_url'], [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max,wind_direction_10m_dominant,uv_index_max',
                'timezone' => $cfg['timezone'],
                'forecast_days' => 7,
            ]);

            if ($response->successful()) {
                $daily = $response->json('daily', []);
                $dates = $daily['time'] ?? [];
                $result = [];

                for ($i = 0, $len = count($dates); $i < $len; $i++) {
                    $code = (int) ($daily['weather_code'][$i] ?? 0);
                    $result[] = [
                        'date' => $dates[$i],
                        'weather_code' => $code,
                        'condition' => $this->getWeatherCondition($code),
                        'icon' => $this->getWeatherIcon($code),
                        'temp_max' => (float) ($daily['temperature_2m_max'][$i] ?? 0),
                        'temp_min' => (float) ($daily['temperature_2m_min'][$i] ?? 0),
                        'precipitation' => (float) ($daily['precipitation_sum'][$i] ?? 0),
                        'precipitation_probability' => (int) ($daily['precipitation_probability_max'][$i] ?? 0),
                        'wind_speed' => (float) ($daily['wind_speed_10m_max'][$i] ?? 0),
                        'wind_direction' => $this->getCompassDirection((int) ($daily['wind_direction_10m_dominant'][$i] ?? 0)),
                        'uv_index' => (float) ($daily['uv_index_max'][$i] ?? 0),
                    ];
                }

                return $result;
            }

            return [];
        } catch (\Exception $e) {
            logger()->error('Open-Meteo daily forecast failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Map WMO weather code to a condition string key.
     */
    public function getWeatherCondition(int $code): string
    {
        return match (true) {
            $code === 0 => 'clear',
            $code <= 3 => 'partly_cloudy',
            $code <= 49 => 'fog',
            $code <= 59 => 'drizzle',
            $code <= 69 => 'rain',
            $code <= 79 => 'snow',
            $code <= 82 => 'rain_showers',
            $code <= 86 => 'snow_showers',
            $code <= 99 => 'thunderstorm',
            default => 'clear',
        };
    }

    /**
     * Map WMO weather code to an emoji icon.
     */
    public function getWeatherIcon(int $code): string
    {
        return match (true) {
            $code === 0 => '☀️',
            $code <= 3 => '⛅',
            $code <= 49 => '🌫️',
            $code <= 59 => '🌦️',
            $code <= 69 => '🌧️',
            $code <= 79 => '❄️',
            $code <= 82 => '🌧️',
            $code <= 86 => '🌨️',
            $code <= 99 => '⛈️',
            default => '☀️',
        };
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
