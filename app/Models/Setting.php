<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting:{$key}", 300, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value)
    {
        Cache::forget("setting:{$key}");

        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
