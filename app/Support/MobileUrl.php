<?php

namespace App\Support;

class MobileUrl
{
    public static function absolute(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = str_starts_with($path, '/') ? $path : '/'.ltrim($path, '/');

        return rtrim((string) config('app.url'), '/').$normalized;
    }

    public static function many(?array $paths): array
    {
        return collect($paths ?? [])
            ->map(fn ($path) => self::absolute(is_string($path) ? Media::url($path) : null))
            ->filter()
            ->values()
            ->all();
    }
}
