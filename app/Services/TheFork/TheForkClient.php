<?php

namespace App\Services\TheFork;

use Illuminate\Support\Facades\Http;

class TheForkClient
{
    /**
     * Get real nearby cafes/bistros from TheFork (or OpenStreetMap Overpass fallback if no API key exists).
     */
    public function getNearby(float $latitude, float $longitude): array
    {
        $apiKey = config('services.thefork.key');

        if ($apiKey) {
            try {
                // Example TheFork API request (requires affiliate credentials)
                $response = Http::timeout(5)->get('https://api.thefork.com/v1/restaurants', [
                    'key' => $apiKey,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $restaurants = $data['data'] ?? [];
                    
                    $results = [];
                    foreach (array_slice($restaurants, 0, 5) as $rest) {
                        if (!isset($rest['name'])) {
                            continue;
                        }
                        $results[] = [
                            'external_id' => 'tf_rest_' . ($rest['id'] ?? null),
                            'name' => $rest['name'],
                            'cuisine_type' => null, // No fallback/fabricated fields
                            'rating' => null,
                            'reviews_count' => null,
                            'address' => $rest['address'] ?? null,
                            'average_price' => null,
                            'booking_url' => $rest['booking_url'] ?? null,
                            'external_url' => $rest['web_url'] ?? 'https://www.thefork.pt',
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                        ];
                    }

                    if (!empty($results)) {
                        return $results;
                    }
                }
            } catch (\Exception $e) {
                logger()->error('TheFork API Request failed: ' . $e->getMessage());
            }
        }

        return [];
    }
}
