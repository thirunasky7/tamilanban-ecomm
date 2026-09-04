<?php

namespace App\Http\Middleware;

use App\Support\LocaleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionLocale = $request->session()->get('locale');
        $locale = is_string($sessionLocale)
            ? LocaleManager::normalize($sessionLocale)
            : null;

        if (! $locale || ! LocaleManager::isEnabled($locale)) {
            $locale = LocaleManager::defaultLocale();
            $request->session()->put('locale', $locale);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
