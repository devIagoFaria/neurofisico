<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * use Illuminate\Support\Facades\Session;
     */
    public function handle(Request $request, Closure $next): Response
    {

        $localeLanguage = Session::get('locale')
            ?? Session::get('languageName')
            ?? (getLogInUser() ? getLogInUser()->language : null);

        $lan = Setting::where('key', 'language')->first();

        App::setLocale(
            $localeLanguage ?: ($lan ? $lan->value : 'pt')
        );

        return $next($request);
    }
}
