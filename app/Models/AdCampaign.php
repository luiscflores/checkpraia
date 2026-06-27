<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'client_name', 'type', 'title', 'image_path', 'link', 'placement_type',
    'beach_id', 'region', 'district', 'municipality', 'starts_at', 'ends_at', 'is_active'
])]
class AdCampaign extends Model
{
    protected $table = 'advertising_campaigns';

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function beach()
    {
        return $this->belongsTo(Beach::class);
    }

    public function placements()
    {
        return $this->hasMany(AdPlacement::class, 'advertising_campaign_id');
    }
}
