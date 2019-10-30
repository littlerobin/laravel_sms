<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\SendEmailService;
use App\Services\ActivityLogService;
use App\Services\SlackNotificationService;
use App\Services\InfoService;
use Auth;
use Carbon\Carbon;
use Session;
use Validator;
use JWTAuth;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;




class AuthController extends WebsiteController
{

	/**
	 * Create a new instance of AuthController class
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->sendEmailRepo = new SendEmailService();
		$this->activityLogRepo = new ActivityLogService();
        $this->middleware('jwt.headers',['only' => [
            'getLogout'
        ]]);
        //$this->middleware('guest', ['except' => ['getConfirmEmailAddress', 'getLogout']]);
	}

    /**
     * Send registration eail to the user and create basic account
     * POST /auth/registration
     *
     * @param Request $request
     * @return JSON
     */
    public function postRegistration(Request $request, UserService $userRepo)
    {
        $emailAddress = $request->get('email_address');
        $ip = $request->ip();
        $agent = new \Jenssegers\Agent\Agent();
        if($agent->isDesktop()){
            $browser = $agent->browser();
            $platform = $agent->platform();
            $httpAgent = $browser . ' on ' . $platform;
        } else{
            $httpAgent = $agent->device();
        }

        $localDateFormat = $request->localDateFormat;

        $validator = Validator::make(
		    [
		        'email' => $emailAddress
		    ],
		    [
		        'email' => 'email|required'
		    ]
		);


        if ($validator->fails())
        {
            $failedRules = $validator->failed();
            $errorNumber = -100;
            $errorMessage = 'Something went wrong';
            if (isset($failedRules['email']['Email'])) {
                $errorNumber = -1;
                $errorMessage = 'Invalid Email Address';
            } elseif (isset($failedRules['email']['Required'])) {
                $errorNumber = -3;
                $errorMessage = 'Email Address is required.';
            }

            $response = [
                'error' => [
                    'no' => $errorNumber,
                    'text' =>$errorMessage
                ]
            ];

        } else {
            $confirmationToken = str_random(20);
            // CRISP TOKEN GENERATION ON REGISTRATION 
            $responseToken = str_random(30);

            $language = \App\Models\Language::where('code',$request->get('language','en'))->first();
			// dd($request->get('language'));
            $userData = [
				'email' => $emailAddress,
				'email_confirmation_token' => $confirmationToken,
				'last_ip' => $ip,
                'language_id' => $language->_id,
                'crisp_history_token' => $responseToken,
			];

            $newUser = \App\User::where('email', $emailAddress)->first();


            if (!$newUser) {
				$newUser = $userRepo->createUser($userData);
			} elseif ($newUser->is_active === 1) {
				$response = $this->createBasicResponse(-10, 'email__address_is_already_registered_1');
				return response()->json(['resource' => $response]);
			} else {

				$newUser->email_confirmation_token = $confirmationToken;
				$newUser->last_ip = $ip;
				$newUser->language_id = $language->_id;
				$newUser->local_date_format = $localDateFormat;
				$newUser->crisp_history_token = $responseToken;
				$newUser->save();
			}

			$this->sendEmailRepo->sendConfirmRegistrationEmail($newUser);
			
			SlackNotificationService::notify('User registered with email - ' . $emailAddress);

			$logData = [
				'user_id' => $newUser->_id,
				'device' => 'WEBSITE',
				'action' => 'REGISTRATION-LOGIN',
				'description' => 'User has been registered '
			];
			$this->activityLogRepo->createActivityLog($logData);

			//VERIFY PHONENUMBER IF EXIST
			/*$phoneNumber = $request->get('phonenumber');
			$code = $request->get('code');
			$this->checkPhonenumberCodeAndAddToAccount($phoneNumber, $code, $newUser);*/
			//END VERIFY PHONENUMBER IF EXIST

			$response = [
				'error' => [
					'no' => 0,
					'text' => 'successfully_registered'
				],
                'user_data' => $newUser
			];

			return response()->json(['resource' => $response]); 
		}

		dd( response()->json(['resource' => $response]));

		return response()->json(['resource' => $response]);
    }

