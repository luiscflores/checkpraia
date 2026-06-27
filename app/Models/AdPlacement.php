<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['advertising_campaign_id', 'placement_key'])]
class AdPlacement extends Model
{
    protected $table = 'advertising_placements';

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'advertising_campaign_id');
    }
}
