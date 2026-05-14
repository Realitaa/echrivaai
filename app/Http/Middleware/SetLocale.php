<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\Locales;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = Locales::toArray();
        $locale = config('app.locale');

        if ($request->user()?->locale) {
            $locale = $request->user()->locale;
        }

        elseif ($request->cookie('locale')) {

            $cookieLocale = $request->cookie('locale');

            if (in_array($cookieLocale, $supportedLocales)) {
                $locale = $cookieLocale;
            }
        }

        else {

            $browserLocale = substr(
                $request->server('HTTP_ACCEPT_LANGUAGE', ''),
                0,
                2
            );

            if (in_array($browserLocale, $supportedLocales)) {
                $locale = $browserLocale;
            }
        }

        app()->setLocale($locale);

        $response = $next($request);

        // Sync cookie if it differs from resolved locale
        if ($request->cookie('locale') !== $locale) {
            $response->withCookie(cookie('locale', $locale, 60 * 24 * 30));
        }

        return $response;
    }
}
