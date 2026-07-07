<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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

        if (!\Illuminate\Support\Facades\Schema::hasTable('jobs') || !\Illuminate\Support\Facades\Schema::hasTable('beaches')) {
            $this->warn('Required tables not found, skipping.');
            return self::SUCCESS;
        }

        @file_put_contents($startedFile, (string) time());
        \App\Jobs\FetchIpmaForecasts::dispatch();
        \App\Jobs\FetchInfoAguaData::dispatch();

        $this->info('Startup jobs dispatched!');

        return self::SUCCESS;
    }
}
