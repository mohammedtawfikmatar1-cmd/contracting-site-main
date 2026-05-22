<?php

namespace App\Http\Middleware;

use App\Services\SiteCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearSiteCacheAfterAdminMutation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe()) {
            SiteCache::flush();
        }

        return $response;
    }
}
