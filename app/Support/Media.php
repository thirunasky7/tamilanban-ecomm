<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Media
{
    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public static function delete(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public static function deleteMany(?array $paths): void
    {
        foreach (array_unique(array_filter($paths ?? [])) as $path) {
            self::delete($path);
        }
    }

    public static function urls(?array $paths): array
    {
        return collect($paths ?? [])
            ->map(fn ($path) => self::url($path))
            ->filter()
            ->values()
            ->all();
    }
}
