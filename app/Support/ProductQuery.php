<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductQuery
{
    /** Columns needed for product cards — avoids loading large text/json fields. */
    public const CARD_COLUMNS = [
        'id', 'name', 'slug', 'product_type', 'price', 'compare_at_price',
        'thumbnail', 'is_featured', 'is_new', 'is_bestseller', 'is_active',
    ];

    public static function card(): Builder
    {
        return Product::query()
            ->select(self::CARD_COLUMNS)
            ->where('is_active', true)
            ->with(['categories:id,name,slug'])
            ->withAvg(['approvedReviews as avg_rating'], 'rating')
            ->withCount(['approvedReviews as reviews_count']);
    }
}
