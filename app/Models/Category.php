<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected static function booted(): void
    {
        $flush = function () {
            Cache::forget('store.home.v2');
            Cache::forget('store.categories');
        };
        static::saved($flush);
        static::deleted($flush);
    }
    protected $fillable = [
        'name', 'slug', 'image', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageAttribute(?string $value): ?string
    {
        return Media::url($value);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
