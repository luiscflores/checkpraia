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
    public $slug;
    public $beach;

    public function mount($slug)
    {
        $this->beach = Beach::with(['currentStatus', 'translations', 'services', 'features', 'restaurants'])
            ->where('slug', $slug)
            ->firstOrFail();
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

        // Limit: one vote per beach per 60 minutes
        $exists = FlagReport::where('user_id', $user->id)
            ->where('beach_id', $this->beach->id)
            ->where('reported_at', '>=', now()->subMinutes(60))
            ->exists();

        if ($exists) {
            $this->addError('report', 'Já enviaste uma confirmação para esta praia nos últimos 60 minutos.');
            return;
        }

        // Validate distance (must be <= 1.0 km)
        $distance = $this->calculateDistance((float)$lat, (float)$lon, (float)$this->beach->latitude, (float)$this->beach->longitude);
        if ($distance > 1.0) {
            $this->addError('report', 'Deves estar a menos de 1 km da praia para confirmar (distância atual: ' . round($distance, 2) . ' km).');
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

        return view('livewire.public.beach-detail', [
            'ocean' => $latestOcean,
            'weather' => $latestWeather,
            'quality' => $latestQuality,
            'alerts' => $activeAlerts,
            'prediction' => $latestPrediction,
        ])->layout('components.layouts.app');
    }
}
