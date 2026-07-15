<?php

namespace Database\Factories;

use App\Models\Beach;
use App\Models\WeatherForecast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeatherForecast>
 */
class WeatherForecastFactory extends Factory
{
    protected $model = WeatherForecast::class;

    public function definition(): array
    {
        return [
            'beach_id' => Beach::factory(),
            'wind_speed' => fake()->randomFloat(2, 0, 50),
            'wind_direction' => fake()->randomElement(['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW']),
            'precipitation' => fake()->randomFloat(1, 0, 30),
            'visibility' => fake()->randomElement(['clear', 'hazy', 'fog', 'rain', 'storm']),
            'temp' => fake()->randomFloat(1, 10, 40),
            'uv_index' => fake()->randomFloat(1, 0, 11),
            'weather_code' => fake()->numberBetween(0, 99),
            'forecasted_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }

    public function sunny(): static
    {
        return $this->state(fn () => [
            'weather_code' => 0,
            'visibility' => 'clear',
            'precipitation' => 0,
            'temp' => fake()->randomFloat(1, 25, 38),
            'uv_index' => fake()->randomFloat(1, 6, 11),
        ]);
    }

    public function rainy(): static
    {
        return $this->state(fn () => [
            'weather_code' => fake()->randomElement([51, 61, 63, 65, 80, 81]),
            'visibility' => 'rain',
            'precipitation' => fake()->randomFloat(1, 2, 25),
        ]);
    }
}
