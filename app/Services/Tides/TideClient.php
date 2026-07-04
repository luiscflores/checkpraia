<?php

namespace App\Services\Tides;

use App\Models\Beach;
use App\Models\TideForecast;
use Carbon\Carbon;

class TideClient
{
    private const SECONDS_PER_DAY = 86400;

    private const DEG2RAD = M_PI / 180.0;

    private const RAD2DEG = 180.0 / M_PI;

    private const M2_PERIOD_HOURS = 12.4206;

    private const M2_SPEED = 28.984104;

    private const S2_SPEED = 30.000000;

    private array $stationParams;

    public function __construct()
    {
        $this->stationParams = config('services.tide_stations', []);
    }

    public function getTideForecasts(Beach $beach): array
    {
        $stationId = $beach->tide_station_id ?? 'default';
        $params = $this->getStationParams($stationId, $beach->latitude, $beach->longitude);

        $now = Carbon::now('UTC');
        $lookbackDays = config('services.tides.lookback_days', 1);
        $lookaheadDays = config('services.tides.lookahead_days', 7);
        $startTime = $now->copy()->startOfDay()->subDays($lookbackDays);
        $endTime = $now->copy()->addDays($lookaheadDays);

        $tides = $this->computeTides($params, $startTime, $endTime);

        $forecasts = [];
        foreach ($tides as $tide) {
            $forecasts[] = [
                'tide_station_id' => $stationId,
                'tide_time' => $tide['time'],
                'tide_type' => $tide['type'],
                'tide_height' => round($tide['height'], 2),
                'moon_phase' => TideForecast::moonPhase($tide['time']),
            ];
        }

        return $forecasts;
    }

    private function getStationParams(?string $stationId, float $lat, float $lon): array
    {
        if ($stationId && isset($this->stationParams[$stationId])) {
            return $this->stationParams[$stationId];
        }

        $closest = null;
        $minDist = INF;
        foreach ($this->stationParams as $id => $params) {
            if (!isset($params['lat'], $params['lon'])) {
                continue;
            }
            $dist = $this->haversine($lat, $lon, $params['lat'], $params['lon']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closest = $params;
            }
        }

        if ($closest) {
            return $closest;
        }

        $range = $this->estimateRangeByLatitude($lat);

        return [
            'range' => $range,
            'lat' => $lat,
            'lon' => $lon,
        ];
    }

    private function computeTides(array $params, Carbon $startTime, Carbon $endTime): array
    {
        $meanRange = $params['range'] ?? 2.5;
        $halfRange = $meanRange / 2.0;

        $refTime = Carbon::parse($startTime->format('Y-m-d') . ' 00:00:00', 'UTC');
        $refJulian = $this->toJulianDay($refTime);

        $tideIntervalHours = self::M2_PERIOD_HOURS;
        $cycleHours = $tideIntervalHours * 2.0;

        $startJulian = $this->toJulianDay($startTime);
        $startOffsetHours = $startTime->diffInHours($refTime, false);

        $stepHours = 0.25;

        $samples = [];
        $currentTime = $startTime->copy();
        while ($currentTime->lte($endTime)) {
            $hoursSinceRef = $refTime->diffInHours($currentTime, false);
            $julianDay = $refJulian + $hoursSinceRef / 24.0;

            $height = $this->tideHeightAt($hoursSinceRef, $julianDay, $halfRange, $params);

            $samples[] = [
                'time' => $currentTime->copy(),
                'height' => $height,
            ];

            $currentTime->addMinutes(15);
        }

        $extrema = $this->findExtrema($samples);

        usort($extrema, fn($a, $b) => $a['time']->timestamp <=> $b['time']->timestamp);

        return $extrema;
    }

    private function tideHeightAt(float $hoursSinceRef, float $julianDay, float $halfRange, array $params): float
    {
        $hoursToRad = (2.0 * M_PI) / (self::M2_PERIOD_HOURS * 2.0);

        $m2Phase = self::M2_SPEED * $hoursSinceRef * self::DEG2RAD;
        $s2Phase = self::S2_SPEED * $hoursSinceRef * self::DEG2RAD;

        $moonAge = $this->moonAge($julianDay);
        $springNapeFactor = cos(2.0 * M_PI * ($moonAge / 29.53058867));

        $amplitudeMod = 1.0 + 0.3 * $springNapeFactor;

        $m2Amp = $halfRange * 0.65 * $amplitudeMod;
        $s2Amp = $halfRange * 0.25 * $amplitudeMod;

        $height = $m2Amp * cos($m2Phase - 2.06)
            + $s2Amp * cos($s2Phase + 2.74);

        return $height + ($params['msl'] ?? 2.15);
    }

