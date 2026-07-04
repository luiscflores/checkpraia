<?php

namespace App\Services\InfoAgua;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class InfoAguaClient
{
    private function arcgisUrl(): string
    {
        return config('services.infoagua.arcgis_url', 'https://sniambgeoogc.apambiente.pt/getogc/rest/services/Visualizador/snirh_balneares_classificacoes_app/MapServer/0/query');
    }

    private ?array $allBeaches = null;

    public function getWaterQuality(string $externalId): ?string
    {
        return null;
    }

    public function getWaterQualityByCoords(float $latitude, float $longitude, ?string $beachName = null): ?array
    {
        try {
            $features = $this->fetchAllBeaches();

            $match = null;

            if (!empty($features)) {
                if ($beachName !== null) {
                    $match = $this->findByName($features, $beachName);
                }

                if ($match === null) {
                    $match = $this->findNearest($features, $latitude, $longitude, 10);
                }
            }

            if ($match !== null) {
                $props = $match['properties'];
                $classification = $props['ultima_classificacao'] ?? null;

                $class = $this->mapQuality($classification);

                if ($class === null) {
                    return null;
                }

                return [
                    'class' => $class,
                    'beach_name' => $props['nome_agua_balnear'],
                    'beach_code' => $props['codigo_agua_balnear'],
                    'description' => $props['ultima_classificacao_desc'],
                    'sampled_at' => isset($props['data_ultima_analise'])
                        ? Carbon::createFromTimestampMs($props['data_ultima_analise'])
                        : null,
                    'source' => 'apa_arcgis',
                ];
            }

            return null;
        } catch (\Exception $e) {
            logger()->error('APA ArcGIS water quality fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAllBeaches(): array
    {
        if ($this->allBeaches !== null) {
            return $this->allBeaches;
        }

        $response = Http::timeout(15)->get($this->arcgisUrl(), [
            'where' => '1=1',
            'outFields' => '*',
            'returnGeometry' => 'true',
            'f' => 'geojson',
            'resultRecordCount' => 1000,
        ]);

        if (!$response->successful()) {
            $this->allBeaches = [];
            return $this->allBeaches;
        }

        $data = $response->json();
        $this->allBeaches = $data['features'] ?? [];

        return $this->allBeaches;
    }

    private function findByName(array $features, string $beachName): ?array
    {
        $normalized = $this->normalizeName($beachName);

        $best = null;
        $bestScore = 0;

        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $arcgisName = $props['nome_agua_balnear'] ?? '';

            if (empty($arcgisName)) {
                continue;
            }

            $arcgisNormalized = $this->normalizeName($arcgisName);

            similar_text($normalized, $arcgisNormalized, $score);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $feature;
            }
        }

        return ($bestScore >= 60) ? $best : null;
    }

    private function findNearest(array $features, float $latitude, float $longitude, float $maxKm = 50): ?array
    {
        $nearest = null;
        $nearestDist = PHP_FLOAT_MAX;

        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $beachLat = isset($props['latitude_wgs84']) ? (float) $props['latitude_wgs84'] : null;
            $beachLon = isset($props['longitude_wgs84']) ? (float) $props['longitude_wgs84'] : null;

            if ($beachLat === null || $beachLon === null) {
                continue;
            }

            $dist = $this->haversine($latitude, $longitude, $beachLat, $beachLon);

            if ($dist < $nearestDist) {
                $nearestDist = $dist;
                $nearest = $feature;
            }
        }

        return ($nearest !== null && $nearestDist <= $maxKm) ? $nearest : null;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = str_replace(['praia de ', 'praia do ', 'praia da ', 'praia dos ', 'praia das '], '', $name);
        $name = preg_replace('/[^a-z0-9\s]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function mapQuality(?int $classification): ?string
    {
        if ($classification === null || $classification === 0) {
            return null;
        }

        return match ($classification) {
            1 => 'Good',
            2 => 'Poor',
            default => null,
        };
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
