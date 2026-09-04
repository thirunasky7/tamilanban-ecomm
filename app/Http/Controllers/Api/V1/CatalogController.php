<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\CatalogTransformer;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Short;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home(): JsonResponse
    {
        $featured = Product::query()
            ->where(['is_active' => true, 'is_featured' => true])
            ->with(['categories', 'approvedReviews'])
            ->latest()
            ->limit(10)
            ->get();

        $newArrivals = Product::query()
            ->where(['is_active' => true, 'is_new' => true])
            ->with(['categories', 'approvedReviews'])
            ->latest()
            ->limit(10)
            ->get();

        $bestsellers = Product::query()
            ->where(['is_active' => true, 'is_bestseller' => true])
            ->with(['categories', 'approvedReviews'])
            ->latest()
            ->limit(10)
            ->get();

        if ($featured->isEmpty()) {
            $featured = Product::query()->where('is_active', true)->with(['categories', 'approvedReviews'])->latest()->limit(10)->get();
        }
        if ($newArrivals->isEmpty()) {
            $newArrivals = Product::query()->where('is_active', true)->with(['categories', 'approvedReviews'])->latest()->limit(10)->get();
        }
        if ($bestsellers->isEmpty()) {
            $bestsellers = Product::query()->where('is_active', true)->with(['categories', 'approvedReviews'])->orderByDesc('id')->limit(10)->get();
        }

        return response()->json([
            'banners' => Banner::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->map(fn (Banner $banner) => CatalogTransformer::banner($banner))->values(),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->map(fn (Category $category) => CatalogTransformer::category($category))->values(),
            'featured' => CatalogTransformer::products($featured),
            'new_arrivals' => CatalogTransformer::products($newArrivals),
            'bestsellers' => CatalogTransformer::products($bestsellers),
            'shorts' => Short::query()->where('is_active', true)->with('product')->orderBy('sort_order')->limit(20)->get()
                ->map(fn (Short $short) => CatalogTransformer::short($short))->values(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['categories', 'approvedReviews', 'attributes.values'])
            ->when($request->category_id, fn ($q) => $q->whereHas(
                'categories',
                fn ($c) => $c->where('categories.id', $request->category_id)
            ))
            ->when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->tag === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when($request->tag === 'new', fn ($q) => $q->where('is_new', true))
            ->when($request->tag === 'bestseller', fn ($q) => $q->where('is_bestseller', true))
            ->when($request->min_price, fn ($q) => $q->where('price', '>=', (float) $request->min_price))
            ->when($request->max_price, fn ($q) => $q->where('price', '<=', (float) $request->max_price));

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popularity' => $query->orderByDesc('is_bestseller')->latest(),
            default => $query->latest(),
        };

        $paginator = $query->paginate((int) $request->get('per_page', 50));

        return response()->json([
            'products' => CatalogTransformer::products(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function product(string $idOrSlug): JsonResponse
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($idOrSlug) {
                $q->where('slug', $idOrSlug);
                if (ctype_digit($idOrSlug)) {
                    $q->orWhere('id', (int) $idOrSlug);
                }
            })
            ->with(['categories', 'approvedReviews', 'productVariants.attributeValues.attribute', 'attributes.values'])
            ->firstOrFail();

        return response()->json(CatalogTransformer::product($product, detailed: true));
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->map(fn (Category $category) => CatalogTransformer::category($category))->values(),
        ]);
    }
}
