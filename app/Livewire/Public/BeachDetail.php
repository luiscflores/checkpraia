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
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class BeachDetail extends Component
{
    public $todayReports;

    private function voteCooldownMinutes(): int
    {
        return (int) config('gamification.report.cooldown_minutes', 60);
    }

    private function loadTodayReports(): void
    {
        $this->todayReports = FlagReport::where('beach_id', $this->beachId)
            ->where('reported_at', '>=', now()->startOfDay())
            ->where('status', '!=', 'cancelled')
            ->with('user')
            ->orderBy('reported_at', 'desc')
            ->get();
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

    public function mount($slug)
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

    public function toggleFavorite()
    {
        if (! auth()->check()) {
            session()->flash('favorite_error', __('common.favorite_login_required'));

            return;
        }

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

    public function submitReport($flag, $lat, $lon, $accuracy = null)
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

        $throttleKey = 'report:'.$user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
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

        $scoreManager = new ScoreManager;
        $weight = $scoreManager->getVoteWeight($user);

        // Save report (confirmed immediately — points awarded straight away)
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

        $resolver = new ConsensusResolver;

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

        $beach = Beach::find($this->beachId);
        $resolver->resolveCurrentStatus($beach);

        // Notify subscribers (favorites + nearby)
        try {
            app(PushNotificationService::class)->notifyBeachVote($beach, $report);
        } catch (\Exception $e) {
            logger()->error('Push notification failed', ['error' => $e->getMessage()]);
        }

        $this->loadTodayReports();

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

    public function render()
    {
        $this->loadTodayReports();

        $beach = Beach::with([
            'currentStatus',
            'translations',
            'services',
            'features',
            'restaurants',
            'latestOceanForecast',
            'latestWeatherForecast',
            'qualitySnapshots' => fn ($q) => $q->latest('sampled_at')->latest('id')->take(1),
        ])->findOrFail($this->beachId);

        $latestOcean = $beach->latestOceanForecast;
        $latestWeather = $beach->latestWeatherForecast;
        $latestQuality = $beach->qualitySnapshots->first();
        $latestPrediction = FlagPrediction::where('beach_id', $this->beachId)
            ->orderBy('calculated_at', 'desc')
            ->first();

        $todaySnapshots = BeachHourlySnapshot::where('beach_id', $this->beachId)
            ->where('captured_at', '>=', now()->startOfDay())
            ->orderBy('captured_at')
            ->get();

        $activeAlerts = OfficialAlert::where(function ($q) {
            $q->where('beach_id', $this->beachId)
                ->orWhereNull('beach_id');
        })
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->get();

        $tides = TideForecast::where('tide_station_id', $this->beachTideStationId)
            ->whereBetween('tide_time', [now()->startOfDay(), now()->endOfDay()->addHours(12)])
            ->orderBy('tide_time', 'asc')
            ->get();

        $nextTide = null;
        $tideDirection = null;
        if ($tides->isNotEmpty()) {
            $nextTide = $tides->firstWhere('tide_time', '>', now());
            if ($nextTide) {
                $prevTide = $tides->where('tide_time', '<=', now())->last();
                if ($prevTide) {
                    $tideDirection = $nextTide->tide_type === 'high' ? 'up' : 'down';
                } else {
                    $tideDirection = $nextTide->tide_type === 'high' ? 'up' : 'down';
                }
            } else {
                $tideDirection = null;
            }
        }

        $tideCurve = [];
        $tideCurvePoints = '';
        if ($tides->isNotEmpty()) {
            $maxH = $tides->pluck('tide_height')->push(4)->max();
            $minH = $tides->pluck('tide_height')->push(0)->min();
            $rangeH = max($maxH - $minH, 0.5);
            $dayStart = now()->startOfDay();
            $steps = 48;

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
                    $height = $prev->tide_height + ($next->tide_height - $prev->tide_height) * $ratio;
                }
                $pct = $rangeH > 0 ? ($height - $minH) / $rangeH : 0.5;
                $x = $i / $steps * 100;
                $y = 95 - $pct * 85;
                $tideCurve[] = ['time' => $t->format('H:i'), 'pct' => round($pct, 3), 'height' => round($height, 2)];
                $tideCurvePoints .= " {$x},{$y}";
            }
        }

        $tidesToday = $tides->filter(fn ($t) => $t->tide_time->isToday());
        $tidesTomorrow = $tides->filter(fn ($t) => $t->tide_time->isTomorrow());

        return view('livewire.public.beach-detail', [
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
            'moonPhase' => TideForecast::moonPhase(),
            'upcomingMoonPhases' => TideForecast::upcomingMoonPhases(),
            'todaySnapshots' => $todaySnapshots,
        ])->layout('components.layouts.app');
    }
}
