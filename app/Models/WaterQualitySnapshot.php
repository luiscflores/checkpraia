<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['beach_id', 'quality_class', 'source', 'sampled_at'])]
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