    // Commented By Arman
    // public function getCrispToken(Request $request) 
    // {
    // 	$response = [
    // 		'crispToken' => ''
    // 	];
    // 	return response()->json(['resource' => $response]);
    // }

    /**
     * Send request to API for activating account
     * POST /auth/activate-account
     *
     * @param Request $request
     * @return JSON
     */
    public function postActivateAccount(Request $request)
    {
        $ip = $request->ip();
        $emailConfirmationToken = $request->get('email_confirmation_token');
        $password = $request->get('password');
        $passwordConfirmation = $request->get('password_confirmation');
        $phonenumber = $request->get('phonenumber');
        $userName = $request->get('myName');
        $companyName = $request->get('companyName');

        $emailAddress = $request->get('email');
        $code = $request->get('voice_code');
        $sendNewsletter = $request->get('send_newsletter');
        $user = \App\User::where('email', $emailAddress)->first();
		if(!$user){
			$response = $this->createBasicResponse(-1, 'invalid__email');
			return response()->json(['resource' => $response]);
		}
        $validator = Validator::make(
		    [
		        'password' => $password,
		        'password_confirmation' => $passwordConfirmation
		    ],
		    [
		        'password' => 'confirmed|required|min:4|max:20'
		    ]
		);
		if ($validator->fails())
		{
			$failedRules = $validator->failed();
			$errorNumber = -100;
			$errorMessage = trans('main.crud.something_went_wrong');
			if(isset($failedRules['password']['Confirmed'])){
				$errorNumber = -2;
				$errorMessage = trans('main.crud.passwords_do_not_match');
			} elseif(isset($failedRules['password']['Min'])){
				$errorNumber = -3;
				$errorMessage = trans('main.crud.password_is_too_short');
			} elseif(isset($failedRules['password']['Max'])){
				$errorNumber = -4;
				$errorMessage = trans('main.crud.password_is_too_long');
			} elseif(isset($failedRules['password']['AlphaDash'])){
				$errorNumber = -5;
				$errorMessage = trans('main.crud.password_criteria_not_fullfilled');
			} elseif(isset($failedRules['password']['Required'])){
				$errorNumber = -6;
				$errorMessage = trans('main.crud.password_is_required');
			}
			$response = $this->createBasicResponse($errorNumber, $errorMessage);

			return response()->json(['resource' => $response]);
		}

		\DB::beginTransaction();
		try{
			$tariff = NULL;
			if($code){
				$ifPhonenumberValid = $this->checkPhonenumberCodeAndAddToAccount($phonenumber, $code, $user);
				if(!$ifPhonenumberValid){
					$response = $this->createBasicResponse(7, 'code_is_not_valid_2');
					return response()->json(['resource' => $response]);
				}
				$tariff = \App\Models\Tariff::with('country')->find($ifPhonenumberValid->tariff_id);
			}
			$apiToken = str_random(20);

			$countryCodeFromCallerId = \Cache::get('countryCodeOf_' . $phonenumber);
            if (config('app.SHOULD_USE_GEOIP')) {
                $countryCode = strtolower(trim(@geoip_country_code_by_name($ip)));
            } elseif ($countryCodeFromCallerId) {
            	$countryCode = $countryCodeFromCallerId;
            } else {
                $countryCode = null;
            }

            \Cache::forget('countryCodeOf_' . $phonenumber);
			$user->password = bcrypt($password);
			$user->country_code = $countryCode;
			$user->personal_name = $userName;
			$user->company_name = $companyName;
			$user->is_active = true;
			$user->send_newsletter = $sendNewsletter;

			$amount = 0;
			if($tariff && $tariff->country){
				$amount = $tariff->country->web_welcome_credit;
			}
			$bonusWithOtherUser = User::where('caller_id_used_for_wlc_credit', $phonenumber)->first();
			if ($amount && !$bonusWithOtherUser) {
				$user->balance = $amount;
				$user->gift_amount = $amount;
				$user->first_time_bonus = $amount;
				$user->caller_id_used_for_wlc_credit = $phonenumber;
				$minimumMargin = 0;

				$countryCode = $tariff ? $tariff->country->code : 'N/A';
				$invoiceRepo = new \App\Services\InvoiceService();
				$invoice = $invoiceRepo->createGiftInvoice($user, $amount, $countryCode, $minimumMargin);

				$this->sendEmailRepo->giftAdded($user, $amount);

				$logData = [
					'user_id' => $user->_id,
					'device' => 'CALLBURN',
					'action' => 'BILLINGS',
					'description' => 'Welcome gift added to user as first caller id added'
				];
				$this->activityLogRepo->createActivityLog($logData);
			}

			$timezone = InfoService::getTimezoneName($request);

			if ($timezone) {
                $user->timezone = $timezone;
            }

            $countryCode = InfoService::getCountryCode($request);
            if($countryCode) {
                $user->country_code = $countryCode;
            }

			$user->save();

	        $credentials = $request->only('email', 'password');
	        $jwtToken = JWTAuth::attempt($credentials);

			\Auth::login($user);

			$token = new \App\Models\ApiToken();
	        $token->user_id = $user->_id;
	        $token->api_token = str_random(10);
	        $token->ip_address = $ip;
	        $token->agent = '';
	        $token->device = 'WEBSITE';
	        $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
	        $token->session_id = \Session::getId();
	        $token->save();
	        \DB::commit();

            event(new \App\Events\UserDataUpdated( [
                'user_id' => $user->_id] ));

	    } catch(\Exception $e) {
	    	\Log::info($e);
	    	\DB::rollback();
	    	$response = [
				'error' => [
					'no' => -100,
					'text' => 'something_went_wrong'
				],
				'message' => $e->getMessage()
			];
			return response()->json(['resource' => $response]);
	    }
		SlackNotificationService::notify('User activated account with email - ' . $user->email);

		$response = [
			'error' => [
				'no' => 0,
				'text' => 'account_activated'
			],
			'user_data' => $user,
            'jwtToken' => $jwtToken,
		];
		return response()->json(['resource' => $response]);
    }

