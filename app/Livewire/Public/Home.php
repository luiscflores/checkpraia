<?php

namespace App\Livewire\Public;

use App\Models\Beach;
use App\Services\GeoService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Home extends Component
{
    public $search = '';

    public $selectedRegion = '';

    public $selectedFlag = '';

    public $favoriteIds = [];

    public $latitude = null;

    public $longitude = null;

    public $nearbyGreen = [];

    private ?array $cachedBeachesList = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedRegion' => ['except' => ''],
        'selectedFlag' => ['except' => ''],
    ];

    public function toggleFavorite($beachId)
    {
        if (! auth()->check()) {
            session()->flash('favorite_error', __('common.favorite_login_required'));

            return;
        }

        $user = auth()->user();

        if (in_array($beachId, $this->favoriteIds)) {
            $user->favorites()->detach($beachId);
            $this->favoriteIds = array_values(array_filter($this->favoriteIds, fn ($id) => (int) $id !== (int) $beachId));
            session()->flash('favorite_success', __('common.favorite_removed'));
        } else {
            $user->favorites()->attach($beachId);
            $this->favoriteIds[] = (int) $beachId;
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

        $all = Beach::select(['id', 'name', 'slug', 'beachcam_slug', 'latitude', 'longitude', 'blue_flag', 'accessible', 'region', 'municipality', 'district', 'is_supervised', 'season_start', 'season_end', 'lifeguard_start', 'lifeguard_end'])
            ->with(['currentStatus', 'translations', 'latestWeatherForecast', 'latestOceanForecast'])
            ->where('is_active', true)
            ->whereHas('currentStatus', fn ($q) => $q->where('flag', 'green'))
            ->get()
            ->map(function ($beach) use ($geoService) {
                $distance = $geoService->haversine(
                    (float) $this->latitude,
                    (float) $this->longitude,
                    (float) $beach->latitude,
                    (float) $beach->longitude
                );
                $weather = $beach->latestWeatherForecast;
                $ocean = $beach->latestOceanForecast;

                return [
                    'id' => $beach->id,
                    'name' => $beach->name,
                    'slug' => $beach->slug,
                    'beachcam_slug' => $beach->beachcam_slug,
                    'latitude' => (float) $beach->latitude,
                    'longitude' => (float) $beach->longitude,
                    'distance_km' => round($distance, 1),
                    'url' => $beach->url,
                    'blue_flag' => (bool) $beach->blue_flag,
                    'accessible' => (bool) $beach->accessible,
                    'region' => $beach->region,
                    'municipality' => $beach->municipality,
                    'flag' => $beach->getDisplayFlag(),
                    'air_temp' => $weather ? (float) $weather->temp : null,
                    'water_temp' => $ocean ? (float) $ocean->water_temp : null,
                    'wave_height_min' => $ocean ? (float) $ocean->wave_height_min : null,
                    'wave_height_max' => $ocean ? (float) $ocean->wave_height_max : null,
                ];
            })
            ->filter(fn ($b) => $b['flag'] === 'green')
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

    private function dispatchBeachesUpdated(): void
    {
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
        $this->dispatch('beaches-updated', beaches: $mapBeaches);
    }

    public function render()
    {
        $this->favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('beach_id')->map(fn ($id) => (int) $id)->toArray()
            : [];

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
        if ($this->cachedBeachesList !== null) {
            return $this->cachedBeachesList;
        }

        $query = Beach::select(['id', 'name', 'slug', 'beachcam_slug', 'latitude', 'longitude', 'blue_flag', 'accessible', 'region', 'municipality', 'district', 'is_supervised', 'season_start', 'season_end', 'lifeguard_start', 'lifeguard_end'])
            ->with(['currentStatus', 'latestWeatherForecast', 'latestOceanForecast', 'translations'])
            ->where('is_active', true);

        if ($this->search) {
            $searchVal = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($searchVal) {
                $q->where('name', 'like', $searchVal)
                    ->orWhere('municipality', 'like', $searchVal)
                    ->orWhere('district', 'like', $searchVal)
                    ->orWhere('region', 'like', $searchVal);
            });
        }

        if ($this->selectedRegion) {
            $query->where('region', $this->selectedRegion);
        }

        $result = $query->get()->sortBy(function ($beach) {
            $isFav = in_array((int) $beach->id, $this->favoriteIds);
            if ($isFav) {
                return [0, $beach->name];
            }

            return [1, -$beach->latitude];
        })->map(function ($beach) {
            $status = $beach->currentStatus;
            $weather = $beach->latestWeatherForecast;
            $ocean = $beach->latestOceanForecast;

            return [
                'id' => $beach->id,
                'name' => $beach->name,
                'slug' => $beach->slug,
                'beachcam_slug' => $beach->beachcam_slug,
                'latitude' => (float) $beach->latitude,
                'longitude' => (float) $beach->longitude,
                'flag' => $beach->getDisplayFlag(),
                'source' => $status ? $status->source : 'prediction',
                'url' => $beach->url,
                'blue_flag' => (bool) $beach->blue_flag,
                'accessible' => (bool) $beach->accessible,
                'region' => $beach->region,
                'municipality' => $beach->municipality,
                'is_favorited' => in_array((int) $beach->id, $this->favoriteIds),
                'air_temp' => $weather ? (float) $weather->temp : null,
                'water_temp' => $ocean ? (float) $ocean->water_temp : null,
                'wave_height_min' => $ocean ? (float) $ocean->wave_height_min : null,
                'wave_height_max' => $ocean ? (float) $ocean->wave_height_max : null,
                'wave_direction' => $ocean ? $ocean->wave_direction : null,
                'wind_speed' => $weather && $weather->wind_speed !== null ? (float) $weather->wind_speed : null,
                'wind_direction' => $weather ? $weather->wind_direction : null,
            ];
        });

        if ($this->selectedFlag) {
            $result = $result->filter(fn ($b) => $b['flag'] === $this->selectedFlag);
        }

        $result = $result->values()->toArray();

        $this->cachedBeachesList = $result;

        return $result;
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

    public function updated($property): void
    {
        if (in_array($property, ['search', 'selectedRegion', 'selectedFlag'])) {
            $this->cachedBeachesList = null;
        }
    }
}