    private function findExtrema(array $samples): array
    {
        $extrema = [];
        $count = count($samples);

        for ($i = 1; $i < $count - 1; $i++) {
            $prev = $samples[$i - 1]['height'];
            $curr = $samples[$i]['height'];
            $next = $samples[$i + 1]['height'];

            $isHigh = $curr > $prev && $curr >= $next;
            $isLow = $curr < $prev && $curr <= $next;

            if ($isHigh) {
                $parabolic = $this->parabolicInterpolation(
                    $samples[$i - 1]['time']->timestamp,
                    $prev,
                    $samples[$i]['time']->timestamp,
                    $curr,
                    $samples[$i + 1]['time']->timestamp,
                    $next
                );
                $extrema[] = [
                    'type' => 'high',
                    'height' => $parabolic['height'],
                    'time' => Carbon::createFromTimestamp($parabolic['time'], 'UTC'),
                ];
            } elseif ($isLow) {
                $parabolic = $this->parabolicInterpolation(
                    $samples[$i - 1]['time']->timestamp,
                    $prev,
                    $samples[$i]['time']->timestamp,
                    $curr,
                    $samples[$i + 1]['time']->timestamp,
                    $next
                );
                $extrema[] = [
                    'type' => 'low',
                    'height' => $parabolic['height'],
                    'time' => Carbon::createFromTimestamp($parabolic['time'], 'UTC'),
                ];
            }
        }

        return $extrema;
    }

    private function parabolicInterpolation(float $t1, float $y1, float $t2, float $y2, float $t3, float $y3): array
    {
        $denom = ($t1 - $t2) * ($t1 - $t3) * ($t2 - $t3);
        if (abs($denom) < 1e-10) {
            return ['time' => $t2, 'height' => $y2];
        }

        $A = ($t3 * ($y2 - $y1) + $t2 * ($y1 - $y3) + $t1 * ($y3 - $y2)) / $denom;
        $B = ($t3 * $t3 * ($y1 - $y2) + $t2 * $t2 * ($y3 - $y1) + $t1 * $t1 * ($y2 - $y3)) / $denom;

        if (abs($A) < 1e-10) {
            return ['time' => $t2, 'height' => $y2];
        }

        $tExtreme = -$B / (2 * $A);
        $yExtreme = $A * $tExtreme * $tExtreme + $B * $tExtreme + $y1 - $A * $t1 * $t1 - $B * $t1;

        return ['time' => $tExtreme, 'height' => $yExtreme];
    }

    private function moonAge(float $julianDay): float
    {
        $julianDay2000 = 2451545.0;
        $daysSince2000 = $julianDay - $julianDay2000;
        $newMoonRef = 2451550.1;

        $age = fmod($daysSince2000 - $newMoonRef, 29.53058867);
        if ($age < 0) {
            $age += 29.53058867;
        }

        return $age;
    }

    private function estimateRangeByLatitude(float $lat): float
    {
        $lat = abs($lat);

        if ($lat >= 41.0) {
            return 3.5;
        }
        if ($lat >= 39.5) {
            return 3.0;
        }
        if ($lat >= 38.0) {
            return 2.5;
        }
        if ($lat >= 37.0) {
            return 2.3;
        }

        return 2.0;
    }

    private function toJulianDay(Carbon $date): float
    {
        $y = (int) $date->format('Y');
        $m = (int) $date->format('n');
        $d = (int) $date->format('j');

        if ($m <= 2) {
            $y--;
            $m += 12;
        }

        $a = (int) floor($y / 100);
        $b = 2 - $a + (int) floor($a / 4);

        $jd = (int) floor(365.25 * ($y + 4716)) + (int) floor(30.6001 * ($m + 1)) + $d + $b - 1524.5;

        $h = (int) $date->format('H');
        $i = (int) $date->format('i');
        $s = (int) $date->format('s');

        $jd += ($h + $i / 60.0 + $s / 3600.0) / 24.0;

        return $jd;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = ($lat2 - $lat1) * self::DEG2RAD;
        $dLon = ($lon2 - $lon1) * self::DEG2RAD;
        $a = sin($dLat / 2) ** 2 + cos($lat1 * self::DEG2RAD) * cos($lat2 * self::DEG2RAD) * sin($dLon / 2) ** 2;

        return 6371.0 * 2.0 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
