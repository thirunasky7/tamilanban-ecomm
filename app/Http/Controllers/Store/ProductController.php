<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductVariantService;
use App\Support\ProductQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private ProductVariantService $variants)
    {
    }

    public function index(Request $request): View
    {
        $products = ProductQuery::card()
            ->withCount(['productVariants as variants_count'])
            ->when($request->category, function ($q) use ($request) {
                $q->whereHas('categories', fn ($c) => $c->where('slug', $request->category));
            })
            ->when($request->tag === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when($request->tag === 'new', fn ($q) => $q->where('is_new', true))
            ->when($request->tag === 'bestseller', fn ($q) => $q->where('is_bestseller', true))
            ->when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when(! $request->sort, fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        $categories = Cache::remember('store.categories', 3600, fn () => Category::query()
            ->select(['id', 'name', 'slug'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get());

        return view('store.products.index', compact('products', 'categories'));
    }

    public function show(string $slug, Request $request): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'categories:id,name,slug',
                'approvedReviews' => fn ($q) => $q->latest()->limit(20),
                'attributes.values',
                'productVariants.attributeValues.attribute',
            ])
            ->withAvg(['approvedReviews as avg_rating'], 'rating')
            ->withCount(['approvedReviews as reviews_count'])
            ->firstOrFail();

        if ($request->filled('ref')) {
            session(['cart.referral' => $request->string('ref')->toString()]);
        }

        return view('store.products.show', [
            'product' => $product,
            'variantPayload' => $this->variants->storefrontPayload($product),
        ]);
    }
}
