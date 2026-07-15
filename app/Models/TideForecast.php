<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tide_station_id', 'tide_time', 'tide_type', 'tide_height', 'moon_phase'])]
class TideForecast extends Model
{
    protected function casts(): array
    {
        return [
            'tide_time' => 'datetime',
            'tide_height' => 'decimal:2',
            'moon_phase' => 'decimal:2',
        ];
    }

    public static function moonPhase(?Carbon $date = null): float
    {
        $date = $date ?: Carbon::now();
        $timestamp = $date->getTimestamp();

        $knownNewMoon = Carbon::parse('2023-01-22 20:53:00', 'UTC')->getTimestamp();
        $synodicMonth = 29.53058867;

        $daysSince = ($timestamp - $knownNewMoon) / 86400;
        $phase = fmod($daysSince, $synodicMonth) / $synodicMonth;

        if ($phase < 0) {
            $phase += 1.0;
        }

        return round((float) $phase, 2);
    }

    public static function moonPhaseName(float $phase): string
    {
        return match (true) {
            $phase < 0.06 || $phase >= 0.94 => 'Lua Nova',
            $phase < 0.19 => 'Lua Crescente',
            $phase < 0.31 => 'Quarto Crescente',
            $phase < 0.44 => 'Lua Gibosa Crescente',
            $phase < 0.56 => 'Lua Cheia',
            $phase < 0.69 => 'Lua Gibosa Minguante',
            $phase < 0.81 => 'Quarto Minguante',
            default => 'Lua Minguante',
        };
    }

    public static function moonPhaseIcon(float $phase): string
    {
        return match (true) {
            $phase < 0.06 || $phase >= 0.94 => '🌑',
            $phase < 0.19 => '🌒',
            $phase < 0.31 => '🌓',
            $phase < 0.44 => '🌔',
            $phase < 0.56 => '🌕',
            $phase < 0.69 => '🌖',
            $phase < 0.81 => '🌗',
            default => '🌘',
        };
    }

    public static function upcomingMoonPhases(int $limit = 4): array
    {
        $currentPhase = self::moonPhase();
        $synodicMonth = 29.53058867;
        $targetPhases = [0.0, 0.25, 0.5, 0.75];
        $upcoming = [];
        $now = Carbon::now();

        foreach ($targetPhases as $targetPhase) {
            if ($targetPhase > $currentPhase) {
                $daysUntil = ($targetPhase - $currentPhase) * $synodicMonth;
            } else {
                $daysUntil = (1.0 - $currentPhase + $targetPhase) * $synodicMonth;
            }

            $date = $now->copy()->addDays($daysUntil);

            $upcoming[] = [
                'phase' => round($targetPhase, 2),
                'name' => self::moonPhaseName($targetPhase),
                'icon' => self::moonPhaseIcon($targetPhase),
                'date' => $date,
                'days_until' => round($daysUntil, 1),
            ];
        }

        usort($upcoming, fn ($a, $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        return array_slice($upcoming, 0, $limit);
    }
}
