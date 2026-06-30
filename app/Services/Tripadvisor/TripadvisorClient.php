<?php

namespace App\Services\Tripadvisor;

use App\Services\OverpassClient;
use Illuminate\Support\Facades\Http;

class TripadvisorClient
{
    public function getNearby(float $latitude, float $longitude): array
    {
        $apiKey = config('services.tripadvisor.key');

        if ($apiKey) {
            try {
                $response = Http::timeout(5)->get('https://api.content.tripadvisor.com/api/v1/location/nearby_search', [
                    'key' => $apiKey,
                    'latLong' => "{$latitude},{$longitude}",
                    'category' => 'restaurants',
                    'language' => 'pt',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $locations = $data['data'] ?? [];

                    $results = [];
                    foreach (array_slice($locations, 0, 5) as $loc) {
                        if (!isset($loc['name'])) {
                            continue;
                        }
                        $results[] = [
                            'external_id' => 'ta_rest_' . ($loc['location_id'] ?? uniqid()),
                            'name' => $loc['name'],
                            'cuisine_type' => null,
                            'rating' => null,
                            'reviews_count' => null,
                            'address' => $loc['address_obj']['address_string'] ?? null,
                            'average_price' => null,
                            'booking_url' => null,
                            'external_url' => $loc['web_url'] ?? 'https://www.tripadvisor.com',
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                        ];
                    }

                    if (!empty($results)) {
                        return $results;
                    }
                }
            } catch (\Exception $e) {
                logger()->error('TripAdvisor API Request failed: ' . $e->getMessage());
            }
        }

        return app(OverpassClient::class)->getNearbyRestaurants($latitude, $longitude);
    }
}
