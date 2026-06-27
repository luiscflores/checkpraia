<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'beach_id', 'coast_orientation', 'exposure_direction', 'exposure_factor',
    'shelter_factor', 'beach_type', 'bottom_type', 'slope', 'current_risk',
    'has_jetties', 'has_bays', 'has_cliffs', 'has_rocks', 'river_influence'
])]
class BeachFeature extends Model
{
    protected function casts(): array
    {
        return [
            'exposure_factor' => 'decimal:2',
            'shelter_factor' => 'decimal:2',
            'has_jetties' => 'boolean',
            'has_bays' => 'boolean',
            'has_cliffs' => 'boolean',
            'has_rocks' => 'boolean',
            'river_influence' => 'boolean',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
