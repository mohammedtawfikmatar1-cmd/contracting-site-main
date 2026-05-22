<?php

namespace App\Models\Concerns;

use App\Services\SiteCache;
use Illuminate\Database\Eloquent\Model;

trait ClearsSiteCache
{
    protected static function bootClearsSiteCache(): void
    {
        static::saved(function (Model $model): void {
            SiteCache::flush();
        });

        static::deleted(function (Model $model): void {
            SiteCache::flush();
        });
    }
}
