<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        return view('admin.languages.index', [
            'locales' => LocaleManager::LOCALES,
            'defaultLocale' => LocaleManager::defaultLocale(),
            'switcherEnabled' => LocaleManager::switcherEnabled(),
            'enabled' => [
                'en' => LocaleManager::isEnabled('en'),
                'ar' => LocaleManager::isEnabled('ar'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_locale' => ['required', 'in:en,ar'],
            'locale_en_enabled' => ['nullable', 'boolean'],
            'locale_ar_enabled' => ['nullable', 'boolean'],
            'locale_switcher_enabled' => ['nullable', 'boolean'],
        ]);

        $enEnabled = $request->boolean('locale_en_enabled');
        $arEnabled = $request->boolean('locale_ar_enabled');

        if (! $enEnabled && ! $arEnabled) {
            return back()->with('error', __('admin.languages_at_least_one'));
        }

        if ($data['default_locale'] === 'en' && ! $enEnabled) {
            return back()->with('error', __('admin.languages_default_must_be_enabled'));
        }

        if ($data['default_locale'] === 'ar' && ! $arEnabled) {
            return back()->with('error', __('admin.languages_default_must_be_enabled'));
        }

        Setting::setValue('default_locale', $data['default_locale'], 'localization');
        Setting::setValue('locale_en_enabled', $enEnabled ? '1' : '0', 'localization', 'boolean');
        Setting::setValue('locale_ar_enabled', $arEnabled ? '1' : '0', 'localization', 'boolean');
        Setting::setValue(
            'locale_switcher_enabled',
            $request->boolean('locale_switcher_enabled') ? '1' : '0',
            'localization',
            'boolean'
        );

        // Keep current session usable if its locale was disabled.
        $sessionLocale = session('locale');
        if ($sessionLocale && ! LocaleManager::isEnabled($sessionLocale)) {
            session(['locale' => $data['default_locale']]);
        }

        return back()->with('success', __('admin.languages_saved'));
    }
}
