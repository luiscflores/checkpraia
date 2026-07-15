<?php

namespace Database\Factories;

use App\Models\Beach;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Beach>
 */
class BeachFactory extends Factory
{
    protected $model = Beach::class;

    public function definition(): array
    {
        $name = fake()->unique()->city().' Beach';

        return [
            'type' => fake()->randomElement(['urban', 'natural', 'semi-urban']),
            'external_id' => fake()->unique()->numericalBetween(1, 999999),
            'name' => $name,
            'slug' => Str::slug($name),
            'beachcam_slug' => Str::slug($name).'-cam',
            'region' => fake()->randomElement(['Continental', 'Açores', 'Madeira']),
            'district' => fake()->city(),
            'municipality' => fake()->city(),
            'island' => null,
            'latitude' => fake()->latitude(36.8, 42.2),
            'longitude' => fake()->longitude(-9.5, -6.2),
            'is_active' => true,
            'is_supervised' => fake()->boolean(70),
            'season_start' => fake()->dateTimeBetween('-1 year', 'now'),
            'season_end' => fake()->dateTimeBetween('now', '+1 year'),
            'lifeguard_start' => '09:00:00',
            'lifeguard_end' => '19:00:00',
            'image_path' => 'beaches/'.Str::slug($name).'.jpg',
            'blue_flag' => fake()->boolean(20),
            'accessible' => fake()->boolean(50),
            'tide_station_id' => fake()->optional()->numericalBetween(1, 100),
            'weather_zone' => fake()->optional()->word(),
            'ocean_zone' => fake()->optional()->word(),
        ];
    }

    public function supervised(): static
    {
        return $this->state(fn () => [
            'is_supervised' => true,
            'season_start' => now()->subMonth(),
            'season_end' => now()->addMonths(3),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function blueFlag(): static
    {
        return $this->state(fn () => [
            'blue_flag' => true,
        ]);
    }

    public function accessible(): static
    {
        return $this->state(fn () => [
            'accessible' => true,
        ]);
    }
}
