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
                $cfg = config('services.tripadvisor');
                $response = Http::timeout($cfg['timeout'])->get($cfg['url'], [
                    'key' => $apiKey,
                    'latLong' => "{$latitude},{$longitude}",
                    'category' => 'restaurants',
                    'language' => $cfg['language'],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $locations = $data['data'] ?? [];

                    $results = [];
                    foreach (array_slice($locations, 0, $cfg['max_results']) as $loc) {
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
                            'external_url' => $loc['web_url'] ?? config('services.tripadvisor.fallback_url'),
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
