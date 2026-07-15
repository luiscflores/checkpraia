<?php

namespace App\Services\TheFork;

use Illuminate\Support\Facades\Http;

class TheForkClient
{
    public function getNearby(float $latitude, float $longitude): array
    {
        $apiKey = config('services.thefork.key');

        if (! $apiKey) {
            logger()->info('TheFork API key not configured — skipping TheFork restaurant search.');

            return [];
        }

        try {
            $cfg = config('services.thefork');
            $response = Http::timeout($cfg['timeout'])->get($cfg['url'], [
                'key' => $apiKey,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $restaurants = $data['data'] ?? [];

                $results = [];
                foreach (array_slice($restaurants, 0, $cfg['max_results']) as $rest) {
                    if (! isset($rest['name'])) {
                        continue;
                    }
                    $results[] = [
                        'external_id' => 'tf_rest_'.($rest['id'] ?? uniqid()),
                        'name' => $rest['name'],
                        'cuisine_type' => null,
                        'rating' => null,
                        'reviews_count' => null,
                        'address' => $rest['address'] ?? null,
                        'average_price' => null,
                        'booking_url' => $rest['booking_url'] ?? null,
                        'external_url' => $rest['web_url'] ?? null,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ];
                }

                return $results;
            }

            logger()->warning('TheFork API returned non-success status: '.$response->status());
        } catch (\Exception $e) {
            logger()->error('TheFork API Request failed: '.$e->getMessage());
        }

        return [];
    }
}
