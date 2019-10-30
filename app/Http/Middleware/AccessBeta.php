<?php

namespace App\Http\Middleware;

use Closure;

class AccessBeta
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
        $user = \Auth::user();
        if($user->can_access_beta or env('VOICE_CALLS')) {
            return $next($request);
        }

        $response = [
            'error' => [
                'no' => -99,
                'message' => 'access denied'
            ],

        ];

        return response()->json(['resource' => $response]);

    }
}
