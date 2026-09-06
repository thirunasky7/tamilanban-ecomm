<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Short extends Model
{
    protected $fillable = [
        'title', 'description', 'video_url', 'thumbnail', 'product_id', 'sort_order', 'is_active', 'likes_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'likes_count' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ShortLike::class);
    }

    public function isLikedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }
}
