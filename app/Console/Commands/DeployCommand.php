<?php

namespace App\Console\Commands;

use App\Jobs\FetchInfoAguaData;
use App\Jobs\FetchIpmaForecasts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DeployCommand extends Command
{
    protected $signature = 'checkpraia:deploy';

    protected $description = 'Run deploy tasks: migrate, seed, clear all caches, fetch initial data';

    public function handle(): int
    {
        $this->info('1/6 Resetting OPCache...');
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->info('  → OPCache cleared');
        }

        $this->info('2/6 Running migrations...');
        $this->call('migrate', ['--force' => true]);

        if (! Schema::hasTable('beaches')) {
            $this->warn('Beaches table not found, skipping seed and jobs.');

            return self::SUCCESS;
        }

        $this->info('3/6 Clearing Laravel cache...');
        $this->call('cache:clear');

        $this->info('4/6 Seeding database...');
        $this->call('db:seed', ['--force' => true]);

        $this->info('5/6 Optimizing Laravel (config, routes, events, views)...');
        $this->call('optimize');

        $this->info('6/6 Dispatching data fetch jobs...');
        FetchIpmaForecasts::dispatch();
        FetchInfoAguaData::dispatch();

        $this->info('Deploy complete!');

        return self::SUCCESS;
    }
}
