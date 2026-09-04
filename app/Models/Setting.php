<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group', 'key', 'value', 'type',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever("setting.{$key}", function () use ($key) {
            return static::query()->where('key', $key)->first();
        });

        return $setting?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );

        Cache::forget("setting.{$key}");
    }
}
