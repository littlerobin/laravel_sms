<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\InvitationParam;
use App\Services\FileService;
use App\Services\SlackNotificationService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use JWTAuth;
use Session;
use Socialize;

class SocialConnectionsController extends WebsiteController
{
    /**
     * Create a new instance of SocialConnectionsController class.
     *
     * @return void
     */

    private $activityLogRepo;

    public function __construct()
    {
        //$this->middleware('guest');
        $this->activityLogRepo = new \App\Services\ActivityLogService();
    }

    public function getFacebookLogin(Request $request)
    {
        $localDateFormat = $request->get('local_date_format');
        $connect = $request->get('connect');
        $jwtToken = $request->get('jwtToken');
        session()->put('local_date_format', $localDateFormat);
        session()->put('connect', $connect);
        session()->put('jwtToken', $jwtToken);

        return Socialize::driver('facebook')->fields([
            'first_name',
            'last_name',
            'email',
            'gender',
            'birthday',
        ])->scopes([
            'email',
            'user_birthday',
        ])->redirect();
    }

    public function getFacebookCallback(Request $request, UserService $userRepo, FileService $fileService)
    {
        try {
            $user = Socialize::driver('facebook')->fields([
                'first_name',
                'last_name',
                'email',
                'gender',
                'birthday',
            ])->stateless()->user();
        } catch (\Exception $e) {
            if ($e) {
                return "<script type='text/javascript'>
                        window.success = 'error';"
                        .
                        "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                        </script>";
            }
        }

        if (session()->pull('connect')) {
            $jwtToken = session()->pull('jwtToken');
            $currentUser = JWTAuth::toUser($jwtToken);
            if ($currentUser) {
                $currentUser->update(['facebook_email'=>$user->email]);
            } else {
                return "<script type='text/javascript'>
                        window.success = 'error';"
                        .
                        "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                        </script>";
            }
            return "<script type='text/javascript'>
                    window.success = 'success';
                    setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }

        $countryCode = \App\Services\InfoService::getCountryCode($request);

        if (array_key_exists('birthday', $user->user)) {
            $birthday = $user->user['birthday'] ? \Carbon\Carbon::parse($user->user['birthday']) : null;
        } else {
            $birthday = null;
        }

        $name = $user->user['first_name'] . ' ' . $user->user['last_name'];

        $facebookToken = $user->token;
        $email = $user->getEmail();
        $facebookId = $user->getId();
        $lastIp = $request->ip();
        $agent = new \Jenssegers\Agent\Agent();

        if ($agent->isDesktop()) {
            $browser = $agent->browser();
            $platform = $agent->platform();
            $httpAgent = $browser . ' on ' . $platform;
        } else {
            $httpAgent = $agent->device();
        }

        // get user image
        $imageName = str_random(30) . '.jpg';

        $filePath = public_path() . '/uploads/img/';

        file_put_contents($filePath . $imageName, file_get_contents($user->getAvatar()));

        $fileService->moveImageToAmazon($imageName, $filePath);

        \File::delete($filePath . $imageName);

        $validator = \Validator::make(
            [
                'facebook_id' => $facebookId,
                'facebook_token' => $facebookToken,
                'email' => $email,
            ],
            [
                'facebook_id' => 'required',
                'facebook_token' => 'required',
                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            return "<script type='text/javascript'>
                        window.success = 'error';" .
                "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }
        $user = $userRepo->getUserByEmail($email);
        $apiKey = str_random(20);
        if (!$user) {
            $userData = [
                'email' => $email,
                'personal_name' => $name,
                'facebook_id' => $facebookId,
                'facebook_access_token' => $facebookToken,
                'last_ip' => $lastIp,
                'image_name' => $imageName,
                'is_active' => 1,
                'birthday' => $birthday,
            ];
            $timezone = \App\Services\InfoService::getTimezoneName($request);
            if ($timezone) {
                $userData['timezone'] = $timezone;
            }

            if ($countryCode) {
                $user['country_code'] = strtolower($countryCode);
            }
            $user = $userRepo->createUser($userData);

            $token = new \App\Models\ApiToken();
            $token->user_id = $user->_id;
            $token->api_token = $apiKey;
            $token->ip_address = $lastIp;
            $token->agent = $httpAgent;
            $token->device = 'WEBSITE';
            $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
            $token->session_id = \Session::getId();
            $token->save();

            $logData = [
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'REGISTRATION-LOGIN',
                'description' => 'User registered using facebook',
            ];
            $this->activityLogRepo->createActivityLog($logData);
            SlackNotificationService::notify('User registered with facebook - ' . $email);

            $jwtToken = JWTAuth::fromUser($user);

            $this->checkInvitation($user);

            return "<script type='text/javascript'>
                        window.jwtToken = '" . $jwtToken . "';
                        window.user_id = '". $user->_id ."';
                        window.success = 'success';
                        window.is_registration = true;
                        setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        } elseif ($user->is_deleted) {
            return "<script type='text/javascript'>
                        window.success = 'deactivated';" .
                "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }

        $user->image_name = $imageName;

        $user->last_ip = $lastIp;
        $user->facebook_id = $facebookId;
        $user->facebook_access_token = $facebookToken;
        if ($countryCode && !$user->country_code) {

            $user->country_code = strtolower($countryCode);
        }
        $user->local_date_format = session()->pull('local_date_format');

        if (!$user->personal_name) {
            $user->personal_name = $name;
        }

        $user->save();

        $token = new \App\Models\ApiToken();
        $token->user_id = $user->_id;
        $token->api_token = $apiKey;
        $token->ip_address = $lastIp;
        $token->agent = $httpAgent;
        $token->device = 'WEBSITE';
        $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
        $token->session_id = \Session::getId();
        $token->save();

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'REGISTRATION-LOGIN',
            'description' => 'User logged in using facebook app',
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $response = [
            'api_key' => $apiKey,
            'user_data' => $user,
            'error' => [
                'no' => 0,
                'text' => 'Success',
            ],
        ];
        $jwtToken = JWTAuth::fromUser($user);

        $this->checkInvitation($user);

        return "<script type='text/javascript'>
                        window.jwtToken = '" . $jwtToken . "';
                        window.user_id = '". $user->_id ."';
                        window.success = 'success';" .
            "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
    }

    public function getGitHubCallback(Request $request, UserService $userRepo, FileService $fileService)
    {
        $user = Socialize::driver('github')->stateless()->user();
        if (session()->pull('connect')) {
            $jwtToken = session()->pull('jwtToken');
            $currentUser = JWTAuth::toUser($jwtToken);
            if ($currentUser) {
                $currentUser->update(['github_email' => $user->getEmail()]);
            } else {
                return "<script type='text/javascript'>
                        window.success = 'error';"
                        .
                        "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                        </script>";
            }
            return "<script type='text/javascript'>
                    window.success = 'success';"
                    .
                    "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }
        $name = $user->getName();

        $uploadFolder = '/social/img/';

        $countryCode = \App\Services\InfoService::getCountryCode($request);
        // get user image
        $imageName = str_random(30) . '.jpg';

        $filePath = public_path() . '/uploads/img/';

        file_put_contents($filePath . $imageName, file_get_contents($user->getAvatar()));

        $fileService->moveImageToAmazon($imageName, $filePath);

        ///////////////////

        \File::delete($filePath . $imageName);

        $email = $user->getEmail();
        $gitHubId = $user->getId();
        $lastIp = $request->ip();

        $agent = new \Jenssegers\Agent\Agent();
        if ($agent->isDesktop()) {
            $browser = $agent->browser();
            $platform = $agent->platform();
            $httpAgent = $browser . ' on ' . $platform;
        } else {
            $httpAgent = $agent->device();
        }

        $validator = \Validator::make(
            [
                'facebook_id' => $gitHubId,

                'email' => $email,
            ],
            [
                'facebook_id' => 'required',

                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            return "<script type='text/javascript'>
                        window.success = 'error';" .
                "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }
        $user = $userRepo->getUserByEmail($email);
        $apiKey = str_random(20);
        if (!$user) {
            $userData = [
                'email' => $email,
                'personal_name' => $name,
                'facebook_id' => $gitHubId,
                'is_active' => 1,
                'image_name' => $imageName,
                'last_ip' => $lastIp,
            ];

            $timezone = \App\Services\InfoService::getTimezoneName($request);

            if ($timezone) {
                $userData['timezone'] = $timezone;
            }

            if ($countryCode) {

                $user['country_code'] = strtolower($countryCode);
            }

            $user = $userRepo->createUser($userData);

            //Auth::login($user);

            $token = new \App\Models\ApiToken();
            $token->user_id = $user->_id;
            $token->api_token = $apiKey;
            $token->ip_address = $lastIp;
            $token->agent = $httpAgent;
            $token->device = 'WEBSITE';
            $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
            $token->session_id = \Session::getId();
            $token->save();

            //VERIFY PHONENUMBER IF EXIST
            //$phoneNumber = $request->get('phonenumber');
            //$code = $request->get('code');
            //$this->checkPhonenumberCodeAndAddToAccount($phoneNumber, $code, $user);
            //END VERIFY PHONENUMBER IF EXIST

            $logData = [
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'REGISTRATION-LOGIN',
                'description' => 'User registered using facebook',
            ];
            $this->activityLogRepo->createActivityLog($logData);
            SlackNotificationService::notify('User registered with github - ' . $email);

            $jwtToken = JWTAuth::fromUser($user);

            return "<script type='text/javascript'>
                        window.jwtToken = '" . $jwtToken . "';
                        window.user_id = '". $user->_id ."';
                        window.success = 'success';
                        window.is_registration = true;
                        setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        } elseif ($user->is_deleted) {
            return "<script type='text/javascript'>
                        window.success = 'deactivated';" .
                "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }

        $user->image_name = $imageName;
        $user->last_ip = $lastIp;
        $user->github_id = $gitHubId;
        if ($countryCode && !$user->country_code) {

            $user->country_code = strtolower($countryCode);
        }

        $user->local_date_format = session()->pull('local_date_format');

        $user->save();

        //Auth::login($user);

        $token = new \App\Models\ApiToken();
        $token->user_id = $user->_id;
        $token->api_token = $apiKey;
        $token->ip_address = $lastIp;
        $token->agent = $httpAgent;
        $token->device = 'WEBSITE';
        $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
        $token->session_id = \Session::getId();
        $token->save();

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'REGISTRATION-LOGIN',
            'description' => 'User logged in using facebook app',
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $response = [
            'api_key' => $apiKey,
            'user_data' => $user,
            'error' => [
                'no' => 0,
                'text' => 'Success',
            ],
        ];
        $jwtToken = JWTAuth::fromUser($user);

        return "<script type='text/javascript'>
                        window.jwtToken = '" . $jwtToken . "';
                        window.success = 'success';" .
            "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
    }

    public function getGoogleCallback(Request $request, UserService $userRepo, FileService $fileService)
    {
        $user = Socialize::driver('google')->stateless()->user();

        if (session()->pull('connect')) {
            $jwtToken = session()->pull('jwtToken');
            $currentUser = JWTAuth::toUser($jwtToken);
            if ($currentUser) {
                $currentUser->update(['gmail_email'=>$user->email]);
            } else {
                return "<script type='text/javascript'>
                        window.success = 'error';"
                        .
                        "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                        </script>";
            }
            return "<script type='text/javascript'>
                    window.success = 'success';"
                    .
                    "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }

        $countryCode = \App\Services\InfoService::getCountryCode($request);
        $name = $user->name;
        $email = $user->email;
        $googleId = $user->id;
        $googleToken = $user->token;
        $lastIp = $request->ip();

        $imageName = str_random(30) . '.jpg';

        $filePath = public_path() . '/uploads/img/';

        file_put_contents($filePath . $imageName, file_get_contents($user->getAvatar()));

        $fileService->moveImageToAmazon($imageName, $filePath);

        \File::delete($filePath . $imageName);

        $validator = \Validator::make([
            'google_id' => $googleId,
            'google_token' => $googleToken,
            'email' => $email,
        ], [
            'google_id' => 'required',
            'google_token' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return "<script type='text/javascript'>

                        window.success = 'error';" .
                "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }
        $user = $userRepo->getUserByEmail($email);
        $apiKey = str_random(20);
        if (!$user) {
            $userData = [
                'email' => $email,
                'personal_name' => $name,
                'google_id' => $googleId,
                'google_access_token' => $googleToken,
                'last_ip' => $lastIp,
                'image_name' => $imageName,
                'is_active' => 1,
            ];

            $timezone = \App\Services\InfoService::getTimezoneName($request);

            if ($timezone) {
                $userData['timezone'] = $timezone;
            }

            if ($countryCode) {

                $user['country_code'] = strtolower($countryCode);
            }
            $user = $userRepo->createUser($userData);

            $agent = new \Jenssegers\Agent\Agent();
            if ($agent->isDesktop()) {
                $browser = $agent->browser();
                $platform = $agent->platform();
                $httpAgent = $browser . ' on ' . $platform;
            } else {
                $httpAgent = $agent->device();
            }

            //Auth::login($user);
            $jwtToken = JWTAuth::fromUser($user);

            $token = new \App\Models\ApiToken();
            $token->user_id = $user->_id;
            $token->api_token = $apiKey;
            $token->ip_address = $lastIp;
            $token->agent = $httpAgent;
            $token->device = 'WEBSITE';
            $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
            $token->session_id = \Session::getId();
            $token->save();

            //VERIFY PHONENUMBER IF EXIST
            //$phoneNumber = $request->get('phonenumber');
            //$code = $request->get('code');
            //$this->checkPhonenumberCodeAndAddToAccount($phoneNumber, $code, $user);
            //END VERIFY PHONENUMBER IF EXIST

            $this->checkInvitation($user);

            $logData = [
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'REGISTRATION-LOGIN',
                'description' => 'User registered using google app',
            ];
            $this->activityLogRepo->createActivityLog($logData);
            SlackNotificationService::notify('User registered with gmail - ' . $email);

            return "<script type='text/javascript'>
                        window.jwtToken = '" . $jwtToken . "';
                        window.user_id = '". $user->_id ."';
                        window.success = 'success';
                        window.is_registration = true;
                        setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        } elseif ($user->is_deleted) {
            return "<script type='text/javascript'>
                        window.success = 'deactivated';" .
                "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
        }
        /*if($user->facebook_id != $googleId){
        $response = $this->createBasicResponse(-2, 'Invalid data');
        return response()->json(['resource' => $response]);
        }*/

        $user->image_name = $imageName;
        $user->last_ip = $lastIp;
        $user->google_id = $googleId;
        $user->google_access_token = $googleToken;
        if ($countryCode && !$user->country_code) {

            $user->country_code = strtolower($countryCode);
        }

        $user->local_date_format = session()->pull('local_date_format');

        $user->save();

        $this->checkInvitation($user);
        $jwtToken = JWTAuth::fromUser($user);

        $agent = new \Jenssegers\Agent\Agent();
        if ($agent->isDesktop()) {
            $browser = $agent->browser();
            $platform = $agent->platform();
            $httpAgent = $browser . ' on ' . $platform;
        } else {
            $httpAgent = $agent->device();
        }

        $token = new \App\Models\ApiToken();
        $token->user_id = $user->_id;
        $token->api_token = $apiKey;
        $token->ip_address = $lastIp;
        $token->agent = $httpAgent;
        $token->device = 'WEBSITE';
        $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
        $token->session_id = \Session::getId();
        $token->save();

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'REGISTRATION-LOGIN',
            'description' => 'User logged in using facebook app',
        ];
        $this->activityLogRepo->createActivityLog($logData);
        return "<script type='text/javascript'>
                        window.jwtToken = '" . $jwtToken . "';
                        window.success = 'success';" .
            "setTimeout(function(){window.close();window.trigger('unload')}, 1000);
                    </script>";
    }

    public function getGithubLogin(Request $request)
    {
        $localDateFormat = $request->get('local_date_format');
        $connect = $request->get('connect');
        $jwtToken = $request->get('jwtToken');
        session()->put('local_date_format', $localDateFormat);
        session()->put('connect', $connect);
        session()->put('jwtToken', $jwtToken);
        return Socialize::driver('github')->redirect();
    }

    public function getGoogleLogin(Request $request)
    {
        $localDateFormat = $request->get('local_date_format');
        $connect = $request->get('connect');
        $jwtToken = $request->get('jwtToken');
        session()->put('local_date_format', $localDateFormat);
        session()->put('connect', $connect);
        session()->put('jwtToken', $jwtToken);
        return Socialize::with('google')->redirect();
    }

    public function checkInvitation($user)
    {
        if (session()->get('invitation_token')) {
            $invitation = InvitationParam::whereToken(session()->get('invitation_token'))->first();
            if (!is_null($invitation)) {
                $expirationDate = $invitation->bonus_expiration_date;
                if (is_null($expirationDate) || $expirationDate->diffInDays(Carbon::today()) >= 0) {
                    if ($invitation->bonus_criteria && $invitation->bonus) {
                        $user->bonus += $invitation->bonus;
                        $user->bonus_criteria = $invitation->bonus_criteria;
                        $user->balance += $invitation->bonus;
                        $user->save();
                        $invitation->status = "BONUS";
                        $invitation->save();
                    } elseif ($invitation->bonus) {
                        $user->balance += $invitation->bonus;
                        $user->save();
                        $invitation->status = "BONUS";
                        $invitation->save();
                    }
                }
                $invitation->makeAsAccepted($user);
            }
        }
    }

}
