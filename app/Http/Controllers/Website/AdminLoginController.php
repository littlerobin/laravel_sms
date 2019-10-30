<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\User;
use JWTAuth;


class AdminLoginController extends WebsiteController
{
    public function tokenCheck ($token)
    {
        $user = User::where('admin_token', $token)->first();

        if (!$user) {
            return view('front.admin.login',[
                'error' => 1,
                'jwtToken' => null,
            ]);
        }

        $now = Carbon::now();
        $adminTokenExpirationDate = Carbon::parse($user->admin_token_expiration_date);

        if (!$now->lt($adminTokenExpirationDate)) {
            return view('front.admin.login',[
                'error' => 1,
                'jwtToken' => null,
            ]);
        }

        $user->admin_token = null;

        if($user->save()) {
            $adminToken = JWTAuth::fromUser($user);

            return view('front.admin.login',[
                'error' => 0,
                'jwtToken' => $adminToken,
            ]);
        }  else {

            return view('front.admin.login',[
                'error' => 1
            ]);
        }
    }
}
