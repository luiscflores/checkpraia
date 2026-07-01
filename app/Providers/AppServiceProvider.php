<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
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
                $cacheKey = 'last_deploy_id';

                if (cache()->get($cacheKey) !== $deployIdentifier) {
                    // 1. Run migrations safely
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    
                    // 2. Run seeders only if the beaches table is empty
                    if (\Illuminate\Support\Facades\Schema::hasTable('beaches') && \App\Models\Beach::count() === 0) {
                        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                    }

                    // 3. Synchronously import weather, tides, and water quality to calculate flags immediately
                    if (\Illuminate\Support\Facades\Schema::hasTable('beaches')) {
                        @set_time_limit(300);
                        $beaches = \App\Models\Beach::where('is_active', true)->get();
                        foreach ($beaches as $beach) {
                            try {
                                \App\Jobs\FetchIpmaForecasts::dispatchSync($beach);
                                \App\Jobs\FetchInfoAguaData::dispatchSync($beach);
                            } catch (\Exception $ex) {
                                logger()->error('Auto-sync failed for beach ' . $beach->name . ': ' . $ex->getMessage());
                            }
                        }
                    }

                    // 4. Save deploy identifier to prevent re-running
                    cache()->forever($cacheKey, $deployIdentifier);
                }
            } catch (\Exception $e) {
                logger()->error('Auto-bootstrap database and import failed: ' . $e->getMessage());
            }
        }
    }
}
