<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['tide_station_id', 'tide_time', 'tide_type', 'tide_height'])]
class TideForecast extends Model
{
    protected function casts(): array
    {
        return [
            'tide_time' => 'datetime',
            'tide_height' => 'decimal:2',
        ];
    }
}
