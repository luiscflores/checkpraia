<?php

namespace App\Providers;

use App\Domain\Community\ConsensusResolver;
use App\Domain\Forecasting\PredictionEngine;
use App\Domain\Gamification\ScoreManager;
use App\Services\GeoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

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
        // SQLite: all PRAGMAs are set via config/database.php on connection.
    }
}
