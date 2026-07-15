<?php

use App\Jobs\CaptureHourlySnapshots;
use App\Jobs\FetchInfoAguaData;
use App\Jobs\FetchIpmaForecasts;
use App\Jobs\PurgePreciseLocations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new PurgePreciseLocations)->daily();
Schedule::job(new FetchIpmaForecasts)->cron('0 * * * *')->withoutOverlapping();
Schedule::job(new FetchInfoAguaData)->cron('5 * * * *')->withoutOverlapping();
Schedule::job(new CaptureHourlySnapshots)->hourlyAt(10)->withoutOverlapping();

Schedule::command('checkpraia:startup')->everyMinute()->withoutOverlapping();
