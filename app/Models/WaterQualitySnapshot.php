<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['beach_id', 'quality_class', 'sampled_at'])]
class WaterQualitySnapshot extends Model
{
    protected function casts(): array
    {
        return [
            'sampled_at' => 'date',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
