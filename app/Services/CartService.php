<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    public const SESSION_KEY = 'cart.items';

    public function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(
        int $productId,
        string $name,
        float $price,
        ?string $thumbnail = null,
        int $qty = 1,
        ?int $variantId = null,
        ?string $variantLabel = null,
        ?string $sku = null,
    ): void {
        $items = $this->items();
        $key = $variantId ? "{$productId}:v{$variantId}" : "{$productId}:default";

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $qty;
        } else {
            $items[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $name,
                'price' => $price,
                'thumbnail' => $thumbnail,
                'variant' => $variantLabel,
                'sku' => $sku,
                'quantity' => $qty,
            ];
        }

        Session::put(self::SESSION_KEY, $items);
    }

    public function update(string $key, int $qty): void
    {
        $items = $this->items();
        if (! isset($items[$key])) {
            return;
        }

        if ($qty <= 0) {
            unset($items[$key]);
        } else {
            $items[$key]['quantity'] = $qty;
        }

        Session::put(self::SESSION_KEY, $items);
    }

    public function remove(string $key): void
    {
        $items = $this->items();
        unset($items[$key]);
        Session::put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget('cart.coupon');
        Session::forget('cart.referral');
    }

    public function count(): int
    {
        return collect($this->items())->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) collect($this->items())->sum(fn ($item) => $item['price'] * $item['quantity']);
    }
}
