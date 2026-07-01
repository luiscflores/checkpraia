<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Auto-migrate, seed and fetch data on deployment detection
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
                    // 1. Save deploy identifier to lock file FIRST to prevent concurrent stampedes
                    @file_put_contents($lockFile, $deployIdentifier);

                    // 2. Run migrations safely
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    
                    // 3. Wipe beach data and run seeders on every deployment
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

                    // 4. Dispatch data sync jobs to the queue (very fast, <100ms)
                    if (\Illuminate\Support\Facades\Schema::hasTable('beaches')) {
                        \App\Jobs\FetchIpmaForecasts::dispatch();
                        \App\Jobs\FetchInfoAguaData::dispatch();
                    }
                }
            } catch (\Throwable $e) {
                logger()->error('Auto-bootstrap database and import failed: ' . $e->getMessage());
            }
        }
    }
}
