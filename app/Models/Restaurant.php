<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'external_id', 'source', 'name', 'image_url', 'cuisine_type',
    'rating', 'reviews_count', 'latitude', 'longitude', 'address',
    'average_price', 'booking_url', 'external_url'
])]
class Restaurant extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'average_price' => 'decimal:2',
        ];
    }

    public function beaches()
    {
        return $this->belongsToMany(Beach::class, 'beach_restaurants')
            ->withPivot('distance');
    }
}
