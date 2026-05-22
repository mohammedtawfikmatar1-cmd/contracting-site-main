<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class SiteCache
{
    private const KEY_REGISTRY = 'site:cache:keys';

    /**
     * Store public-site data until an admin change invalidates it.
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        self::registerKey($key);

        return Cache::rememberForever($key, $callback);
    }

    /**
     * Remove all cached public-site entries without touching sessions or framework cache.
     */
    public static function flush(): void
    {
        try {
            foreach (self::keys() as $key) {
                Cache::forget($key);
            }

            Cache::forget(self::KEY_REGISTRY);
        } catch (\Throwable $e) {
        }
    }

    public static function forget(string $key): void
    {
        try {
            Cache::forget($key);

            $keys = array_values(array_filter(
                self::keys(),
                fn (string $storedKey): bool => $storedKey !== $key
            ));

            Cache::forever(self::KEY_REGISTRY, $keys);
        } catch (\Throwable $e) {
        }
    }

    /**
     * @return array<int, string>
     */
    private static function keys(): array
    {
        $keys = Cache::get(self::KEY_REGISTRY, []);

        return is_array($keys) ? array_values(array_unique($keys)) : [];
    }

    private static function registerKey(string $key): void
    {
        if ($key === self::KEY_REGISTRY) {
            return;
        }

        try {
            $keys = self::keys();

            if (! in_array($key, $keys, true)) {
                $keys[] = $key;
                Cache::forever(self::KEY_REGISTRY, $keys);
            }
        } catch (\Throwable $e) {
        }
    }
}
