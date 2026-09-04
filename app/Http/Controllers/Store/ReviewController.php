<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = auth()->user();

        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user?->id,
            'reviewer_name' => $user?->name ?? 'Guest User',
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'is_approved' => true,
        ]);

        return back()->with('success', 'Thank you! Your review has been posted.');
    }
}
