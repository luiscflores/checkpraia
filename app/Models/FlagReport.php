<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'beach_id', 'flag', 'vote_weight', 'status',
    'distance_to_beach', 'gps_accuracy', 'latitude', 'longitude',
    'reported_at', 'resolved_at',
])]
class FlagReport extends Model
{
    protected function casts(): array
    {
        return [
            'vote_weight' => 'integer',
            'distance_to_beach' => 'decimal:3',
            'gps_accuracy' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }

    public function scoreTransaction()
    {
        return $this->hasOne(ScoreTransaction::class);
    }
}
