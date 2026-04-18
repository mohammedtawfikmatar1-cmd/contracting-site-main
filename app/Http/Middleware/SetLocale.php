<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $enabled = (bool) Setting::getValue('enable_multilingual', false);
        if (! $enabled) {
            app()->setLocale('ar');

            return $next($request);
        }

        $locale = (string) $request->session()->get('locale', 'ar');
        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}

