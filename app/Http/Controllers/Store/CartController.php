<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ProductVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private ProductVariantService $variants,
    ) {
    }

    public function index(): View
    {
        return view('store.cart', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;
        $variant = $this->variants->resolveForCart($product->id, $data['variant_id'] ?? null);
        $this->variants->assertStock($variant, $quantity);

        $thumbnail = $variant->thumbnail ?: $product->thumbnail;
        $variantLabel = $variant->exists ? $variant->label() : null;

        $this->cart->add(
            $product->id,
            $product->name,
            (float) $variant->price,
            $thumbnail,
            $quantity,
            $variant->exists ? $variant->id : null,
            $variantLabel,
            $variant->sku,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Added to cart.',
                'cart_count' => $this->cart->count(),
            ]);
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $this->cart->update($data['key'], $data['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate(['key' => ['required', 'string']]);
        $this->cart->remove($data['key']);

        return back()->with('success', 'Item removed.');
    }
}