    /**
     * Check if verification token is right or not
     * GET /auth/check-token-validation
     *
     * @param Request $request
     * @return JSON
     */

    
	/**
     * Try to login the user
     * POST /auth/login
     *
     * @param Request $request
     * @return JSON
     */
    public function postLogin(Request $request)
    {
    	// dd($request->localDateFormat);
    	// $user = Auth::user();
        $ip = $request->ip();
        // $language = \App\Models\Language::where('code', $request->get('language','en'))->first();
        $agent = new \Jenssegers\Agent\Agent();
        if($agent->isDesktop()){
            $browser = $agent->browser();
            $platform = $agent->platform();
            $httpAgent = $browser . ' on ' . $platform;
        } else{
            $httpAgent = $agent->device();
        }
        $email = $request->get('email');
        $password = $request->get('password');

        $tempUser = \App\User::where('email', $email)->first();

        $localDateFormat = $request->localDateFormat;

        if(!$tempUser){
            $response = $this->createBasicResponse(-1, 'account_not_active_or_suspended');
            return response()->json(['resource' => $response]);
        }

        if(!$tempUser->is_active){
            $response = $this->createBasicResponse(-1, 'account_not_active_or_suspended');
            return response()->json(['resource' => $response]);
        }



        $credentials = $request->only('email', 'password');
        $jwtToken = JWTAuth::attempt($credentials, ['is_active' => 1, 'is_deleted' => 0]);

        if($tempUser->is_deleted) {

            return response()->json([

                'error' => [
                    'no' => -70,
                    'text' => trans('main.crud.we_are_sorry_your_account_was_blocked_contact_us_for_further_assistance'),
                ]
            ], 422);
        }

        //if (Auth::attempt(['email' => $email, 'password' => $password], 1)) {
        if ($jwtToken) {

        	$user = Auth::user();

            // $user->language_id = $language->_id;
            $countryCode = InfoService::getCountryCode($request);
            if($countryCode && !$user->country_code) {
                $user->country_code = $countryCode;
            }

	        $user->local_date_format = $localDateFormat;
            $user->save();

            $token = new \App\Models\ApiToken();
            $token->user_id = $user->_id;
            $token->api_token = str_random(10);
            $token->ip_address = $ip;
            $token->agent = $httpAgent;
            $token->device = 'WEBSITE';
            $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
            $token->session_id = \Session::getId();
            $token->save();
            
            $response = [
            	'error' => [
            		'no' => 0,
            		'text' => 'logged_in'
            	],
            	'user_data' => $user,
                'jwtToken' => $jwtToken,
                
            ];


        } else{
            $response = $this->createBasicResponse(-1, 'invalid_credentials');
        }
        return response()->json(['resource' => $response]);
    }



