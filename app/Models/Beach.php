<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'type', 'external_id', 'name', 'slug', 'region', 'district', 'municipality', 'island',
    'latitude', 'longitude', 'is_active', 'is_supervised', 'season_start', 'season_end',
    'lifeguard_start', 'lifeguard_end', 'image_path', 'blue_flag', 'accessible',
    'tide_station_id', 'weather_zone', 'ocean_zone'
])]
class Beach extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'is_supervised' => 'boolean',
            'blue_flag' => 'boolean',
            'accessible' => 'boolean',
            'season_start' => 'date',
            'season_end' => 'date',
        ];
    }

    public function getNameAttribute($value)
    {
        $translation = $this->translations->where('locale', app()->getLocale())->first();
        return $translation ? $translation->name : $value;
    }

    public function getDescriptionAttribute()
    {
        $translation = $this->translations->where('locale', app()->getLocale())->first();
        return $translation ? $translation->description : '';
    }

    public function getTimezoneAttribute()
    {
        return ((float) $this->longitude) < -20.0 ? 'Atlantic/Azores' : 'Europe/Lisbon';
    }

    public function getUrlAttribute()
    {
        $locale = app()->getLocale();
        $routeName = match ($locale) {
            'en' => 'beach.show.en',
            'es' => 'beach.show.es',
            'fr' => 'beach.show.fr',
            default => 'beach.show.pt',
        };
        return route($routeName, ['locale' => $locale, 'slug' => $this->slug]);
    }

    public static function boot(): void
    {
        parent::boot();

        static::saved(fn ($beach) => Cache::tags('beaches')->flush());
    }

    public function translations()
    {
        return $this->hasMany(BeachTranslation::class);
    }

    public function services()
    {
        return $this->hasOne(BeachService::class);
    }

    public function features()
    {
        return $this->hasOne(BeachFeature::class);
    }

    public function predictionProfile()
    {
        return $this->hasOne(BeachPredictionProfile::class);
    }

    public function oceanForecasts()
    {
        return $this->hasMany(OceanForecast::class);
    }

    public function weatherForecasts()
    {
        return $this->hasMany(WeatherForecast::class);
    }

    public function latestWeatherForecast()
    {
        return $this->hasOne(WeatherForecast::class)->latestOfMany('forecasted_at');
    }

    public function latestOceanForecast()
    {
        return $this->hasOne(OceanForecast::class)->latestOfMany('forecasted_at');
    }

    public function getCachedLatestWeatherForecast(): ?WeatherForecast
    {
        return Cache::tags('beaches')->remember(
            'weather:' . $this->id,
            300,
            fn () => $this->latestWeatherForecast
        );
    }

    public function getCachedLatestOceanForecast(): ?OceanForecast
    {
        return Cache::tags('beaches')->remember(
            'ocean:' . $this->id,
            300,
            fn () => $this->latestOceanForecast
        );
    }

    public function qualitySnapshots()
    {
        return $this->hasMany(WaterQualitySnapshot::class);
    }

    public function alerts()
    {
        return $this->hasMany(OfficialAlert::class)->where(function ($query) {
            $query->whereNull('ended_at')
                  ->orWhere('ended_at', '>', now());
        });
    }

    public function predictions()
    {
        return $this->hasMany(FlagPrediction::class);
    }

    public function reports()
    {
        return $this->hasMany(FlagReport::class);
    }

    public function hourlySnapshots()
    {
        return $this->hasMany(BeachHourlySnapshot::class)->orderBy('captured_at');
    }

    public function currentStatus()
    {
        return $this->hasOne(BeachCurrentStatus::class);
    }

    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'beach_restaurants')
            ->withPivot('distance')
            ->orderBy('pivot_distance');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}
