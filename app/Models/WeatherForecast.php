<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'beach_id', 'wind_speed', 'wind_direction', 'precipitation',
    'visibility', 'temp', 'uv_index', 'jellyfish_risk', 'forecasted_at'
])]
class WeatherForecast extends Model
{
    protected function casts(): array
    {
        return [
            'wind_speed' => 'decimal:2',
            'precipitation' => 'decimal:1',
            'temp' => 'decimal:1',
            'uv_index' => 'decimal:1',
            'forecasted_at' => 'datetime',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
