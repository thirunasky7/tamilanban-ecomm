<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Media;
use Illuminate\Validation\ValidationException;

class ProductVariantService
{
    public function sync(Product $product, array $attributeIds, array $variations): void
    {
        $product->attributes()->sync($attributeIds);

        $keptIds = [];

        foreach (array_values($variations) as $index => $row) {
            if (empty($row['price']) && empty($row['sku']) && empty($row['attribute_value_ids'])) {
                continue;
            }

            $valueIds = collect($row['attribute_value_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($valueIds === []) {
                continue;
            }

            $variant = isset($row['id'])
                ? ProductVariant::query()->where('product_id', $product->id)->find($row['id'])
                : null;

            if (! $variant) {
                $variant = new ProductVariant(['product_id' => $product->id]);
            }

            $variant->fill([
                'sku' => $row['sku'] ?? null,
                'price' => (float) ($row['price'] ?? 0),
                'compare_at_price' => filled($row['compare_at_price'] ?? null) ? (float) $row['compare_at_price'] : null,
                'stock' => (int) ($row['stock'] ?? 0),
                'thumbnail' => $row['thumbnail'] ?? null,
                'gallery' => ! empty($row['gallery'])
                    ? array_slice(array_values(array_filter($row['gallery'])), 0, 3)
                    : null,
                'is_active' => array_key_exists('is_active', $row)
                    ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN)
                    : true,
                'sort_order' => $index,
            ]);
            $variant->save();
            $variant->attributeValues()->sync($valueIds);
            $keptIds[] = $variant->id;
        }

        ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(function (ProductVariant $variant) {
                $variant->deleteMedia();
                $variant->delete();
            });

        if ($product->isVariable()) {
            $this->syncParentPricing($product);
        }
    }

    public function syncParentPricing(Product $product): void
    {
        $variants = $product->productVariants()->where('is_active', true)->get();

        if ($variants->isEmpty()) {
            return;
        }

        $minPrice = $variants->min('price');
        $minVariant = $variants->firstWhere('price', $minPrice);

        $product->update([
            'price' => $minPrice,
            'compare_at_price' => $minVariant?->compare_at_price,
            'stock' => $variants->sum('stock'),
        ]);
    }

    public function resolveForCart(int $productId, ?int $variantId): ProductVariant
    {
        $product = Product::query()->findOrFail($productId);

        if ($product->isVariable()) {
            if (! $variantId) {
                throw ValidationException::withMessages([
                    'variant_id' => 'Please select product options before adding to cart.',
                ]);
            }

            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('id', $variantId)
                ->where('is_active', true)
                ->with('attributeValues.attribute')
                ->first();

            if (! $variant) {
                throw ValidationException::withMessages([
                    'variant_id' => 'Selected variation is not available.',
                ]);
            }

            return $variant;
        }

        if ($variantId) {
            throw ValidationException::withMessages([
                'variant_id' => 'This product does not support variations.',
            ]);
        }

        if ($product->stock < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is out of stock.',
            ]);
        }

        return new ProductVariant([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'price' => $product->price,
            'compare_at_price' => $product->compare_at_price,
            'stock' => $product->stock,
            'thumbnail' => $product->thumbnail,
            'is_active' => true,
        ]);
    }

    public function assertStock(ProductVariant $variant, int $quantity): void
    {
        if (! $variant->isInStock($quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock for the selected variation.',
            ]);
        }
    }

    public function storefrontPayload(Product $product): array
    {
        if (! $product->isVariable()) {
            return [
                'type' => 'simple',
                'price' => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'stock' => (int) $product->stock,
                'thumbnail' => $product->thumbnail,
            ];
        }

        $product->loadMissing([
            'attributes.values',
            'productVariants.attributeValues.attribute',
        ]);

        $attributes = $product->attributes->map(fn ($attribute) => [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'values' => $attribute->values->map(fn ($value) => [
                'id' => $value->id,
                'value' => $value->value,
            ])->values()->all(),
        ])->values()->all();

        $variants = $product->productVariants
            ->where('is_active', true)
            ->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'compare_at_price' => $variant->compare_at_price ? (float) $variant->compare_at_price : null,
                'stock' => (int) $variant->stock,
                'thumbnail' => $variant->thumbnail ?: $product->thumbnail,
                'images' => $variant->imageUrls() ?: array_filter([$product->thumbnail]),
                'label' => $variant->label(),
                'value_ids' => $variant->attributeValues->pluck('id')->values()->all(),
            ])->values()->all();

        $prices = collect($variants)->pluck('price')->filter();
        $totalStock = (int) collect($variants)->sum('stock');

        return [
            'type' => 'variable',
            'price_min' => $prices->min(),
            'price_max' => $prices->max(),
            'total_stock' => $totalStock,
            'in_stock' => $totalStock > 0,
            'attributes' => $attributes,
            'variants' => $variants,
            'thumbnail' => $product->thumbnail,
        ];
    }

    public function decrementOrderStock(Order $order): void
    {
        $order->load('items');

        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                ProductVariant::query()
                    ->where('id', $item->product_variant_id)
                    ->decrement('stock', $item->quantity);

                $variant = ProductVariant::query()->find($item->product_variant_id);
                if ($variant?->product) {
                    $this->syncParentPricing($variant->product);
                }
                continue;
            }

            if ($item->product_id) {
                Product::query()
                    ->where('id', $item->product_id)
                    ->decrement('stock', $item->quantity);
            }
        }
    }
}
