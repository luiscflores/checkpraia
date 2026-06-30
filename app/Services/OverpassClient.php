<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OverpassClient
{
    public function getNearbyRestaurants(float $latitude, float $longitude, int $limit = 5): array
    {
        try {
            $query = <<<QUERY
[out:json][timeout:10];
(
  node(around:1500,{$latitude},{$longitude})[amenity=restaurant];
  node(around:1500,{$latitude},{$longitude})[amenity=cafe];
  node(around:1500,{$latitude},{$longitude})[amenity=fast_food];
);
out body {$limit};
QUERY;

            $response = Http::timeout(10)->post('https://overpass-api.de/api/interpreter', [
                'data' => $query,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];
            $results = [];

            foreach (array_slice($elements, 0, $limit) as $el) {
                $tags = $el['tags'] ?? [];
                $name = $tags['name'] ?? null;
                if (!$name) {
                    continue;
                }

                $lat = $el['lat'] ?? $latitude;
                $lon = $el['lon'] ?? $longitude;

                $results[] = [
                    'external_id' => 'osm_' . ($el['id'] ?? uniqid()),
                    'name' => $name,
                    'cuisine_type' => $tags['cuisine'] ?? null,
                    'rating' => null,
                    'reviews_count' => null,
                    'address' => ($tags['addr:street'] ?? '') . ($tags['addr:housenumber'] ? ' ' . $tags['addr:housenumber'] : ''),
                    'average_price' => null,
                    'booking_url' => $tags['website'] ?? null,
                    'external_url' => $tags['website'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'distance' => $this->haversine($latitude, $longitude, $lat, $lon),
                ];
            }

            return $results;
        } catch (\Exception $e) {
            logger()->error('Overpass API request failed: ' . $e->getMessage());
            return [];
        }
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return round($earthRadius * 2 * asin(sqrt($a)), 3);
    }
}
