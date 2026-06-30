<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\OceanForecast;
use App\Models\WeatherForecast;
use App\Models\WaterQualitySnapshot;
use App\Models\OfficialAlert;
use App\Domain\Community\ConsensusResolver;
use App\Domain\Gamification\ScoreManager;

class BeachDetail extends Component
{
    private const VOTE_COOLDOWN_MINUTES = 60;
    private const MAX_DISTANCE_KM = 1.0;

    public $slug;
    public $beach;
    public $isFavorited = false;

    public function mount($slug)
    {
        $this->beach = Beach::with(['currentStatus', 'translations', 'services', 'features', 'restaurants'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->isFavorited = auth()->check() && auth()->user()->favorites()
            ->where('beach_id', $this->beach->id)
            ->exists();
    }

    public function toggleFavorite()
    {
        if (!auth()->check()) {
            session()->flash('favorite_error', 'Precisas de iniciar sessão para guardar favoritos.');
            return;
        }

        $user = auth()->user();

        if ($this->isFavorited) {
            $user->favorites()->detach($this->beach->id);
            $this->isFavorited = false;
            session()->flash('favorite_success', 'Praia removida dos favoritos.');
        } else {
            $user->favorites()->attach($this->beach->id);
            $this->isFavorited = true;
            session()->flash('favorite_success', 'Praia adicionada aos favoritos! ⭐');
        }
    }

    public function submitReport($flag, $lat, $lon, $accuracy = null)
    {
        if (!auth()->check()) {
            $this->addError('report', 'Precisas de iniciar sessão para confirmar a bandeira.');
            return;
        }

        $user = auth()->user();
        if ($user->is_suspended) {
            $this->addError('report', 'A tua conta está suspensa e não pode enviar relatórios.');
            return;
        }

        $exists = FlagReport::where('user_id', $user->id)
            ->where('beach_id', $this->beach->id)
            ->where('reported_at', '>=', now()->subMinutes(self::VOTE_COOLDOWN_MINUTES))
            ->exists();

        if ($exists) {
            $this->addError('report', 'Já enviaste uma confirmação para esta praia nos últimos ' . self::VOTE_COOLDOWN_MINUTES . ' minutos.');
            return;
        }

        $distance = $this->calculateDistance((float)$lat, (float)$lon, (float)$this->beach->latitude, (float)$this->beach->longitude);
        if ($distance > self::MAX_DISTANCE_KM) {
            $this->addError('report', 'Deves estar a menos de ' . self::MAX_DISTANCE_KM . ' km da praia para confirmar (distância atual: ' . round($distance, 2) . ' km).');
            return;
        }

        $scoreManager = new ScoreManager();
        $weight = $scoreManager->getVoteWeight($user);

        // Save report (pending status)
        FlagReport::create([
            'user_id' => $user->id,
            'beach_id' => $this->beach->id,
            'flag' => $flag,
            'vote_weight' => $weight,
            'status' => 'pending',
            'distance_to_beach' => $distance,
            'gps_accuracy' => $accuracy,
            'latitude' => $lat,
            'longitude' => $lon,
            'reported_at' => now(),
        ]);

        // Resolve current beach status immediately
        $resolver = new ConsensusResolver();
        $resolver->resolveCurrentStatus($this->beach);

        // Reload beach relation to refresh view
        $this->beach->load('currentStatus');

        session()->flash('report_success', 'Bandeira confirmada! Os teus pontos estão pendentes até ao fecho da janela.');
    }

    /**
     * Compute geographical distance using Haversine formula.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c; // returns distance in km
    }

    public function render()
    {
        // Fetch forecasts
        $latestOcean = OceanForecast::where('beach_id', $this->beach->id)->orderBy('forecasted_at', 'desc')->first();
        $latestWeather = WeatherForecast::where('beach_id', $this->beach->id)->orderBy('forecasted_at', 'desc')->first();
        $latestQuality = WaterQualitySnapshot::where('beach_id', $this->beach->id)->orderBy('sampled_at', 'desc')->orderBy('id', 'desc')->first();
        $latestPrediction = \App\Models\FlagPrediction::where('beach_id', $this->beach->id)->orderBy('calculated_at', 'desc')->first();
        
        $activeAlerts = OfficialAlert::where('beach_id', $this->beach->id)
            ->orWhereNull('beach_id')
            ->where('started_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            })->get();

        $tides = \App\Models\TideForecast::where('tide_station_id', $this->beach->tide_station_id)
            ->where('tide_time', '>=', now()->startOfDay())
            ->where('tide_time', '<=', now()->endOfDay()->addHours(12))
            ->orderBy('tide_time', 'asc')
            ->get();

        return view('livewire.public.beach-detail', [
            'ocean' => $latestOcean,
            'weather' => $latestWeather,
            'quality' => $latestQuality,
            'alerts' => $activeAlerts,
            'prediction' => $latestPrediction,
            'tides' => $tides,
        ])->layout('components.layouts.app');
    }
}
