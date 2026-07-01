<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'luis@checkpraia.pt'],
            [
                'name' => 'Luis Flores',
                'username' => 'luisflores',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'referral_code' => strtoupper(Str::random(8)),
            ]
        );

        $this->call([
            BeachSeeder::class,
            RestaurantSeeder::class,
        ]);
    }
}
