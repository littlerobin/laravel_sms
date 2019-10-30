<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;


class Language {


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1);
        $languages = \App\Models\Language::select('code')->where('is_active',1)->get()->toArray();
        $browserLang = strtolower(trim(substr($request->server('HTTP_ACCEPT_LANGUAGE'),0,2)));
        $defaultLanguage = config('app.locale');

        $segments = $request->segments();

        if(in_array($browserLang, array_column($languages, 'code'))) {
            $defaultLanguage = $browserLang;
        }
        $current = session('currentLang') ?  session('currentLang') : $defaultLanguage ;

        if (strlen($locale) != 2 && $current != $defaultLanguage) {
            array_unshift($segments, $current);
            $to = implode('/', $segments);
            App::setLocale($current);

            return redirect($to);
        }

        if (in_array($locale, array_column($languages,'code'))) {
            App::setLocale($locale);
            session(['currentLang' => $locale]);
            return $next($request);
        }


        if (strlen($locale) == 2) {
            $segments[0] = $defaultLanguage;
        } else {
            array_unshift($segments,$current);
        }

        $to = implode('/', $segments);

        return redirect($to);
    }

}