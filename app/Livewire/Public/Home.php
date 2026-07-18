<?php

namespace App\Livewire\Public;

use App\Services\GeoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Renderless;
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

    public int $perPage = 20;

    public int $page = 1;

    public bool $hasMore = true;

    private ?array $cachedBeachesList = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedRegion' => ['except' => ''],
        'selectedFlag' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->favoriteIds = $this->loadFavoriteIds();
    }

    private function loadFavoriteIds(): array
    {
        return auth()->check()
            ? auth()->user()->favorites()->pluck('beach_id')->map(fn ($id) => (int) $id)->toArray()
            : [];
    }

    public function loadMore(): void
    {
        if (! $this->hasMore) {
            return;
        }

        $this->page++;
    }

    public function updatedSearch(): void
    {
        $this->resetPagination();
        $this->cachedBeachesList = null;
        $this->dispatchBeachesUpdated();
    }

    public function updatedSelectedRegion(): void
    {
        $this->resetPagination();
        $this->cachedBeachesList = null;
        $this->dispatchBeachesUpdated();
    }

    public function updatedSelectedFlag(): void
    {
        $this->resetPagination();
        $this->cachedBeachesList = null;
        $this->dispatchBeachesUpdated();
    }

    private function resetPagination(): void
    {
        $this->page = 1;
        $this->hasMore = true;
    }

    public function findNearby(float $lat, float $lon): void
    {
        $this->latitude = $lat;
        $this->longitude = $lon;

        $cacheKey = 'nearby:'.round($lat, 3).':'.round($lon, 3);

        $this->nearby = Cache::remember($cacheKey, 900, function () use ($lat, $lon) {
            $geoService = app(GeoService::class);
            $locale = app()->getLocale();
            $routeName = $this->routeNameFor($locale);
            $isAdmin = auth()->user()?->is_admin ?? false;
            $now = now();

            $latDelta = 0.25;
            $lngDelta = 0.25;

            $sql = $this->baseSelect('
                b.id, b.name, b.slug, b.beachcam_slug, b.latitude, b.longitude,
                b.blue_flag, b.accessible, b.region, b.municipality,
                b.is_supervised, b.season_start, b.season_end,
                b.lifeguard_start, b.lifeguard_end,
                bcs.flag as current_flag,
                bt.name as translated_name,
                wf.temp as air_temp,
                of2.water_temp, of2.wave_height_min, of2.wave_height_max
            ');
            $sql .= ' AND b.latitude BETWEEN ? AND ? AND b.longitude BETWEEN ? AND ?';

            $bindings = [$locale, $lat - $latDelta, $lat + $latDelta, $lon - $lngDelta, $lon + $lngDelta];

            $rows = DB::select($sql, $bindings);

            $lisbonTime = $now->copy()->timezone('Europe/Lisbon')->format('H:i:s');
            $azoresTime = $now->copy()->timezone('Atlantic/Azores')->format('H:i:s');
            $todayStr = $now->format('Y-m-d');

            $result = [];
            foreach ($rows as $r) {
                $distance = $geoService->haversine($lat, $lon, (float) $r->latitude, (float) $r->longitude);
                if ($distance <= 30) {
                    $result[] = $this->mapRow($r, $routeName, $locale, $isAdmin, $lisbonTime, $azoresTime, $todayStr, $distance);
                }
            }

            usort($result, fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);

            return array_slice($result, 0, 5);
        });

        $this->dispatch('nearby-updated', nearby: $this->nearby);
    }

    #[Renderless]
    public function saveDefaultRegion(string $region): void
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
        $allBeaches = $this->buildBeachesList();
        $locale = app()->getLocale();
        $routeName = $this->routeNameFor($locale);

        $total = count($allBeaches);
        $visibleCount = $this->page * $this->perPage;
        $this->hasMore = $visibleCount < $total;

        $beachesList = array_slice($allBeaches, 0, $visibleCount);

        $mapBeaches = [];
        foreach ($allBeaches as $b) {
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
            'beachesList' => $beachesList,
            'mapBeaches' => $mapBeaches,
            'flagFilters' => config('flags.labels'),
            'flagIcons' => config('flags.icons'),
            'defaultRegion' => $defaultRegion,
        ])->layout('components.layouts.app');
    }

    // ── Shared query helpers ──────────────────────────────────────────────

    private function routeNameFor(string $locale): string
    {
        return Route::has("beach.show.{$locale}") ? "beach.show.{$locale}" : 'beach.show.pt';
    }

    private function baseSelect(string $columns): string
    {
        return "SELECT {$columns}
            FROM beaches b
            LEFT JOIN beach_current_statuses bcs ON bcs.beach_id = b.id
            LEFT JOIN beach_translations bt ON bt.beach_id = b.id AND bt.locale = ?
            LEFT JOIN weather_forecasts wf ON wf.beach_id = b.id AND wf.id = (
                SELECT MAX(id) FROM weather_forecasts WHERE beach_id = b.id
            )
            LEFT JOIN ocean_forecasts of2 ON of2.beach_id = b.id AND of2.id = (
                SELECT MAX(id) FROM ocean_forecasts WHERE beach_id = b.id
            )
            WHERE b.is_active = 1";
    }

    private function resolveFlag(array $r, bool $isAdmin, string $lisbonTime, string $azoresTime, string $todayStr): string
    {
        if ($isAdmin) {
            return $r['current_flag'] ?? 'gray';
        }

        $inSeason = ! $r['season_start'] || ! $r['season_end']
            || ($todayStr >= $r['season_start'] && $todayStr <= $r['season_end']);

        if (! $inSeason) {
            return 'gray';
        }

        $t = $r['longitude'] < -20 ? $azoresTime : $lisbonTime;
        $inHours = ! $r['is_supervised'] || ! $r['lifeguard_start'] || ! $r['lifeguard_end']
            || ($t >= $r['lifeguard_start'] && $t <= $r['lifeguard_end']);

        return $inHours ? ($r['current_flag'] ?? 'gray') : 'gray';
    }

    private function mapRow(object|array $r, string $routeName, string $locale, bool $isAdmin, string $lisbonTime, string $azoresTime, string $todayStr, ?float $distance = null): array
    {
        $row = is_object($r) ? (array) $r : $r;

        $mapped = [
            'id' => (int) $row['id'],
            'name' => $row['translated_name'] ?? $row['name'],
            'slug' => $row['slug'],
            'beachcam_slug' => $row['beachcam_slug'],
            'latitude' => (float) $row['latitude'],
            'longitude' => (float) $row['longitude'],
            'url' => route($routeName, ['locale' => $locale, 'slug' => $row['slug']]),
            'blue_flag' => (bool) $row['blue_flag'],
            'accessible' => (bool) $row['accessible'],
            'region' => $row['region'],
            'municipality' => $row['municipality'],
            'flag' => $this->resolveFlag($row, $isAdmin, $lisbonTime, $azoresTime, $todayStr),
            'air_temp' => $row['air_temp'] !== null ? (float) $row['air_temp'] : null,
            'water_temp' => $row['water_temp'] !== null ? (float) $row['water_temp'] : null,
            'wave_height_min' => $row['wave_height_min'] !== null ? (float) $row['wave_height_min'] : null,
            'wave_height_max' => $row['wave_height_max'] !== null ? (float) $row['wave_height_max'] : null,
        ];

        if ($distance !== null) {
            $mapped['distance_km'] = round($distance, 1);
        }

        if (isset($row['wind_speed'])) {
            $mapped['wind_speed'] = $row['wind_speed'] !== null ? (float) $row['wind_speed'] : null;
            $mapped['wind_direction'] = $row['wind_direction'];
            $mapped['wave_direction'] = $row['wave_direction'];
            $mapped['source'] = $row['current_source'] ?? 'prediction';
            $mapped['is_favorited'] = isset(array_flip($this->favoriteIds)[$row['id']]);
        }

        return $mapped;
    }

    private function dispatchBeachesUpdated(): void
    {
        $beaches = $this->buildBeachesList();
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
        $this->dispatch('beaches-updated', beaches: $mapBeaches);
    }

    /**
     * Build the filtered beach rows — cached per filter combination.
     * Returns ALL matching beaches (up to 570) so the map and pagination both work.
     * Cache is invalidated when search/region/flag changes.
     */
    private function buildBeachesList(): array
    {
        if ($this->cachedBeachesList !== null) {
            return $this->cachedBeachesList;
        }

        $locale = app()->getLocale();
        $now = now();
        $isAdmin = auth()->user()?->is_admin ?? false;

        $sql = $this->baseSelect('
            b.id, b.name, b.slug, b.beachcam_slug, b.latitude, b.longitude,
            b.blue_flag, b.accessible, b.region, b.municipality,
            b.is_supervised, b.season_start, b.season_end,
            b.lifeguard_start, b.lifeguard_end,
            bcs.flag as current_flag, bcs.source as current_source,
            bt.name as translated_name,
            wf.temp as air_temp, wf.wind_speed, wf.wind_direction,
            of2.water_temp, of2.wave_height_min, of2.wave_height_max, of2.wave_direction
        ');

        $bindings = [$locale];

        if ($this->search) {
            $searchVal = '%'.trim($this->search).'%';
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
                } else {
                    $sql .= ' AND COALESCE(bcs.flag, \'gray\') = ?';
                }
                $bindings[] = $this->selectedFlag;
            }
        }

        $sql .= ' ORDER BY b.latitude DESC';

        $cacheKey = 'beach_rows:'.md5(implode(':', [
            $locale,
            $this->search ?: '',
            $this->selectedRegion ?: '',
            $this->selectedFlag ?: '',
            $isAdmin ? 'admin' : 'user',
        ]));

        $rows = Cache::remember($cacheKey, 300, function () use ($sql, $bindings) {
            return array_map(fn ($row) => (array) $row, DB::select($sql, $bindings));
        });

        $routeName = $this->routeNameFor($locale);
        $result = [];

        $lisbonTime = $now->copy()->timezone('Europe/Lisbon')->format('H:i:s');
        $azoresTime = $now->copy()->timezone('Atlantic/Azores')->format('H:i:s');
        $todayStr = $now->format('Y-m-d');

        foreach ($rows as $r) {
            $result[] = $this->mapRow($r, $routeName, $locale, $isAdmin, $lisbonTime, $azoresTime, $todayStr);
        }

        $favoriteSet = array_flip($this->favoriteIds);
        if (count($favoriteSet) > 0) {
            usort($result, function ($a, $b) use ($favoriteSet) {
                $aFav = isset($favoriteSet[$a['id']]) ? 0 : 1;
                $bFav = isset($favoriteSet[$b['id']]) ? 0 : 1;

                return $aFav <=> $bFav ?: $b['latitude'] <=> $a['latitude'];
            });
        }

        $this->cachedBeachesList = $result;

        return $result;
    }

    /**
     * Get beaches for the current page only — uses SQL LIMIT/OFFSET.
     * Caches the full filtered result set to avoid re-querying on each loadMore().
     */
}
