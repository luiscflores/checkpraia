<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);

        if (\App\Models\Beach::count() === 0) {
            $this->call([
                BeachSeeder::class,
                RestaurantSeeder::class,
            ]);
        }
    }
}
