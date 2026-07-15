<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'beach_id', 'exposure_factor', 'shelter_factor', 'current_risk_factor',
    'wave_height_weight', 'wave_period_weight', 'wave_direction_weight',
    'wind_weight', 'tide_weight', 'warning_weight', 'water_quality_weight',
    'algorithm_version',
])]
class BeachPredictionProfile extends Model
{
    protected function casts(): array
    {
        return [
            'exposure_factor' => 'decimal:2',
            'shelter_factor' => 'decimal:2',
            'current_risk_factor' => 'decimal:2',
            'wave_height_weight' => 'decimal:2',
            'wave_period_weight' => 'decimal:2',
            'wave_direction_weight' => 'decimal:2',
            'wind_weight' => 'decimal:2',
            'tide_weight' => 'decimal:2',
            'warning_weight' => 'decimal:2',
            'water_quality_weight' => 'decimal:2',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
