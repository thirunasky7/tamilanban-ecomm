<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'price', 'compare_at_price',
        'stock', 'thumbnail', 'gallery', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'gallery' => 'array',
        'is_active' => 'boolean',
    ];

    public function getThumbnailAttribute(?string $value): ?string
    {
        return Media::url($value);
    }

    public function galleryPaths(): array
    {
        $gallery = $this->attributes['gallery'] ?? null;

        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true);
        }

        return is_array($gallery)
            ? array_values(array_filter($gallery, fn ($path) => filled($path)))
            : [];
    }

    public function imageUrls(): array
    {
        $urls = Media::urls($this->galleryPaths());
        $thumbnail = $this->thumbnail;

        if ($thumbnail && ! in_array($thumbnail, $urls, true)) {
            array_unshift($urls, $thumbnail);
        }

        return $urls ?: ($thumbnail ? [$thumbnail] : []);
    }

    public function deleteMedia(): void
    {
        Media::deleteMany(array_unique(array_filter(array_merge(
            [$this->getRawOriginal('thumbnail')],
            $this->galleryPaths(),
        ))));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_value_product_variant',
            'product_variant_id',
            'attribute_value_id',
        )->with('attribute');
    }

    public function label(): string
    {
        return $this->attributeValues
            ->sortBy(fn ($value) => $value->attribute?->name)
            ->map(fn ($value) => $value->attribute?->name.': '.$value->value)
            ->implode(' / ');
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->is_active && $this->stock >= $quantity;
    }
}
