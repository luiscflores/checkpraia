<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'beach_id', 'parking', 'bathrooms', 'showers', 'accessible',
    'amphibious_chair', 'first_aid', 'lifeguard_post', 'bar',
    'restaurant', 'surf_school', 'equipment_rental',
])]
class BeachService extends Model
{
    protected function casts(): array
    {
        return [
            'parking' => 'boolean',
            'bathrooms' => 'boolean',
            'showers' => 'boolean',
            'accessible' => 'boolean',
            'amphibious_chair' => 'boolean',
            'first_aid' => 'boolean',
            'lifeguard_post' => 'boolean',
            'bar' => 'boolean',
            'restaurant' => 'boolean',
            'surf_school' => 'boolean',
            'equipment_rental' => 'boolean',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
