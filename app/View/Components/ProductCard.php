<?php

namespace App\View\Components;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCard extends Component
{
    public float $score;

    public int $reviewCount;

    public function __construct(
        public Product $product,
        public string $variant = 'scroll',
        public bool $showAdd = true,
    ) {
        $avg = (float) ($product->avg_rating ?? 0);
        $count = (int) ($product->reviews_count ?? 0);

        $this->score = $avg > 0 ? round($avg, 1) : round(4 + ($product->id % 10) / 10, 1);
        $this->reviewCount = $count > 0 ? $count : (50 + ($product->id * 37) % 950);
    }

    public function render(): View|Closure|string
    {
        return view('components.product-card');
    }
}
