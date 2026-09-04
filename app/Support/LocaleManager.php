<?php

namespace App\Support;

use App\Models\Setting;

class LocaleManager
{
    public const LOCALES = [
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native' => 'English',
            'dir' => 'ltr',
        ],
        'ar' => [
            'code' => 'ar',
            'name' => 'Arabic',
            'native' => 'العربية',
            'dir' => 'rtl',
        ],
    ];

    public static function defaultLocale(): string
    {
        $locale = (string) Setting::getValue('default_locale', 'en');

        return self::isEnabled($locale) ? $locale : 'en';
    }

    public static function isEnabled(string $locale): bool
    {
        if (! array_key_exists($locale, self::LOCALES)) {
            return false;
        }

        return filter_var(
            Setting::getValue("locale_{$locale}_enabled", '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function enabledLocales(): array
    {
        return collect(self::LOCALES)
            ->filter(fn ($meta, $code) => self::isEnabled($code))
            ->all();
    }

    public static function switcherEnabled(): bool
    {
        return filter_var(
            Setting::getValue('locale_switcher_enabled', '1'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public static function isRtl(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        return (self::LOCALES[$locale]['dir'] ?? 'ltr') === 'rtl';
    }

    public static function dir(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function htmlLang(?string $locale = null): string
    {
        return $locale ?? app()->getLocale();
    }

    public static function normalize(string $locale): ?string
    {
        $locale = strtolower(substr($locale, 0, 2));

        return array_key_exists($locale, self::LOCALES) ? $locale : null;
    }
}
