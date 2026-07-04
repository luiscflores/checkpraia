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
        if (!app()->runningInConsole()) {
            try {
                $composerPath = base_path('composer.json');
                $artisanPath = base_path('artisan');

                $composerTime = file_exists($composerPath) ? filemtime($composerPath) : time();
                $artisanTime = file_exists($artisanPath) ? filemtime($artisanPath) : time();

                $deployIdentifier = md5($composerTime . '-' . $artisanTime);
                $lockFile = storage_path('app/deployed_version.txt');

                $lastDeploy = file_exists($lockFile) ? file_get_contents($lockFile) : '';

                if ($lastDeploy !== $deployIdentifier) {
                    @file_put_contents($lockFile, $deployIdentifier);

                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

                    if (\Illuminate\Support\Facades\Schema::hasTable('beaches')) {
                        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

                        \App\Models\FlagReport::truncate();
                        \App\Models\FlagPrediction::truncate();
                        \App\Models\OceanForecast::truncate();
                        \App\Models\WeatherForecast::truncate();
                        \App\Models\TideForecast::truncate();
                        \App\Models\WaterQualitySnapshot::truncate();
                        \App\Models\OfficialAlert::truncate();
                        \App\Models\BeachTranslation::truncate();
                        \App\Models\BeachService::truncate();
                        \App\Models\BeachFeature::truncate();
                        \App\Models\BeachPredictionProfile::truncate();
                        \App\Models\Restaurant::truncate();
                        \Illuminate\Support\Facades\DB::table('beach_restaurants')->truncate();
                        \App\Models\Beach::truncate();

                        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

                        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                    }

                    if (\Illuminate\Support\Facades\Schema::hasTable('beaches')) {
                        \App\Jobs\FetchIpmaForecasts::dispatch();
                        \App\Jobs\FetchInfoAguaData::dispatch();
                    }
                }
            } catch (\Throwable $e) {
                logger()->error('Auto-bootstrap database and import failed: ' . $e->getMessage());
            }
        }

        if (config('app.env') === 'production') {
            $startedFile = '/tmp/checkpraia_started.txt';
            if (!file_exists($startedFile)) {
                @file_put_contents($startedFile, time());
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('jobs') && \Illuminate\Support\Facades\Schema::hasTable('beaches')) {
                        \App\Jobs\FetchIpmaForecasts::dispatch();
                        \App\Jobs\FetchInfoAguaData::dispatch();
                    }
                } catch (\Throwable $e) {
                    logger()->error('Failed to dispatch startup jobs: ' . $e->getMessage());
                }
            }
        }
    }
}
