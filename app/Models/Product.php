<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('store.home.v2');
        static::saved($flush);
        static::deleted($flush);
    }

    protected $fillable = [
        'name', 'slug', 'product_type', 'sku', 'short_description', 'description',
        'price', 'compare_at_price', 'stock', 'thumbnail', 'gallery', 'variants',
        'is_featured', 'is_new', 'is_bestseller', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'gallery' => 'array',
        'variants' => 'array',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getThumbnailAttribute(?string $value): ?string
    {
        return Media::url($value);
    }

    public function isVariable(): bool
    {
        return $this->product_type === 'variable';
    }

    public function isSimple(): bool
    {
        return ! $this->isVariable();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->orderBy('categories.name');
    }

    public function getCategoryAttribute(): ?Category
    {
        $categories = $this->relationLoaded('categories')
            ? $this->getRelation('categories')
            : $this->categories()->get();

        return $categories->first();
    }

    public function categoryNamesLabel(): string
    {
        $categories = $this->relationLoaded('categories')
            ? $this->categories
            : $this->categories()->get();

        return $categories->pluck('name')->implode(', ');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_product')->with('values');
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function shorts(): HasMany
    {
        return $this->hasMany(Short::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function averageRating(): float
    {
        return round((float) $this->approvedReviews()->avg('rating'), 1) ?: 0;
    }

    public function reviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    public function displayPriceLabel(): string
    {
        if (! $this->isVariable()) {
            return '₹'.number_format((float) $this->price, 0);
        }

        $variants = $this->relationLoaded('productVariants')
            ? $this->productVariants->where('is_active', true)
            : $this->productVariants()->where('is_active', true)->get();

        if ($variants->isEmpty()) {
            return '₹'.number_format((float) $this->price, 0);
        }

        $min = $variants->min('price');
        $max = $variants->max('price');

        if ($min == $max) {
            return '₹'.number_format((float) $min, 0);
        }

        return '₹'.number_format((float) $min, 0).' – ₹'.number_format((float) $max, 0);
    }

    public function totalStock(): int
    {
        if ($this->isVariable()) {
            if ($this->relationLoaded('productVariants')) {
                return (int) $this->productVariants->where('is_active', true)->sum('stock');
            }

            return (int) $this->productVariants()->where('is_active', true)->sum('stock');
        }

        return (int) $this->stock;
    }
}
