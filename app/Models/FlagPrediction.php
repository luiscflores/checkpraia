<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'beach_id', 'green_probability', 'yellow_probability', 'red_probability',
    'selected_flag', 'confidence', 'algorithm_version', 'calculated_at'
])]
class FlagPrediction extends Model
{
    protected function casts(): array
    {
        return [
            'green_probability' => 'integer',
            'yellow_probability' => 'integer',
            'red_probability' => 'integer',
            'confidence' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
