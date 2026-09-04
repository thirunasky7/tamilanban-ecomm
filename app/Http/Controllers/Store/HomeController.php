<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Short;
use App\Support\ProductQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $data = Cache::remember('store.home.v2', 300, function () {
            $productList = fn (callable $scope) => ProductQuery::card()
                ->tap($scope)
                ->latest()
                ->limit(10)
                ->get();

            return [
                'banners' => Banner::query()
                    ->select(['id', 'title', 'subtitle', 'image', 'link', 'sort_order'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(),
                'categories' => Category::query()
                    ->select(['id', 'name', 'slug', 'image', 'sort_order'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(),
                'featured' => $productList(fn ($q) => $q->where('is_featured', true)),
                'newest' => $productList(fn ($q) => $q->where('is_new', true)),
                'bestsellers' => $productList(fn ($q) => $q->where('is_bestseller', true)),
                'shorts' => Short::query()
                    ->select(['id', 'title', 'thumbnail', 'product_id', 'sort_order'])
                    ->where('is_active', true)
                    ->with(['product:id,name,price'])
                    ->orderBy('sort_order')
                    ->limit(10)
                    ->get(),
            ];
        });

        return view('store.home', $data);
    }
}
