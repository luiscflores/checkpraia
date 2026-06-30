<?php

namespace Database\Seeders;

use App\Models\Beach;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RestaurantSeeder extends Seeder
{
    private array $templates = [
        [
            'source' => 'tripadvisor',
            'name' => 'Mar À Vista',
            'cuisine_type' => 'Portuguesa, Marisco',
            'rating' => 4.5,
            'reviews_count' => 328,
            'average_price' => 35.00,
            'booking_url' => null,
        ],
        [
            'source' => 'tripadvisor',
            'name' => 'O Pescador',
            'cuisine_type' => 'Peixe, Tradicional',
            'rating' => 4.2,
            'reviews_count' => 215,
            'average_price' => 28.00,
            'booking_url' => null,
        ],
        [
            'source' => 'tripadvisor',
            'name' => 'Retiro do Mar',
            'cuisine_type' => 'Marisco, Mediterrânica',
            'rating' => 4.7,
            'reviews_count' => 512,
            'average_price' => 45.00,
            'booking_url' => null,
        ],
        [
            'source' => 'tripadvisor',
            'name' => 'Tasca da Praia',
            'cuisine_type' => 'Petiscos, Regional',
            'rating' => 4.0,
            'reviews_count' => 147,
            'average_price' => 18.00,
            'booking_url' => null,
        ],
        [
            'source' => 'thefork',
            'name' => 'Sabor a Sal',
            'cuisine_type' => 'Contemporânea, Fusão',
            'rating' => 4.8,
            'reviews_count' => 689,
            'average_price' => 52.00,
            'booking_url' => 'https://www.thefork.pt',
        ],
        [
            'source' => 'thefork',
            'name' => 'Baía dos Navegantes',
            'cuisine_type' => 'Italiana, Marisco',
            'rating' => 4.3,
            'reviews_count' => 276,
            'average_price' => 32.00,
            'booking_url' => 'https://www.thefork.pt',
        ],
        [
            'source' => 'thefork',
            'name' => 'Cais da Ribeira',
            'cuisine_type' => 'Francesa, Gourmet',
            'rating' => 4.6,
            'reviews_count' => 431,
            'average_price' => 48.00,
            'booking_url' => 'https://www.thefork.pt',
        ],
        [
            'source' => 'thefork',
            'name' => 'A Ilha',
            'cuisine_type' => 'Sushi, Japonesa',
            'rating' => 4.4,
            'reviews_count' => 198,
            'average_price' => 38.00,
            'booking_url' => 'https://www.thefork.pt',
        ],
    ];

    public function run(): void
    {
        $beaches = Beach::whereNotNull('latitude')->whereNotNull('longitude')->get();

        foreach ($beaches as $beach) {
            $count = rand(2, 4);
            $keys = array_rand($this->templates, $count);
            if (!is_array($keys)) {
                $keys = [$keys];
            }

            foreach ($keys as $key) {
                $tpl = $this->templates[$key];
                $offsetLat = (rand(-300, 300) / 100000);
                $offsetLon = (rand(-300, 300) / 100000);
                $distance = round(sqrt($offsetLat ** 2 + $offsetLon ** 2) * 111, 2);

                $restaurant = Restaurant::firstOrCreate(
                    ['external_id' => $tpl['source'] . '_demo_' . $key . '_' . $beach->id],
                    [
                        'source' => $tpl['source'],
                        'name' => $tpl['name'] . ' (' . $beach->municipality . ')',
                        'cuisine_type' => $tpl['cuisine_type'],
                        'rating' => $tpl['rating'] + round((rand(-5, 5) / 100), 2),
                        'reviews_count' => $tpl['reviews_count'] + rand(-20, 50),
                        'average_price' => $tpl['average_price'] + rand(-5, 10),
                        'booking_url' => $tpl['booking_url'] ? $tpl['booking_url'] . '/restaurant/' . Str::slug($tpl['name']) : null,
                        'external_url' => 'https://www.tripadvisor.com/Search?q=' . urlencode($tpl['name']),
                        'latitude' => $beach->latitude + $offsetLat,
                        'longitude' => $beach->longitude + $offsetLon,
                        'address' => 'Próximo da ' . $beach->name . ', ' . $beach->municipality,
                    ]
                );

                $beach->restaurants()->syncWithoutDetaching([
                    $restaurant->id => ['distance' => max(0.1, $distance)],
                ]);
            }
        }

        $this->command->info('Restaurants seeded successfully for ' . $beaches->count() . ' beaches.');
    }
}
