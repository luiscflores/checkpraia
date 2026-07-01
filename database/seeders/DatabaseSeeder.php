<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Luis Flores',
            'email' => 'luis@checkpraia.pt',
            'username' => 'luisflores',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            BeachSeeder::class,
            RestaurantSeeder::class,
        ]);
    }
}
