<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Beach;
use Illuminate\Support\Facades\DB;

class Home extends Component
{
    public $search = '';
    public $selectedRegion = '';
    public $selectedFlag = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedRegion' => ['except' => ''],
        'selectedFlag' => ['except' => ''],
    ];

    public function render()
    {
        $query = Beach::with(['currentStatus', 'translations'])
            ->where('is_active', true);

        if ($this->search) {
            $searchVal = '%' . strtolower(trim($this->search)) . '%';
            $query->where(function ($q) use ($searchVal) {
                $q->where(DB::raw('lower(name)'), 'like', $searchVal)
                  ->orWhere(DB::raw('lower(municipality)'), 'like', $searchVal)
                  ->orWhere(DB::raw('lower(district)'), 'like', $searchVal)
                  ->orWhere(DB::raw('lower(region)'), 'like', $searchVal);
            });
        }

        if ($this->selectedRegion) {
            $query->where('region', $this->selectedRegion);
        }

        if ($this->selectedFlag) {
            $query->whereHas('currentStatus', function ($q) {
                $q->where('flag', $this->selectedFlag);
            });
        }

        $beaches = $query->get()->map(function ($beach) {
            $status = $beach->currentStatus;
            return [
                'id' => $beach->id,
                'name' => $beach->name, // triggers translation accessor
                'slug' => $beach->slug,
                'latitude' => (float) $beach->latitude,
                'longitude' => (float) $beach->longitude,
                'flag' => $status ? $status->flag : 'gray',
                'source' => $status ? $status->source : 'prediction',
                'url' => $beach->url,
                'blue_flag' => (bool)$beach->blue_flag,
                'accessible' => (bool)$beach->accessible,
                'region' => $beach->region,
                'municipality' => $beach->municipality,
            ];
        });

        $this->dispatch('beaches-updated', beaches: $beaches);

        return view('livewire.public.home', [
            'beachesList' => $beaches,
        ])->layout('components.layouts.app');
    }
}
