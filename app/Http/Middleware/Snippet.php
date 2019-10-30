<?php

namespace App\Http\Middleware;

use Closure;

class Snippet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle ($request, Closure $next)
    {
        $languages = \App\Models\Language::select('code')->where('is_active',1)->get()->toArray();
        $browserLanguage = $request->get('language','en');

        if (!in_array($browserLanguage,array_column($languages,'code'))) {
            $browserLanguage = 'en';
        }
        \App::setLocale($browserLanguage);

        $referer = parse_url ($request->header('referer'),PHP_URL_HOST);

        if (strpos($referer,'callburn.com')) {
            return $next($request);
        }

        $token = $request->get('token');
        $hosts = array();
        $response = [
            'error' => [
                'no' => -10,
                'text' => 'referrer_not_allowed_to_send_request'
            ]

        ];

        $snippet = \App\Models\Snippet::where('api_token', $token)->first();

        if (!$snippet) {
            return response()->json(['resource' => $response],405);
        }

        $allowedUrls = explode(",",$snippet->allowed_url);

        foreach ($allowedUrls as $url) {
            $hosts[] = parse_url(trim($url), PHP_URL_HOST);
        }

        if (in_array($referer,$hosts)) {
            return $next($request);
        }

        return response()->json(['resource' => $response],405);


    }
}
