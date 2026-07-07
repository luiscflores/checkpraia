<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('checkpraia:startup', function () {
    $this->call(\App\Console\Commands\StartupCommand::class);
})->purpose('Dispatch initial data fetch jobs on first boot');

use Illuminate\Support\Facades\Schedule;

Schedule::job(new \App\Jobs\PurgePreciseLocations)->daily();
Schedule::job(new \App\Jobs\FetchIpmaForecasts)->cron('0 * * * *')->withoutOverlapping();
Schedule::job(new \App\Jobs\FetchInfoAguaData)->cron('5 * * * *')->withoutOverlapping();
Schedule::job(new \App\Jobs\CaptureHourlySnapshots)->hourlyAt(10)->withoutOverlapping();

Schedule::command('checkpraia:startup')->everyMinute()->withoutOverlapping();
