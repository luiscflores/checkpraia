<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'beach_id', 'flag', 'source', 'confidence',
    'wave_height', 'wind_speed', 'water_temp', 'air_temp',
    'water_quality', 'captured_at', 'vote_time',
])]
class BeachHourlySnapshot extends Model
{
    protected function casts(): array
    {
        return [
            'confidence' => 'integer',
            'wave_height' => 'decimal:2',
            'wind_speed' => 'decimal:2',
            'water_temp' => 'decimal:1',
            'air_temp' => 'decimal:1',
            'captured_at' => 'datetime',
            'vote_time' => 'datetime',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