    /**
     * Logout the user
     * GET /auth/logout
     *
     * @return JSON
     */
    public function getLogout()
    {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);

    	$sessionId = \Session::getId();
    	\App\Models\ApiToken::where('session_id', $sessionId)->delete();
    	Auth::logout();
        $response = $this->createBasicResponse(0, 'logged_out');
    	return response()->json(['resource' => $response]);
    }

    /**
     * Send request to API for sending password reset link to the user with given email.
     * POST /auth/send-reset-link
     *
     * @param Request $request
     * @return JSON
     */
    public function postSendResetLink(Request $request, UserService $userRepo)
    {
    	$emailAddress = $request->get('email');

        $user = \App\User::whereEmail($emailAddress)->first();

        // if($user and $user->password) {

        //     $response = $this->createBasicResponse(-5, 'access_denied');
        //     return response()->json(['resource' => $response]);

        // }

    	$validator = Validator::make(
		    [
		        'email' => $emailAddress,
		    ],
		    [
		        'email' => 'required|exists:users',
		    ]
		);
		if ($validator->fails())
		{
			$failedRules = $validator->failed();
			$errorNumber = -100;
			$errorMessage = 'Ssomething__went__wrong';
			if(isset($failedRules['email']['Required'])){
				$errorNumber = -1;
				$errorMessage = 'email_can_not_be_blank';
			} elseif(isset($failedRules['email']['Exists'])){
				$errorNumber = -2;
				$errorMessage = 'user_with_this_email_does__not_exists';
			}
		} else{
			$token = str_random(20);
			$user = $userRepo->getUserByEmail($emailAddress);
			$updateData = ['password_reset' => $token];
			$userRepo->updateUser($user->_id, $updateData);

			$this->sendEmailRepo->sendPasswordResetNotificationEmail($user, $token);
			
			//$userRepo->sendPasswordResetNotification($to, $variables);

			$errorNumber = 0;
			$errorMessage = 'password_reset_code_sent';

			$logData = [
				'user_id' => $user->_id,
				'device' => 'WEBSITE',
				'action' => 'REGISTRATION-LOGIN',
				'description' => 'User ordered password reset link with token - ' . $token
			];
			$this->activityLogRepo->createActivityLog($logData);
		}
		$response = $this->createBasicResponse($errorNumber, $errorMessage);
		return response()->json(['resource' => $response]);
    }

    /**
     * Send request to server for resetting password.
     * POST /auth/make-reset-password
     *
     * @param Request $request
     * @return JSON
     */
    public function postMakeResetPassword(Request $request, UserService $userRepo)
    {
    	$errorNumber = -100;
        $language = \App\Models\Language::where('code',$request->get('language','en'))->first();
		$errorMessage = 'something__went__wrong';
    	$token = $request->get('token');
    	$password = $request->get('password');
    	$passwordConfirmation = $request->get('password_confirmation');
    	$validator = Validator::make(
		    [
		        'password' => $password,
		        'password_confirmation' => $passwordConfirmation,
		        'token' => $token,
		    ],
		    [
		        'password' => 'required|confirmed|min:4|max:20',
		        'token' => 'required'
		    ]
		);
		if ($validator->fails())
		{
			$failedRules = $validator->failed();
			if(isset($failedRules['password']['Required'])){
				$errorNumber = -1;
				$errorMessage = trans('main.crud.password_can_not_be_blank');
			} elseif(isset($failedRules['password']['Confirmed'])){
				$errorNumber = -2;
				$errorMessage = trans('main.crud.passwords_do_not_match');
			} elseif(isset($failedRules['password']['Min'])){
				$errorNumber = -3;
				$errorMessage = trans('main.crud.password_is_too_short');
			} elseif(isset($failedRules['password']['Max'])){
				$errorNumber = -4;
				$errorMessage = trans('main.crud.password_is_too_long');
			} elseif(isset($failedRules['password']['AlphaDash'])){
				$errorNumber = -5;
				$errorMessage = trans('main.crud.password_criteria_not_fullfilled');
			} elseif(isset($failedRules['token']['Required'])){
				$errorNumber = -6;
				$errorMessage = 'token_is_missing';
                abort('404');
			}
		} else{
			$user = $userRepo->getUserByPasswordToken($token);
			if($user){


                $user->language_id = $language->_id;
                $user->save();
				$updateData = ['password' => bcrypt($password), 'password_reset' => null];
				$userRepo->updateUser($user->_id, $updateData);
				$errorNumber = 0;
				$errorMessage = 'password__changed_1';

				$user->apiTokens()->delete();
				$token = str_random(20);
				\App\Models\ApiToken::create([
					'user_id' => $user->_id,
					'api_token' => $token
					]);

				$logData = [
					'user_id' => $user->_id,
					'device' => 'WEBSITE',
					'action' => 'REGISTRATION-LOGIN',
					'description' => 'User updated his password'
				];
				$this->activityLogRepo->createActivityLog($logData);


                $credentials = [
                    'email'    => $user->email,
                    'password' => $password
                ];
                $jwtToken = JWTAuth::attempt($credentials);


                //\Auth::login($user);

				$response = $this->createBasicResponse($errorNumber, $errorMessage);
				$response['api_token'] = $token;
				$response['jwtToken'] = $jwtToken;

				return response()->json(['resource' => $response]);
			} else{
				$errorNumber = -7;
				$errorMessage = 'invalid_token';
			}
		}
		$response = $this->createBasicResponse($errorNumber, $errorMessage);
		return response()->json(['resource' => $response]);
    }

    /**
     * Validate new email address
     * GET /auth/confirm-email-address
     *
     * @param string $token
     * @return JSON
     */
    public function getConfirmEmailAddress($token)
    {
    	$emailConfirmationToken = $token;
		$user = \App\User::where('email_confirmation_token', $emailConfirmationToken)->first();
		if(!$user){
			$response = $this->createBasicResponse(-1, 'invalid_token');
			return response()->json(['resource' => $response]);
		}
		$user->email = $user->new_email;
		$user->new_email = NULL;
		$user->email_confirmation_token = NULL;
		$user->save();
		$this->sendEmailRepo->sendChangeEmailNotificationEmail($user);
		return redirect('/#/home-page?is_email_changed=true');
    }

    /**
     * Show hint for username recovery
     * POST /auth/recover-username
     *
     * @param Request $request
     * @return JSON
     */
    public function postRecoverUsername(Request $request)
    {
    	$phonenumber = $request->get('phonenumber');
    	$code = $request->get('code');
    	$numberVerfiication = \App\Models\NumberVerification::where('phone_number', $phonenumber)
    		->where('code', $code)->first();
    	if(!$numberVerfiication){
    		$response = $this->createBasicResponse(-1, 'Invalid_code');
    		return response()->json(['resource' => $response]);
    	}
    	$callerId = \App\Models\CallerId::where('phone_number', $phonenumber)->with('user')->first();
    	if(!$callerId){
    		$response = $this->createBasicResponse(-2, 'this_caller_id_is_not_registered');
    		return response()->json(['resource' => $response]);
    	}
    	$user = $callerId->user;
    	$numberVerfiication->delete();
    	$response = [
    		'error' => [
    			'no' => 0,
    			'text' => 'username_1'
    		],
    		'username' => $user->email
    	];
    	return response()->json(['resource' => $response]);
    }

    /**
     * Check phonenumber for logging with TOTP
     * POST /auth/check-totp-number
     *
     * @param Request $request
     * @return JSON
     */
    public function postCheckTotpNumber(Request $request)
    {
    	$phonenumber = $request->get('phonenumber');
    	$callerId = \App\Models\CallerId::where('phone_number', $phonenumber)
    		->with('user')->first();
    	if(!$callerId || !$callerId->user){
    		$response = $this->createBasicResponse(-1, 'phonenumber_not_exists');
    		return response()->json(['resource' => $response]);
    	}
    	if(!$callerId->user->totp_token){
    		$response = $this->createBasicResponse(-2, 'account_is_not_synced_with_application');
    		return response()->json(['resource' => $response]);
    	}
    	$response = [
    		'error' => [
    			'no' => 0,
    			'text' => 'valid'
     		],
    		'user_id' => $callerId->user->_id
    	];
    	return response()->json(['resource' => $response]);
    }

    /**
     * Login user with totp token
     * POST /auth/login-with-totp
     *
     * @param Request $request
     * @return JSON 
     */
    public function postLoginWithTotp(Request $request)
    {
    	$ip = $request->ip();
    	$userId = $request->get('user_id');
    	$totpCode = $request->get('code');
    	$user = \App\User::find($userId);
    	if(!$user){
    		$response = $this->createBasicResponse(-1, 'Invalid__user');
    		return response()->json(['resource' => $response]);
    	}
    	$totpService = new \App\Services\TOTPService();
    	$totpRealCode = $totpService->generateTimebased ($user->totp_token, 6);
    	if($totpCode != $totpRealCode){
    		$response = $this->createBasicResponse(-2, 'Invalid_code');
    		return response()->json(['resource' => $response]);
    	}
    	Auth::login($user);

		$token = new \App\Models\ApiToken();
        $token->user_id = $user->_id;
        $token->api_token = str_random(10);
        $token->ip_address = $ip;
        $token->agent = '';
        $token->device = 'WEBSITE';
        $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
        $token->session_id = \Session::getId();
        $token->save();

		$response = [
			'error' => [
				'no' => 0,
				'text' => 'account_activated'
			],
			'user_data' => $user
		];
		return response()->json(['resource' => $response]);
    }

    /**
	 * Check if phonenumber and code valid and add to user
	 *
	 * @param string $phoneNumber
	 * @param string $code
	 * @param User $newUser
	 * @return bool
	 */
	private function checkPhonenumberCodeAndAddToAccount($phoneNumber, $code, $newUser)
	{
		$callerIdsModel = new \App\Models\CallerId();
		$numVerificationRepo = new \App\Services\NumberVerificationService();
		$numberField = $numVerificationRepo->getNumberVerification($code, $phoneNumber);
		if( $numberField ){
			$data = [
				'user_id' => $newUser->_id,
				'phone_number' => $phoneNumber,
				'is_verified' => 1,
				'tariff_id' => $numberField->tariff_id
			];
			$callerIdsWithOldOwner = $callerIdsModel->where('phone_number', $phoneNumber)->with('user')->first();
			if($callerIdsWithOldOwner && $callerIdsWithOldOwner->user){
                //$callerIdsWithOldOwner->snippet()->update(['is_blocked' => 1]);
                //$callerIdsWithOldOwner->snippet()->detach();
				$oldUser = $callerIdsWithOldOwner->user;

				if($oldUser) {
					$callerIdRepo = new \App\Services\CallerIdsService();
					$callerIdRepo->handleRemovingCallerIdFromUser($oldUser, $callerIdsWithOldOwner, true);
				}

				// If the caller id is registered to another user
				// and that user does not have any other caller id
				// and the user has not synchronized account with website
				// we are adding that users balance to new user
				// and removing the old user , because that account will be unaccessible 
				
				//NOTE THIS CODE NEEDED FOR MOBILE APPLICATION USERS
				//AS WE DON"T HAVE IT NOW WE ARE COMMENTING THIS PART
				$callerIdsWithOldOwner->update($data);
				// if($oldUser->numbers()->count() == 1 && !$oldUser->email && !$oldUser->password){
				// 	$newUser->balance = $newUser->balance + $oldUser->balance;
				// 	$oldUser->balance = 0;
				// 	$oldUser->is_deleted = 1;
				// 	$oldUser->caller_id_country_code = NULL;
				// 	$oldUser->deleted_at = Carbon::now();
				// 	$oldUser->save();
				//}
			} else{
				$callerIdsModel->create($data);
			}
			$numVerificationRepo->removeNumberVerification($numberField->_id);
			return $numberField;
		}
		return false;
	}

	public function RefreshToken() {

        $token = \JWTAuth::getToken();
        $token = \JWTAuth::refresh($token);

        if($token) {
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => ''
                ],
                'token' => $token

            ];
        } else {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => ''
                ],


            ];
        }



        return response()->json(['resource' => $response]);

    }

}