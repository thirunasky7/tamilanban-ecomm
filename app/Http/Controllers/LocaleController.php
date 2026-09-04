<?php

namespace App\Http\Controllers;

use App\Support\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $normalized = LocaleManager::normalize($locale);

        if (! $normalized || ! LocaleManager::isEnabled($normalized)) {
            return back()->with('error', __('store.language_unavailable'));
        }

        // Storefront switcher can be disabled; admins may still switch for preview.
        if (! LocaleManager::switcherEnabled() && ! auth('admin')->check()) {
            return back()->with('error', __('store.language_switch_disabled'));
        }

        $request->session()->put('locale', $normalized);

        return back();
    }
}
