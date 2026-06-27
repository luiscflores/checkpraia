<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'flag_report_id', 'referral_id', 'type', 'points', 'status', 'description'])]
class ScoreTransaction extends Model
{
    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function report()
    {
        return $this->belongsTo(FlagReport::class, 'flag_report_id');
    }
}
