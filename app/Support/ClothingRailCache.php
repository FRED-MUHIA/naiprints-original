<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ClothingRailCache
{
    private const VERSION_KEY = 'home.clothing-rail.catalog-version';

    public static function version(): string
    {
        return (string) Cache::get(self::VERSION_KEY, 'initial');
    }

    public static function bust(): void
    {
        Cache::put(self::VERSION_KEY, (string) Str::uuid(), now()->addYear());
    }
}
