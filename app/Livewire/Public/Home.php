<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Beach;
use App\Services\GeoService;
use Illuminate\Support\Facades\DB;

class Home extends Component
{
    public $search = '';
    public $selectedRegion = '';
    public $selectedFlag = '';
    public $favoriteIds = [];

    public $latitude = null;
    public $longitude = null;
    public $nearbyGreen = [];

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

    public function findNearbyGreen()
    {
        $this->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $geoService = app(GeoService::class);

        $all = Beach::with(['currentStatus', 'translations', 'latestWeatherForecast', 'latestOceanForecast'])
            ->where('is_active', true)
            ->whereHas('currentStatus', fn($q) => $q->where('flag', 'green'))
            ->get()
            ->map(function ($beach) use ($geoService) {
                $distance = $geoService->haversine(
                    (float)$this->latitude,
                    (float)$this->longitude,
                    (float)$beach->latitude,
                    (float)$beach->longitude
                );
                $weather = $beach->latestWeatherForecast;
                $ocean = $beach->latestOceanForecast;
                $status = $beach->currentStatus;
                return [
                    'id' => $beach->id,
                    'name' => $beach->name,
                    'slug' => $beach->slug,
                    'latitude' => (float) $beach->latitude,
                    'longitude' => (float) $beach->longitude,
                    'distance_km' => round($distance, 1),
                    'url' => $beach->url,
                    'blue_flag' => (bool)$beach->blue_flag,
                    'accessible' => (bool)$beach->accessible,
                    'region' => $beach->region,
                    'municipality' => $beach->municipality,
                    'air_temp' => $weather ? (float)$weather->temp : null,
                    'water_temp' => $ocean ? (float)$ocean->water_temp : null,
                    'wave_height_min' => $ocean ? (float)$ocean->wave_height_min : null,
                    'wave_height_max' => $ocean ? (float)$ocean->wave_height_max : null,
                ];
            })
            ->sortBy('distance_km')
            ->take(5)
            ->values()
            ->toArray();

        $this->nearbyGreen = $all;
        $this->dispatch('nearby-green-updated', nearbyGreen: $all);
    }

    public function updatedSearch()
    {
        $this->dispatchBeachesUpdated();
    }

    public function updatedSelectedRegion()
    {
        $this->dispatchBeachesUpdated();
    }

    public function updatedSelectedFlag()
    {
        $this->dispatchBeachesUpdated();
    }

    private function dispatchBeachesUpdated()
    {
        $mapBeaches = $this->buildMapBeaches();
        $this->dispatch('beaches-updated', beaches: $mapBeaches);
    }

    public function render()
    {
        $this->favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('beach_id')->map(fn($id) => (int)$id)->toArray()
            : [];

        // Build list ONCE and derive map beaches from same result — avoids double DB round-trip
        $beaches = $this->buildBeachesList();
        $mapBeaches = collect($beaches)->map(fn ($b) => [
            'id' => $b['id'],
            'name' => $b['name'],
            'latitude' => $b['latitude'],
            'longitude' => $b['longitude'],
            'flag' => $b['flag'],
            'region' => $b['region'],
            'municipality' => $b['municipality'],
            'url' => $b['url'],
        ])->values()->toArray();

        return view('livewire.public.home', [
            'beachesList' => $beaches,
            'mapBeaches' => $mapBeaches,
            'flagFilters' => config('flags.labels'),
            'flagIcons' => config('flags.icons'),
        ])->layout('components.layouts.app');
    }

    private function buildBeachesList(): array
    {
        $query = Beach::with(['currentStatus', 'latestWeatherForecast', 'latestOceanForecast'])
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

        return $query->get()->sortByDesc(function ($beach) {
            return in_array((int)$beach->id, $this->favoriteIds);
        })->map(function ($beach) {
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
            ];
        })->toArray();
    }

    private function buildMapBeaches(): array
    {
        return collect($this->buildBeachesList())->map(fn ($b) => [
            'id' => $b['id'],
            'name' => $b['name'],
            'latitude' => $b['latitude'],
            'longitude' => $b['longitude'],
            'flag' => $b['flag'],
            'region' => $b['region'],
            'municipality' => $b['municipality'],
            'url' => $b['url'],
        ])->values()->toArray();
    }
}
