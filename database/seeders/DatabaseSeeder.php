<?php

namespace Database\Seeders;

use App\Models\Beach;
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

        if (Beach::count() === 0) {
            $this->call([
                BeachSeeder::class,
                RestaurantSeeder::class,
            ]);
        }
    }
}
