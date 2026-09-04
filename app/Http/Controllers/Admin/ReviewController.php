<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = ProductReview::query()
            ->with(['product', 'user'])
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->latest()
            ->get();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }

    public function toggle(ProductReview $review): RedirectResponse
    {
        $review->update(['is_approved' => ! $review->is_approved]);

        return back()->with('success', 'Review status updated.');
    }
}

