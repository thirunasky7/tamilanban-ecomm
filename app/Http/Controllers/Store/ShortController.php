<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Short;
use Illuminate\View\View;

class ShortController extends Controller
{
    public function index(): View
    {
        return view('store.shorts.index', [
            'shorts' => Short::query()
                ->select(['id', 'title', 'thumbnail', 'video_url', 'product_id', 'sort_order'])
                ->where('is_active', true)
                ->with(['product:id,name,slug,price,thumbnail'])
                ->orderBy('sort_order')
                ->limit(50)
                ->get(),
        ]);
    }
}
