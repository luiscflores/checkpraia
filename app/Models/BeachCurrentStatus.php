<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['beach_id', 'source', 'flag', 'confidence', 'consensus_reports_count', 'reason'])]
class BeachCurrentStatus extends Model
{
    protected $table = 'beach_current_statuses';

    protected function casts(): array
    {
        return [
            'confidence' => 'integer',
            'consensus_reports_count' => 'integer',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
