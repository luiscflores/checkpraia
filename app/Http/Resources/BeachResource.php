<?php

namespace App\Http\Resources;

use App\Models\Beach;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read float $latitude
 * @property-read float $longitude
 */
class BeachResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Beach $this */
        $flag = $this->getDisplayFlag($request->user());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'flag' => $flag,
            'flag_color' => config("flags.colors.{$flag}.hex", '#6b7280'),
            'region' => $this->region,
            'district' => $this->district,
            'municipality' => $this->municipality,
            'blue_flag' => (bool) $this->blue_flag,
            'accessible' => (bool) $this->accessible,
            'is_supervised' => (bool) $this->is_supervised,
            'image_url' => $this->image_path ? asset('storage/'.$this->image_path) : null,
            'distance_km' => null,
            'air_temp' => null,
            'water_temp' => null,
            'wind_speed' => null,
            'wind_direction' => null,
            'wave_height_min' => null,
            'wave_height_max' => null,
            'url' => $this->url,
        ];
    }

    public function withDistance(float $distance): static
    {
        $this->resource->distance_km = round($distance, 1);

        return $this;
    }

    public function withWeather(?float $airTemp, ?float $windSpeed, ?string $windDirection): static
    {
        $this->resource->air_temp = $airTemp;
        $this->resource->wind_speed = $windSpeed;
        $this->resource->wind_direction = $windDirection;

        return $this;
    }

    public function withOcean(?float $waterTemp, ?float $waveHeightMin, ?float $waveHeightMax): static
    {
        $this->resource->water_temp = $waterTemp;
        $this->resource->wave_height_min = $waveHeightMin;
        $this->resource->wave_height_max = $waveHeightMax;

        return $this;
    }
}
