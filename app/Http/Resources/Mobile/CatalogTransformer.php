<?php

namespace App\Http\Resources\Mobile;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Short;
use App\Support\Media;
use App\Support\MobileUrl;
use Illuminate\Support\Collection;

class CatalogTransformer
{
    public static function product(Product $product, bool $detailed = false): array
    {
        $product->loadMissing(['categories', 'approvedReviews', 'productVariants', 'attributes.values']);

        $category = $product->categories->first();
        $thumbnail = MobileUrl::absolute($product->thumbnail);
        $gallery = MobileUrl::many($product->gallery ?? []);
        $images = array_values(array_unique(array_filter([
            $thumbnail,
            ...$gallery,
        ])));

        if ($images === [] && $thumbnail) {
            $images = [$thumbnail];
        }

        $colors = [];
        $sizes = [];
        foreach ($product->attributes as $attribute) {
            $name = strtolower((string) $attribute->name);
            $values = $attribute->values->pluck('value')->filter()->values()->all();
            if (str_contains($name, 'color') || str_contains($name, 'colour')) {
                $colors = $values;
            }
            if (str_contains($name, 'size')) {
                $sizes = $values;
            }
        }

        $tags = [];
        if ($product->is_featured) {
            $tags[] = 'featured';
        }
        if ($product->is_new) {
            $tags[] = 'new';
        }
        if ($product->is_bestseller) {
            $tags[] = 'bestseller';
        }

        $price = (float) $product->price;
        $compare = (float) ($product->compare_at_price ?: $product->price);
        if ($product->isVariable() && $product->productVariants->isNotEmpty()) {
            $price = (float) $product->productVariants->min('price');
            $compare = (float) ($product->productVariants->max('compare_at_price') ?: $product->productVariants->max('price'));
        }

        $payload = [
            'id' => (string) $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'description' => (string) ($product->description ?: $product->short_description ?: ''),
            'price' => $price,
            'originalPrice' => $compare > 0 ? $compare : $price,
            'rating' => $product->averageRating(),
            'reviewCount' => $product->reviewsCount(),
            'categoryId' => $category ? (string) $category->id : '',
            'brand' => 'ShopEase',
            'images' => $images,
            'tags' => $tags,
            'colors' => $colors,
            'sizes' => $sizes,
            'specifications' => array_filter([
                'SKU' => $product->sku,
                'Type' => $product->product_type,
            ]),
            'stock' => (int) ($product->isVariable()
                ? $product->productVariants->sum('stock')
                : $product->stock),
            'isNew' => (bool) $product->is_new,
            'createdAt' => optional($product->created_at)?->toIso8601String(),
        ];

        if ($detailed) {
            $payload['shortDescription'] = (string) ($product->short_description ?: '');
            $payload['variants'] = $product->productVariants->loadMissing('attributeValues.attribute')->map(fn ($variant) => [
                'id' => (string) $variant->id,
                'sku' => $variant->sku,
                'label' => $variant->label() ?: ($variant->sku ?? 'Default'),
                'price' => (float) $variant->price,
                'originalPrice' => (float) ($variant->compare_at_price ?: $variant->price),
                'stock' => (int) $variant->stock,
                'imageUrl' => MobileUrl::absolute($variant->thumbnail),
            ])->values()->all();
            $payload['reviews'] = $product->approvedReviews()
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($review) => [
                    'id' => (string) $review->id,
                    'name' => $review->reviewer_name,
                    'rating' => (int) $review->rating,
                    'title' => $review->title,
                    'comment' => $review->comment,
                    'createdAt' => optional($review->created_at)?->toIso8601String(),
                ])->values()->all();
        }

        return $payload;
    }

    public static function products(Collection $products): array
    {
        return $products->map(fn (Product $product) => self::product($product))->values()->all();
    }

    public static function category(Category $category): array
    {
        return [
            'id' => (string) $category->id,
            'name' => $category->name,
            'icon' => 'category',
            'imageUrl' => MobileUrl::absolute($category->image) ?? '',
        ];
    }

    public static function banner(Banner $banner): array
    {
        $link = (string) ($banner->link ?? '');
        $deepLinkType = 'tag';
        $deepLinkValue = 'featured';

        if (str_contains($link, '/products/')) {
            $deepLinkType = 'product';
            $deepLinkValue = trim((string) parse_url($link, PHP_URL_PATH), '/');
            $parts = explode('/', $deepLinkValue);
            $deepLinkValue = end($parts) ?: $deepLinkValue;
        } elseif (str_contains($link, 'category')) {
            $deepLinkType = 'category';
            $deepLinkValue = $link;
        }

        return [
            'id' => (string) $banner->id,
            'title' => (string) $banner->title,
            'subtitle' => (string) ($banner->subtitle ?? ''),
            'imageUrl' => MobileUrl::absolute(Media::url($banner->image)) ?? '',
            'deepLinkType' => $deepLinkType,
            'deepLinkValue' => $deepLinkValue,
            'backgroundColor' => null,
        ];
    }

    public static function short(Short $short): array
    {
        $short->loadMissing('product');
        $product = $short->product;

        return [
            'id' => (string) $short->id,
            'title' => (string) $short->title,
            'subtitle' => (string) ($short->description ?? ''),
            'videoUrl' => MobileUrl::absolute(Media::url($short->video_url)) ?? (string) $short->video_url,
            'thumbnailUrl' => MobileUrl::absolute(Media::url($short->thumbnail)) ?? '',
            'productId' => $product ? (string) $product->id : '',
            'productName' => $product?->name ?? '',
            'productPrice' => $product ? (float) $product->price : 0,
            'likes' => 0,
        ];
    }
}
