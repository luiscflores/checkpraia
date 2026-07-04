<?php

namespace App\Services\InfoAgua;

use Illuminate\Support\Facades\Http;

class InfoAguaClient
{
    private function wfsUrl(): string
    {
        return config('services.infoagua.wfs_url', 'https://sniambgeoogc.apambiente.pt/getogc/services/SNIAmb/Praias/MapServer/WFSServer');
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
                $qualityDesc = $props['qualidade_agua_balnear_dsc'] ?? null;
                $classification = $props['classificacao_agua_balnear'] ?? null;

                if ($qualityDesc || $classification !== null) {
                    $class = $this->mapQuality($qualityDesc, $classification);

                    return [
                        'class' => $class,
                        'beach_name' => $props['nome_praia'] ?? 'Desconhecida',
                        'beach_code' => $props['codigo_praia'] ?? null,
                        'description' => $qualityDesc,
                        'source' => 'apa_wfs',
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            logger()->error('APA WFS water quality fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchAllBeaches(): array
    {
        if ($this->allBeaches !== null) {
            return $this->allBeaches;
        }

        $response = Http::timeout(10)->get($this->wfsUrl(), [
            'service' => 'WFS',
            'version' => '2.0.0',
            'request' => 'GetFeature',
            'typeName' => 'SNIAmb_Praias:Praia',
            'count' => 100,
            'outputFormat' => 'GEOJSON',
        ]);

        if (!$response->successful()) {
            return [];
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
            $wfsName = $props['nome_praia'] ?? '';

            if (empty($wfsName)) {
                continue;
            }

            $wfsNormalized = $this->normalizeName($wfsName);

            similar_text($normalized, $wfsNormalized, $score);

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
            $geom = $feature['geometry'] ?? null;
            if (!$geom || !isset($geom['coordinates'])) {
                continue;
            }

            $coords = $geom['coordinates'];
            $beachLon = (float) $coords[0];
            $beachLat = (float) $coords[1];

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

    private function mapQuality(?string $description, ?int $classification): string
    {
        if ($description !== null) {
            $desc = mb_strtolower(trim($description));

            if (str_contains($desc, 'imprópria') || str_contains($desc, 'má') || str_contains($desc, 'desaconselhada')) {
                return 'Poor';
            }

            if (str_contains($desc, 'adequada') || str_contains($desc, 'própria') || str_contains($desc, 'boa')) {
                return 'Good';
            }

            if (str_contains($desc, 'excelente')) {
                return 'Excellent';
            }

            if (str_contains($desc, 'suficiente')) {
                return 'Sufficient';
            }
        }

        if ($classification !== null) {
            return match ($classification) {
                1 => 'Good',
                2 => 'Sufficient',
                3 => 'Poor',
                default => 'Good',
            };
        }

        return 'Good';
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
