<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['referrer_user_id', 'invited_user_id', 'code', 'status', 'qualified_at'])]
class Referral extends Model
{
    protected function casts(): array
    {
        return [
            'qualified_at' => 'datetime',
        ];
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function invited()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }
}
