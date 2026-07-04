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
    public $favoriteIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedRegion' => ['except' => ''],
        'selectedFlag' => ['except' => ''],
    ];

    public function toggleFavorite($beachId)
    {
        if (!auth()->check()) {
            session()->flash('favorite_error', __('common.favorite_login_required'));
            return;
        }

        $user = auth()->user();

        if (in_array($beachId, $this->favoriteIds)) {
            $user->favorites()->detach($beachId);
            $this->favoriteIds = array_values(array_filter($this->favoriteIds, fn($id) => (int)$id !== (int)$beachId));
            session()->flash('favorite_success', __('common.favorite_removed'));
        } else {
            $user->favorites()->attach($beachId);
            $this->favoriteIds[] = (int)$beachId;
            session()->flash('favorite_success', __('common.favorite_added'));
        }
    }

    public function render()
    {
        $this->favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('beach_id')->map(fn($id) => (int)$id)->toArray()
            : [];

        $query = Beach::with(['currentStatus', 'translations', 'latestWeatherForecast', 'latestOceanForecast'])
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
            $weather = $beach->latestWeatherForecast;
            $ocean = $beach->latestOceanForecast;
            return [
                'id' => $beach->id,
                'name' => $beach->name,
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
                'is_favorited' => in_array((int)$beach->id, $this->favoriteIds),
                'air_temp' => $weather ? (float)$weather->temp : null,
                'water_temp' => $ocean ? (float)$ocean->water_temp : null,
                'wave_height_min' => $ocean ? (float)$ocean->wave_height_min : null,
                'wave_height_max' => $ocean ? (float)$ocean->wave_height_max : null,
                'wave_direction' => $ocean ? $ocean->wave_direction : null,
                'wind_speed' => $weather && $weather->wind_speed !== null ? (float) $weather->wind_speed : null,
                'wind_direction' => $weather ? $weather->wind_direction : null,
                'jellyfish_risk' => $weather ? $weather->jellyfish_risk : null,
            ];
        });

        $mapBeaches = $beaches->map(fn ($b) => [
            'id' => $b['id'],
            'name' => $b['name'],
            'latitude' => $b['latitude'],
            'longitude' => $b['longitude'],
            'flag' => $b['flag'],
            'region' => $b['region'],
            'municipality' => $b['municipality'],
            'url' => $b['url'],
        ])->values();

        $this->dispatch('beaches-updated', beaches: $mapBeaches);

        return view('livewire.public.home', [
            'beachesList' => $beaches,
            'mapBeaches' => $mapBeaches,
            'flagFilters' => config('flags.labels'),
            'flagIcons' => config('flags.icons'),
        ])->layout('components.layouts.app');
    }
}
