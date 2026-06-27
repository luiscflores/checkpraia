<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['admin_user_id', 'target_user_id', 'previous_points', 'new_points', 'difference', 'justification'])]
class AdminScoreAdjustment extends Model
{
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
