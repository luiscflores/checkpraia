<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'beach_id', 'wave_height_min', 'wave_height_max', 'wave_period_min',
    'wave_period_max', 'wave_direction', 'water_temp', 'forecasted_at'
])]
class OceanForecast extends Model
{
    protected function casts(): array
    {
        return [
            'wave_height_min' => 'decimal:2',
            'wave_height_max' => 'decimal:2',
            'wave_period_min' => 'decimal:2',
            'wave_period_max' => 'decimal:2',
            'water_temp' => 'decimal:1',
            'forecasted_at' => 'datetime',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
