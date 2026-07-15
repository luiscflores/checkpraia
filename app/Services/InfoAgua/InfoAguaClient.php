<?php

namespace App\Services\InfoAgua;

use App\Services\GeoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class InfoAguaClient
{
    private function arcgisUrl(): string
    {
        return config('services.infoagua.arcgis_url', 'https://sniambgeoogc.apambiente.pt/getogc/rest/services/Visualizador/snirh_balneares_classificacoes_app/MapServer/0/query');
    }

    private ?array $allBeaches = null;

    private ?array $normalizedNameIndex = null;

    public function getWaterQualityByCoords(float $latitude, float $longitude, ?string $beachName = null): ?array
    {
        try {
            $features = $this->fetchAllBeaches();

            $match = null;

            if (! empty($features)) {
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
            logger()->error('APA ArcGIS water quality fetch failed: '.$e->getMessage());

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

        if (! $response->successful()) {
            $this->allBeaches = [];

            return $this->allBeaches;
        }

        $data = $response->json();
        $this->allBeaches = $data['features'] ?? [];

        return $this->allBeaches;
    }

    /**
     * Build a normalized name → feature index for O(1) lookups.
     * On RPI3, this avoids O(n²) similar_text() across 570 × 500 features.
     */
    private function buildNameIndex(array $features): array
    {
        if ($this->normalizedNameIndex !== null) {
            return $this->normalizedNameIndex;
        }

        $index = [];
        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];
            $name = $props['nome_agua_balnear'] ?? '';
            if ($name !== '') {
                $normalized = $this->normalizeName($name);
                $index[$normalized] = $feature;
            }
        }

        $this->normalizedNameIndex = $index;

        return $index;
    }

    private function findByName(array $features, string $beachName): ?array
    {
        $normalized = $this->normalizeName($beachName);
        $index = $this->buildNameIndex($features);

        // O(1) exact match after normalization
        if (isset($index[$normalized])) {
            return $index[$normalized];
        }

        // Fallback: strip common prefixes and try again
        $stripped = preg_replace('/^(praia\s+(de\s+|do\s+|da\s+|dos\s+|das\s+)?)/u', '', $normalized);
        if ($stripped !== '' && isset($index[$stripped])) {
            return $index[$stripped];
        }

        // Last resort: substring match (still O(n) but rare path)
        foreach ($index as $arcgisNormalized => $feature) {
            if (str_contains($arcgisNormalized, $normalized) || str_contains($normalized, $arcgisNormalized)) {
                return $feature;
            }
        }

        return null;
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

            $dist = app(GeoService::class)->haversine($latitude, $longitude, $beachLat, $beachLon);

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
}
