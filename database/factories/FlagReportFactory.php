<?php

namespace Database\Factories;

use App\Models\Beach;
use App\Models\FlagReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlagReport>
 */
class FlagReportFactory extends Factory
{
    protected $model = FlagReport::class;

    public function definition(): array
    {
        $latitude = fake()->latitude(36.8, 42.2);
        $longitude = fake()->longitude(-9.5, -6.2);

        return [
            'user_id' => User::factory(),
            'beach_id' => Beach::factory(),
            'flag' => fake()->randomElement(['green', 'yellow', 'red', 'blue_or_neutral', 'gray']),
            'vote_weight' => fake()->numberBetween(1, 3),
            'status' => fake()->randomElement(['pending', 'confirmed', 'rejected']),
            'distance_to_beach' => fake()->randomFloat(3, 0, 500),
            'gps_accuracy' => fake()->randomFloat(2, 1, 50),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'reported_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'resolved_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'resolved_at' => fake()->dateTimeBetween('-15 days', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'resolved_at' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'resolved_at' => fake()->dateTimeBetween('-15 days', 'now'),
        ]);
    }
}
