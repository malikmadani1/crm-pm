<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['en', 'ar']);
        $locale = session('locale');

        if (! $locale && $request->user()?->locale) {
            $locale = $request->user()->locale;
        }

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        $timezone = $request->user()?->timezone ?: config('app.timezone', 'Asia/Damascus');

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = config('app.timezone', 'Asia/Damascus');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
