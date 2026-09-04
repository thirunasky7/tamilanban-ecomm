<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer(['partials.store.header', 'partials.store.bottom-nav'], function ($view) {
            static $shared = null;

            if ($shared === null) {
                $shared = [
                    'cartCount' => app(CartService::class)->count(),
                ];
            }

            $view->with($shared);
        });
    }
}
