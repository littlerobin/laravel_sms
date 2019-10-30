<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;


class LanguageControl
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $languages = \App\Models\Language::select('code')->where('is_active',1)->get()->toArray();

        if(! Cookie::get('callburn-locale')) {

            $browserLang = strtolower(trim(substr($request->server('HTTP_ACCEPT_LANGUAGE'),0,2)));

            $defaultLang = $browserLang ? $browserLang : \Config::get('app.locale');

            if(!in_array($defaultLang,array_column($languages,'code'))) {
                $defaultLang = \Config::get('app.locale');
            }


            App::setLocale($defaultLang);

            return $next($request)->withCookie(cookie()->forever('callburn-locale', $defaultLang));

        }

        $locale = Cookie::get('callburn-locale');

        App::setLocale($locale);

        return $response = $next($request);



    }
}
