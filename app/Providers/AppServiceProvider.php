<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Forecasting\PredictionEngine;
use App\Domain\Community\ConsensusResolver;
use App\Domain\Gamification\ScoreManager;
use App\Services\InfoAgua\InfoAguaClient;
use App\Services\Ipma\IpmaClient;
use App\Services\Tides\TideClient;
use App\Services\GeoService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeoService::class);
        $this->app->singleton(PredictionEngine::class);
        $this->app->singleton(ConsensusResolver::class, function ($app) {
            return new ConsensusResolver($app->make(ScoreManager::class));
        });
        $this->app->singleton(ScoreManager::class);
    }

    public function boot(): void
    {
        // No heavy operations in boot() — they are moved to a dedicated console command.
    }
}
