<?php

namespace App\Console\Commands;

use App\Jobs\FetchInfoAguaData;
use App\Jobs\FetchIpmaForecasts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class StartupCommand extends Command
{
    protected $signature = 'checkpraia:startup';

    protected $description = 'Dispatch initial data fetch jobs on first boot';

    public function handle(): int
    {
        $startedFile = '/tmp/checkpraia_started.txt';
        if (file_exists($startedFile)) {
            $this->info('Already started, skipping.');

            return self::SUCCESS;
        }

        if (! Schema::hasTable('jobs') || ! Schema::hasTable('beaches')) {
            $this->warn('Required tables not found, skipping.');

            return self::SUCCESS;
        }

        @file_put_contents($startedFile, (string) time());
        FetchIpmaForecasts::dispatch();
        FetchInfoAguaData::dispatch();

        $this->info('Startup jobs dispatched!');

        return self::SUCCESS;
    }
}
