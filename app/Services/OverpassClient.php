<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OverpassClient
{
    public function getNearbyRestaurants(float $latitude, float $longitude, int $limit = 5): array
    {
        try {
            $cfg = config('services.overpass');
            $radius = $cfg['search_radius'];
            $query = <<<QUERY
[out:json][timeout:{$cfg['query_timeout']}];
(
  node(around:{$radius},{$latitude},{$longitude})[amenity=restaurant];
  node(around:{$radius},{$latitude},{$longitude})[amenity=cafe];
  node(around:{$radius},{$latitude},{$longitude})[amenity=fast_food];
);
out body {$limit};
QUERY;

            $response = Http::timeout($cfg['timeout'])->post($cfg['url'], [
                'data' => $query,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];
            $results = [];

            foreach (array_slice($elements, 0, $limit) as $el) {
                $tags = $el['tags'] ?? [];
                $name = $tags['name'] ?? null;
                if (! $name) {
                    continue;
                }

                $lat = $el['lat'] ?? $latitude;
                $lon = $el['lon'] ?? $longitude;

                $results[] = [
                    'external_id' => 'osm_'.($el['id'] ?? uniqid()),
                    'name' => $name,
                    'cuisine_type' => $tags['cuisine'] ?? null,
                    'rating' => null,
                    'reviews_count' => null,
                    'address' => ($tags['addr:street'] ?? '').($tags['addr:housenumber'] ? ' '.$tags['addr:housenumber'] : ''),
                    'average_price' => null,
                    'booking_url' => $tags['website'] ?? null,
                    'external_url' => $tags['website'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'distance' => app(GeoService::class)->haversine($latitude, $longitude, $lat, $lon),
                ];
            }

            return $results;
        } catch (\Exception $e) {
            logger()->error('Overpass API request failed: '.$e->getMessage());

            return [];
        }
    }
}
