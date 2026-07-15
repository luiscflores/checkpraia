<?php

namespace App\Livewire\Public;

use App\Domain\Community\ConsensusResolver;
use App\Domain\Gamification\ScoreManager;
use App\Models\Beach;
use App\Models\BeachHourlySnapshot;
use App\Models\FlagPrediction;
use App\Models\FlagReport;
use App\Models\OfficialAlert;
use App\Models\ScoreTransaction;
use App\Models\TideForecast;
use App\Services\GeoService;
use App\Services\Ipma\IpmaClient;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class BeachDetail extends Component
{
    private function voteCooldownMinutes(): int
    {
        return (int) config('gamification.report.cooldown_minutes', 60);
    }

    private function maxDistanceKm(): float
    {
        return (float) config('gamification.report.max_distance_km', 1.0);
    }

    public $slug;

    public $beachId;

    public $beachLatitude;

    public $beachLongitude;

    public $beachTideStationId;

    public $isFavorited = false;

    public function mount(string $slug): void
    {
        $beach = Beach::where('slug', $slug)->firstOrFail(['id', 'latitude', 'longitude', 'tide_station_id', 'slug']);

        $this->beachId = $beach->id;
        $this->beachLatitude = (float) $beach->latitude;
        $this->beachLongitude = (float) $beach->longitude;
        $this->beachTideStationId = $beach->tide_station_id;

        $this->isFavorited = auth()->check() && auth()->user()->favorites()
            ->where('beach_id', $this->beachId)
            ->exists();
    }

    public function toggleFavorite(): void
    {
        if (! auth()->check()) {
            session()->flash('favorite_error', __('common.favorite_login_required'));

            return;
        }

        $throttleKey = 'favorite:'.auth()->id();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            session()->flash('favorite_error', __('common.favorite_login_required'));

            return;
        }
        RateLimiter::hit($throttleKey, 60);

        $user = auth()->user();

        if ($this->isFavorited) {
            $user->favorites()->detach($this->beachId);
            $this->isFavorited = false;
            session()->flash('favorite_success', __('common.favorite_removed'));
        } else {
            $user->favorites()->attach($this->beachId);
            $this->isFavorited = true;
            session()->flash('favorite_success', __('common.favorite_added'));
        }
    }

    public function submitReport(string $flag, float $lat, float $lon, ?float $accuracy = null): void
    {
        if (! auth()->check()) {
            $this->addError('report', __('common.favorite_login_required'));

            return;
        }

        $user = auth()->user();
        if ($user->is_suspended) {
            $this->addError('report', __('beach.report_error'));

            return;
        }

        $isAdmin = $user->is_admin;

        $beach = Beach::findOrFail($this->beachId);
        if (! $isAdmin && ! $beach->isInLifeguardHours()) {
            $this->addError('report', __('beach.report_outside_lifeguard_hours', [
                'start' => Carbon::parse($beach->lifeguard_start)->format('H:i'),
                'end' => Carbon::parse($beach->lifeguard_end)->format('H:i'),
            ]));

            return;
        }

        $throttleKey = 'report:'.$user->id.':'.$this->beachId;
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $this->addError('report', __('beach.report_rate_limit'));

            return;
        }
        RateLimiter::hit($throttleKey, 60);

        // If user already voted today, cancel their old vote so the new one replaces it
        $existingVote = FlagReport::where('user_id', $user->id)
            ->where('beach_id', $this->beachId)
            ->where('reported_at', '>=', now()->startOfDay())
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingVote) {
            if ($existingVote->flag === $flag) {
                $this->addError('report', __('beach.report_same_flag'));

                return;
            }
            $existingVote->status = 'cancelled';
            $existingVote->resolved_at = now();
            $existingVote->save();
        }

        $distance = $this->calculateDistance((float) $lat, (float) $lon, (float) $this->beachLatitude, (float) $this->beachLongitude);

        if (! $isAdmin && $distance > $this->maxDistanceKm()) {
            $this->addError('report', __('beach.report_too_far', ['distance' => round($distance, 1), 'max' => $this->maxDistanceKm()]));

            return;
        }

        $scoreManager = app(ScoreManager::class);
        $weight = $isAdmin ? 10 : $scoreManager->getVoteWeight($user);

        // Wrap report + consensus in a transaction to prevent partial writes
        $report = DB::transaction(function () use ($user, $flag, $weight, $distance, $accuracy, $lat, $lon) {
            $report = FlagReport::create([
                'user_id' => $user->id,
                'beach_id' => $this->beachId,
                'flag' => $flag,
                'vote_weight' => $weight,
                'status' => 'pending',
                'distance_to_beach' => $distance,
                'gps_accuracy' => $accuracy,
                'latitude' => $lat,
                'longitude' => $lon,
                'reported_at' => now(),
            ]);

            $resolver = app(ConsensusResolver::class);

            // Points only once per hour per beach
            $hasPointsThisHour = ScoreTransaction::where('user_id', $user->id)
                ->whereHas('report', fn ($q) => $q->where('beach_id', $this->beachId))
                ->where('created_at', '>=', now()->startOfHour())
                ->exists();

            if ($hasPointsThisHour) {
                $report->status = 'confirmed';
                $report->resolved_at = now();
                $report->save();
            } else {
                $resolver->resolveReport($report);
            }

            return $report;
        });

        $beach = Beach::find($this->beachId);
        app(ConsensusResolver::class)->resolveCurrentStatus($beach);

        // Invalidate today's reports cache so the new vote shows immediately
        Cache::forget("beach_today_reports:{$this->beachId}:".now()->format('Y-m-d'));

        // Notify subscribers (favorites + nearby)
        try {
            app(PushNotificationService::class)->notifyBeachVote($beach, $report);
        } catch (\Exception $e) {
            logger()->error('Push notification failed', ['error' => $e->getMessage()]);
        }

        $points = $report->scoreTransaction?->points;
        $msg = $points
            ? __('beach.report_success_points', ['points' => $points])
            : __('beach.report_success');
        session()->flash('report_success', $msg);

        $this->dispatch('report-submitted');
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        return app(GeoService::class)->haversine($lat1, $lon1, $lat2, $lon2);
    }

    // ─── Cached data helpers ─────────────────────────────────────────────────

    /**
     * Beach with static relations — changes very rarely, cache for 1 hour.
     * Cached separately from dynamic data (reports, status).
     */
    private function getCachedBeach(): Beach
    {
        return Cache::remember("beach_detail_static:{$this->beachId}", 3600, function () {
            return Beach::with([
                'currentStatus',
                'translations',
                'services',
                'features',
                'restaurants' => fn ($q) => $q->take(6),
                'latestOceanForecast',
                'latestWeatherForecast',
                'qualitySnapshots' => fn ($q) => $q->latest('sampled_at')->latest('id')->take(1),
            ])->findOrFail($this->beachId);
        });
    }

    /**
     * Today's flag reports — short cache (2 min), invalidated on submitReport().
     */
    private function getTodayReports(string $startOfDay)
    {
        $key = "beach_today_reports:{$this->beachId}:".now()->format('Y-m-d');

        return Cache::remember($key, 120, function () use ($startOfDay) {
            return FlagReport::where('beach_id', $this->beachId)
                ->where('reported_at', '>=', $startOfDay)
                ->where('status', '!=', 'cancelled')
                ->with(['user:id,name,username,avatar'])  // restrict columns — no N+1
                ->orderBy('reported_at', 'desc')
                ->get();
        });
    }

    /**
     * Tides for today+tomorrow — changes at most once per day.
     */
    private function getCachedTides()
    {
        $key = "beach_tides:{$this->beachTideStationId}:".now()->format('Y-m-d');

        return Cache::remember($key, 21600, function () { // 6 hours
            return TideForecast::where('tide_station_id', $this->beachTideStationId)
                ->whereBetween('tide_time', [now()->startOfDay()->subHours(30), now()->endOfDay()->addHours(12)])
                ->orderBy('tide_time', 'asc')
                ->get();
        });
    }

    /**
     * Latest flag prediction — cache 10 min.
     */
    private function getCachedPrediction(): ?FlagPrediction
    {
        return Cache::remember("beach_prediction:{$this->beachId}", 600, function () {
            return FlagPrediction::where('beach_id', $this->beachId)
                ->orderBy('calculated_at', 'desc')
                ->first();
        });
    }

    /**
     * Active official alerts — cache 5 min.
     */
    private function getCachedAlerts()
    {
        return Cache::remember("beach_alerts:{$this->beachId}", 300, function () {
            return OfficialAlert::where(function ($q) {
                $q->where('beach_id', $this->beachId)
                    ->orWhereNull('beach_id');
            })
                ->where('started_at', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
                })->get();
        });
    }

    /**
     * Today's hourly snapshots — cache 15 min.
     */
    private function getCachedTodaySnapshots(Beach $beach, string $startOfDay)
    {
        $key = "beach_snapshots:{$this->beachId}:".now()->format('Y-m-d-H');

        return Cache::remember($key, 900, function () use ($beach, $startOfDay) {
            return BeachHourlySnapshot::where('beach_id', $this->beachId)
                ->where('captured_at', '>=', $startOfDay)
                ->orderBy('captured_at')
                ->get()
                ->each(function ($snapshot) use ($beach) {
                    $snapshot->within_hours = $beach->isTimeInLifeguardHours($snapshot->captured_at);
                });
        });
    }

    /**
     * Moon phase — pure math, cache 1 hour.
     */
    private function getCachedMoonData(): array
    {
        return Cache::remember('moon_phases:'.now()->format('Y-m-d-H'), 3600, function () {
            return [
                'phase' => TideForecast::moonPhase(),
                'upcoming' => TideForecast::upcomingMoonPhases(),
            ];
        });
    }

    /**
     * Tide curve computation (48-point interpolation) — derived from tides, cache per day.
     */
    private function buildTideCurve($tides, string $beachTimezone): array
    {
        if ($tides->isEmpty()) {
            return ['curve' => [], 'points' => ''];
        }

        $key = "beach_tide_curve:{$this->beachTideStationId}:".now()->format('Y-m-d');

        return Cache::remember($key, 21600, function () use ($tides, $beachTimezone) {
            $maxH = $tides->pluck('tide_height')->push(4)->max();
            $minH = $tides->pluck('tide_height')->push(0)->min();
            $rangeH = max($maxH - $minH, 0.5);
            $dayStart = now($beachTimezone)->startOfDay();
            $steps = 96;

            $tideCurve = [];
            $tideCurvePoints = '';

            for ($i = 0; $i <= $steps; $i++) {
                $t = $dayStart->copy()->addMinutes($i * 1440 / $steps);
                $prev = null;
                $next = null;
                foreach ($tides as $tide) {
                    if ($tide->tide_time->lte($t)) {
                        $prev = $tide;
                    }
                    if ($tide->tide_time->gte($t) && ! $next) {
                        $next = $tide;
                    }
                }
                $height = $prev ? $prev->tide_height : ($next ? $next->tide_height : $tides->first()->tide_height);
                if ($prev && $next && $prev->tide_time->ne($next->tide_time)) {
                    $ratio = $prev->tide_time->diffInMinutes($t) / max(1, $prev->tide_time->diffInMinutes($next->tide_time));
                    $mu = (1 - cos($ratio * M_PI)) / 2;
                    $height = $prev->tide_height + ($next->tide_height - $prev->tide_height) * $mu;
                }
                $pct = $rangeH > 0 ? ($height - $minH) / $rangeH : 0.5;
                $x = $i / $steps * 100;
                $y = 95 - $pct * 85;
                $tideCurve[] = ['time' => $t->format('H:i'), 'pct' => round($pct, 3), 'height' => round($height, 2)];
                $tideCurvePoints .= " {$x},{$y}";
            }

            return ['curve' => $tideCurve, 'points' => $tideCurvePoints];
        });
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        $beach = $this->getCachedBeach();

        $beachTimezone = $beach->timezone;
        $startOfDay = now($beachTimezone)->startOfDay()->timezone(config('app.timezone'));
        $startOfDayStr = $startOfDay->toDateTimeString();

        $todayReports = $this->getTodayReports($startOfDayStr);
        $tides = $this->getCachedTides();
        $latestOcean = $beach->latestOceanForecast;
        $latestWeather = $beach->latestWeatherForecast;
        $latestQuality = $beach->qualitySnapshots->first();
        $latestPrediction = $this->getCachedPrediction();
        $activeAlerts = $this->getCachedAlerts();
        $todaySnapshots = $this->getCachedTodaySnapshots($beach, $startOfDayStr);

        // Tide direction (cheap: runs on already-cached collection)
        $nextTide = null;
        $tideDirection = null;
        if ($tides->isNotEmpty()) {
            $nextTide = $tides->firstWhere('tide_time', '>', now());
            if ($nextTide) {
                $tideDirection = $nextTide->tide_type === 'high' ? 'up' : 'down';
            }
        }

        // Tide curve — cached per day
        $tideData = $this->buildTideCurve($tides, $beach->timezone);
        $tideCurve = $tideData['curve'];
        $tideCurvePoints = $tideData['points'];

        $tidesToday = $tides->filter(fn ($t) => $t->tide_time->isToday());
        $tidesTomorrow = $tides->filter(fn ($t) => $t->tide_time->isTomorrow());

        // Daily weather forecast (cached 1h)
        $dailyForecast = Cache::remember("beach_daily_forecast:{$beach->id}", 3600, function () use ($beach) {
            return (new IpmaClient)->getDailyForecast($beach->latitude, $beach->longitude);
        });

        // Hourly weather forecast (cached 1h)
        $hourlyForecast = Cache::remember("beach_hourly_forecast:{$beach->id}", 3600, function () use ($beach) {
            return (new IpmaClient)->getHourlyForecast($beach->latitude, $beach->longitude);
        });

        // Moon data — cached 1h
        $moonData = $this->getCachedMoonData();

        return view('livewire.public.beach-detail', [
            'todayReports' => $todayReports,
            'beach' => $beach,
            'ocean' => $latestOcean,
            'weather' => $latestWeather,
            'quality' => $latestQuality,
            'alerts' => $activeAlerts,
            'prediction' => $latestPrediction,
            'tides' => $tides,
            'tidesToday' => $tidesToday,
            'tidesTomorrow' => $tidesTomorrow,
            'nextTide' => $nextTide,
            'tideDirection' => $tideDirection,
            'tideCurve' => $tideCurve,
            'tideCurvePoints' => $tideCurvePoints,
            'moonPhase' => $moonData['phase'],
            'upcomingMoonPhases' => $moonData['upcoming'],
            'todaySnapshots' => $todaySnapshots,
            'dailyForecast' => $dailyForecast,
            'hourlyForecast' => $hourlyForecast,
        ])->layout('components.layouts.app');
    }
}
