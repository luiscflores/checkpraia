<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('RestaurantSeeder skipped — restaurants are synced from live APIs via restaurants:sync command.');
        $this->command->info('Run `php artisan restaurants:sync` to fetch real restaurant data from TripAdvisor/TheFork/Overpass.');
    }
}
