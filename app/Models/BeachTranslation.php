<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['beach_id', 'locale', 'name', 'description'])]
class BeachTranslation extends Model
{
    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }
}
