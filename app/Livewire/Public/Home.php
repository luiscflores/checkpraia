<?php

namespace App\Livewire\Public;

use App\Services\GeoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Home extends Component
{
    public $search = '';

    public $selectedRegion = '';

    public $selectedFlag = '';

    public $favoriteIds = [];

    public $latitude = null;

    public $longitude = null;

    public $nearby = [];

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

    public function findNearby()
    {
        $this->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $geoService = app(GeoService::class);
        $locale = app()->getLocale();
        $routeName = Route::has("beach.show.{$locale}") ? "beach.show.{$locale}" : 'beach.show.pt';
        $user = auth()->user();
        $isAdmin = $user && $user->is_admin;
        $now = now();

        $lat = (float) $this->latitude;
        $lon = (float) $this->longitude;

        $rows = DB::select('
            SELECT b.*,
                   bcs.flag as current_flag,
                   bt.name as translated_name,
                   wf.temp as air_temp,
                   of2.water_temp, of2.wave_height_min, of2.wave_height_max
            FROM beaches b
            LEFT JOIN beach_current_statuses bcs ON bcs.beach_id = b.id
            LEFT JOIN beach_translations bt ON bt.beach_id = b.id AND bt.locale = ?
            LEFT JOIN weather_forecasts wf ON wf.beach_id = b.id
            LEFT JOIN ocean_forecasts of2 ON of2.beach_id = b.id
            WHERE b.is_active = 1
        ', [$locale]);

        $result = [];
        foreach ($rows as $r) {
            $distance = $geoService->haversine($lat, $lon, (float) $r->latitude, (float) $r->longitude);

            $flag = 'gray';
            if ($isAdmin) {
                $flag = $r->current_flag ?? 'gray';
            } else {
                $inSeason = !$r->season_start || !$r->season_end || $now->between($r->season_start, $r->season_end);
                if ($inSeason) {
                    $tz = $r->longitude < -20 ? 'Atlantic/Azores' : 'Europe/Lisbon';
                    $t = $now->copy()->timezone($tz)->format('H:i:s');
                    $inHours = !$r->is_supervised || !$r->lifeguard_start || !$r->lifeguard_end || ($t >= $r->lifeguard_start && $t <= $r->lifeguard_end);
                    if ($inHours) $flag = $r->current_flag ?? 'gray';
                }
            }

            $result[] = [
                'id' => (int) $r->id,
                'name' => $r->translated_name ?? $r->name,
                'slug' => $r->slug,
                'beachcam_slug' => $r->beachcam_slug,
                'latitude' => (float) $r->latitude,
                'longitude' => (float) $r->longitude,
                'distance_km' => round($distance, 1),
                'url' => route($routeName, ['locale' => $locale, 'slug' => $r->slug]),
                'blue_flag' => (bool) $r->blue_flag,
                'accessible' => (bool) $r->accessible,
                'region' => $r->region,
                'municipality' => $r->municipality,
                'flag' => $flag,
                'air_temp' => $r->air_temp !== null ? (float) $r->air_temp : null,
                'water_temp' => $r->water_temp !== null ? (float) $r->water_temp : null,
                'wave_height_min' => $r->wave_height_min !== null ? (float) $r->wave_height_min : null,
                'wave_height_max' => $r->wave_height_max !== null ? (float) $r->wave_height_max : null,
            ];
        }

        usort($result, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        $this->nearby = array_slice($result, 0, 5);
        $this->dispatch('nearby-updated', nearby: $this->nearby);
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
        $locale = app()->getLocale();
        $routeName = Route::has("beach.show.{$locale}") ? "beach.show.{$locale}" : 'beach.show.pt';
        $mapBeaches = [];
        foreach ($beaches as $b) {
            $mapBeaches[] = [
                'id' => $b['id'],
                'name' => $b['name'],
                'latitude' => $b['latitude'],
                'longitude' => $b['longitude'],
                'flag' => $b['flag'],
                'region' => $b['region'],
                'municipality' => $b['municipality'],
                'url' => route($routeName, ['locale' => $locale, 'slug' => $b['slug']]),
            ];
        }
        $this->dispatch('beaches-updated', beaches: $mapBeaches);
    }

    public function saveDefaultRegion($region)
    {
        if (! auth()->check()) {
            return;
        }

        $allowed = ['Continental', 'Açores', 'Madeira'];
        if (! in_array($region, $allowed)) {
            return;
        }

        auth()->user()->update(['default_region' => $region]);
    }

    public function render()
    {
        $this->favoriteIds = auth()->check()
            ? auth()->user()->favorites()->pluck('beach_id')->map(fn ($id) => (int) $id)->toArray()
            : [];

        $beaches = $this->buildBeachesList();

        $locale = app()->getLocale();
        $routeName = Route::has("beach.show.{$locale}") ? "beach.show.{$locale}" : 'beach.show.pt';

        foreach ($beaches as &$b) {
            $b['url'] = route($routeName, ['locale' => $locale, 'slug' => $b['slug']]);
        }
        unset($b);

        $mapBeaches = [];
        foreach ($beaches as $b) {
            $mapBeaches[] = [
                'id' => $b['id'],
                'name' => $b['name'],
                'latitude' => $b['latitude'],
                'longitude' => $b['longitude'],
                'flag' => $b['flag'],
                'region' => $b['region'],
                'municipality' => $b['municipality'],
                'url' => $b['url'],
            ];
        }

        $defaultRegion = auth()->check() ? auth()->user()->default_region : null;

        return view('livewire.public.home', [
            'beachesList' => $beaches,
            'mapBeaches' => $mapBeaches,
            'flagFilters' => config('flags.labels'),
            'flagIcons' => config('flags.icons'),
            'defaultRegion' => $defaultRegion,
        ])->layout('components.layouts.app');
    }

    private function buildBeachesList(): array
    {
        if ($this->cachedBeachesList !== null) {
            return $this->cachedBeachesList;
        }

        $locale = app()->getLocale();
        $routeName = Route::has("beach.show.{$locale}") ? "beach.show.{$locale}" : 'beach.show.pt';
        $now = now();
        $user = auth()->user();
        $isAdmin = $user && $user->is_admin;

        $sql = 'SELECT b.*,
                   bcs.flag as current_flag, bcs.source as current_source,
                   bt.name as translated_name,
                   wf.temp as air_temp, wf.wind_speed, wf.wind_direction,
                   of2.water_temp, of2.wave_height_min, of2.wave_height_max, of2.wave_direction
            FROM beaches b
            LEFT JOIN beach_current_statuses bcs ON bcs.beach_id = b.id
            LEFT JOIN beach_translations bt ON bt.beach_id = b.id AND bt.locale = ?
            LEFT JOIN weather_forecasts wf ON wf.beach_id = b.id
            LEFT JOIN ocean_forecasts of2 ON of2.beach_id = b.id
            WHERE b.is_active = 1';

        $bindings = [$locale];

        if ($this->search) {
            $searchVal = '%' . trim($this->search) . '%';
            $sql .= ' AND (b.name LIKE ? OR b.municipality LIKE ? OR b.district LIKE ? OR b.region LIKE ?)';
            $bindings = array_merge($bindings, [$searchVal, $searchVal, $searchVal, $searchVal]);
        }

        if ($this->selectedRegion) {
            $sql .= ' AND b.region = ?';
            $bindings[] = $this->selectedRegion;
        }

        if ($this->selectedFlag) {
            $flagMap = ['green' => 'green', 'yellow' => 'yellow', 'red' => 'red', 'blue_or_neutral' => 'blue_or_neutral', 'gray' => 'gray'];
            if (isset($flagMap[$this->selectedFlag])) {
                if ($isAdmin) {
                    $sql .= ' AND bcs.flag = ?';
                    $bindings[] = $this->selectedFlag;
                } else {
                    $sql .= ' AND COALESCE(bcs.flag, \'gray\') = ?';
                    $bindings[] = $this->selectedFlag;
                }
            }
        }

        $sql .= ' ORDER BY b.latitude DESC';

        $rows = DB::select($sql, $bindings);

        $favoriteSet = array_flip($this->favoriteIds);
        $hasFavorites = count($favoriteSet) > 0;
        $tzCache = [];
        $result = [];

        foreach ($rows as $r) {
            $flag = 'gray';
            if ($isAdmin) {
                $flag = $r->current_flag ?? 'gray';
            } else {
                $sStart = $r->season_start ? new \Carbon\Carbon($r->season_start) : null;
                $sEnd = $r->season_end ? new \Carbon\Carbon($r->season_end) : null;
                $inSeason = !$sStart || !$sEnd || $now->between($sStart, $sEnd);
                if ($inSeason) {
                    $tz = $tzCache[$r->id] ??= ($r->longitude < -20 ? 'Atlantic/Azores' : 'Europe/Lisbon');
                    $t = $now->copy()->timezone($tz)->format('H:i:s');
                    $inHours = !$r->is_supervised || !$r->lifeguard_start || !$r->lifeguard_end || ($t >= $r->lifeguard_start && $t <= $r->lifeguard_end);
                    if ($inHours) {
                        $flag = $r->current_flag ?? 'gray';
                    }
                }
            }

            $result[] = [
                'id' => (int) $r->id,
                'name' => $r->translated_name ?? $r->name,
                'slug' => $r->slug,
                'beachcam_slug' => $r->beachcam_slug,
                'latitude' => (float) $r->latitude,
                'longitude' => (float) $r->longitude,
                'flag' => $flag,
                'source' => $r->current_source ?? 'prediction',
                'blue_flag' => (bool) $r->blue_flag,
                'accessible' => (bool) $r->accessible,
                'region' => $r->region,
                'municipality' => $r->municipality,
                'is_favorited' => isset($favoriteSet[$r->id]),
                'air_temp' => $r->air_temp !== null ? (float) $r->air_temp : null,
                'water_temp' => $r->water_temp !== null ? (float) $r->water_temp : null,
                'wave_height_min' => $r->wave_height_min !== null ? (float) $r->wave_height_min : null,
                'wave_height_max' => $r->wave_height_max !== null ? (float) $r->wave_height_max : null,
                'wave_direction' => $r->wave_direction,
                'wind_speed' => $r->wind_speed !== null ? (float) $r->wind_speed : null,
                'wind_direction' => $r->wind_direction,
            ];
        }

        if ($hasFavorites) {
            usort($result, function ($a, $b) use ($favoriteSet) {
                $aFav = isset($favoriteSet[$a['id']]) ? 0 : 1;
                $bFav = isset($favoriteSet[$b['id']]) ? 0 : 1;

                return $aFav <=> $bFav ?: $b['latitude'] <=> $a['latitude'];
            });
        }

        $this->cachedBeachesList = $result;

        return $result;
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'selectedRegion', 'selectedFlag'])) {
            $this->cachedBeachesList = null;
        }
    }
}
