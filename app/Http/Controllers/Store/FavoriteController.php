<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', ProductFavorite::query()
                ->where('user_id', $request->user()->id)
                ->select('product_id'))
            ->with(['categories', 'approvedReviews'])
            ->latest('id')
            ->get();

        return view('store.favorites.index', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        if (! $product->is_active) {
            abort(404);
        }

        $existing = ProductFavorite::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Removed from favorites.';
        } else {
            ProductFavorite::query()->create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ]);
            $message = 'Added to favorites.';
        }

        return back()->with('success', $message);
    }
}
