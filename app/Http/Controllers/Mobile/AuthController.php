<?php namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Mobile\MobileController as Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use Carbon\Carbon;
use Validator;

/**
 * This class is responsible for handling requests
 * from android and ios applications .
 */
class AuthController extends Controller {

	/**
	 * The current device.
	 *
	 * @var string
	 */
	private $device;

	/**
	 * Object of ActivityLogService
	 *
	 * @var App\Services\ActivityLogService
	 */
	private $activityLogRepo;

	/**
	 * Create a new instance of AuthApiController class.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function __construct(
		Request $request
		)
	{
		$this->device = $request->get('device', 'WEBSITE');
		$this->activityLogRepo = new \App\Services\ActivityLogService();
	}


	/**
	 * Log in the user, giving auth token . At the moment user will use
	 * this token only for syncing contacts
	 *
	 * POST /v1/mobile/auth/log-in
	 * @param Request $request
	 * @return JSON
	 */
	public function postLogIn(Request $request, UserService $userRepo)
	{
		$email = $request->get('email_address');
		$password = $request->get('password');
		if(!$email || !$password) {
			$response = $this->createBasicResponse(-1, 'Email and password required');
			return response()->json($response);
		}
		$websiteUser = $userRepo->getUserByEmail($email);
		if(!$websiteUser || !\Hash::check($password, $websiteUser->password))
		{
		    $response = $this->createBasicResponse(-2, 'Invalid credentials');
		    return response()->json($response);
		}
		$apiToken = str_random(20);
		$websiteUser->mobile_api_token =  $apiToken;
		$websiteUser->save();
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Successfully logged in'
			],
			'api_key' => $apiToken,
		];
		return response()->json($response); 
	}


	/**
	 * Generate a number and make a call to user to verify it.
	 * POST /v1/mobile/auth/registration-of-number
	 * 
	 * @param Request $request
	 * @param NumberVerificationService $numberRepo
	 * @param CampaignDbService $campaignDbRepo
	 * @return JSON
	 */
	// public function postRegistrationOfNumber(
	// 	Request $request,
	// 	\App\Services\NumberVerificationService $numVerificationRepo,
	// 	\App\Services\CampaignDbService $campaignDbRepo,
	// 	\App\Services\TariffService $tariffRepo
	// )
	// {
	// 	$errorNumber = -100;
	// 	$errorMessage = 'Something Went Wrong';

	// 	$phonenumber = $request->get('phonenumber'); 
	// 	$validationResponse = $campaignDbRepo->isValidNumber($phonenumber);
	// 	if(!$validationResponse['finalNumber']){
	// 		if($validationResponse['reason'] == 'country'){
	// 			$errorNumber = -2;
	// 			$errorMessage = 'Callburn service is still not available on your country, please, retry in sometime or fill a request on www.callburn.com';
	// 		} else{
	// 			$errorNumber = -1;
	// 			$errorMessage = 'Invalid phonenumber';
	// 		}
	// 	} else{
	// 		$tariff = $validationResponse['detectedTariff'];
	// 		$phonenumber = $validationResponse['finalNumber'];

	// 		$phonenumberObj = new \stdClass;
	// 		$phonenumberObj->tariff = $tariff;
	// 		$isp = $tariffRepo->detectIsp($phonenumberObj);
	// 		if(!$isp){
	// 			$errorNumber = -2;
	// 			$errorMessage = 'Callburn service is still not available on your country, please, retry in sometime or fill a request on www.callburn.com';
	// 		} else{
	// 			$phonenumberData = [
	// 				'phone_no' => $phonenumber,
	// 				'aserver_id' => NULL,
	// 				'isp_id' => $isp->_id,
	// 				'tariff_id' => $tariff->_id,
	// 				'cost' => 0,
	// 				'action_type' => 'VERIFICATION_CALL'
	// 			];
	// 			$finalPhonenumber = \App\Models\Phonenumber::create($phonenumberData);

	// 			$response = [
	// 				'error' => [
	// 					'no' => 0,
	// 					'text' => 'System Is making verification call'
	// 				],
	// 				'phonenumber' => $phonenumber
	// 			];
	// 			return response()->json($response);
	// 		}
	// 	}
	// 	$response = [
	// 		'error' => [
	// 			'no' => $errorNumber,
	// 			'text' => $errorMessage
	// 		]
	// 	];
	// 	return response()->json($response);
	// }



	/**
	 * Send request for registering new account
	 * POST /v1/mobile/auth/sign-up
	 *
	 * @param Request $request
	 * @param NumberVerificationService $numVerificationRepo
	 * @param UserService $userRepo
	 * @param CallerId $callerIdsModel
	 * @return JSON
	 */
	// public function postSignUp(
	// 	Request $request,
	// 	\App\Services\NumberVerificationService $numVerificationRepo,
	// 	\App\Services\UserService $userRepo,
	// 	\App\Models\CallerId $callerIdsModel
	// 	)
	// {
	// 	$phoneNumber = $request->get('phonenumber');
	// 	$code = $request->get('voice_code');
	// 	$totpToken = str_random(20);
	// 	$validator = Validator::make(
	// 	    [
	// 	        'code' => $code,
	// 	        'phone_number' => $phoneNumber
	// 	    ],
	// 	    [
	// 	        'code' => 'required',
	// 	        'phone_number' => 'required'
	// 	    ]
	// 	);
	// 	if ($validator->fails()){
	// 		$response = $this->createBasicResponse(-1, 'Phonenumber and code is required');
	// 		return response()->json($response);
	// 	}
	// 	$numberField = $numVerificationRepo->getNumberVerification($code, $phoneNumber);
	// 	if( !$numberField ){
	// 		$response = $this->createBasicResponse(-2, 'Phonenumber verification failed');
	// 		return response()->json($response);
	// 	}
	// 	$userData = [
	// 		'last_ip' => $request->get('last_ip')
	// 	];
	// 	$newUser = $userRepo->createUser($userData);
		
	// 	$data = [
	// 		'user_id' => $newUser->_id,
	// 		'phone_number' => $phoneNumber,
	// 		'is_verified' => 1,
	// 		'tariff_id' => $numberField->tariff_id
	// 	];

	// 	$callerIdsWithOldOwner = $callerIdsModel->where('phone_number', $phoneNumber)->with('user')->first();
	// 	//Check if this caller id is already registered on another user
	// 	if($callerIdsWithOldOwner){
	// 		$oldUser = $callerIdsWithOldOwner->user;
	// 		// If the caller id is registered to another user
	// 		// and that user does not have any other caller id
	// 		// and the user has not synchronized account with website
	// 		// we are adding that users balance to new user
	// 		// and removing the old user , because that account will be unaccessible 
	// 		$callerIdsWithOldOwner->update($data);
	// 		if($oldUser->numbers()->count() == 0 && !$oldUser->email && !$oldUser->password){
	// 			$newUser->balance = $newUser->balance + $oldUser->balance;
	// 			$oldUser->balance = 0;
	// 			$oldUser->is_deleted = 1;
	// 			$oldUser->caller_id_country_code = NULL;
	// 			$oldUser->deleted_at = Carbon::now();
	// 			$oldUser->save();
	// 		}
	// 	} else{
	// 		$callerIdsModel->create($data);
	// 	}
	// 	$numVerificationRepo->removeNumberVerification($numberField->_id);
		
	// 	$apiToken = str_random(20);
	// 	$newUser->mobile_api_token =  $apiToken;
	// 	$newUser->registered_from = $this->device;
	// 	$newUser->totp_token = $totpToken;
	// 	$newUser->save();
	// 	$newUser = $userRepo->getUserByMobileApiToken($apiToken);


	// 	$logData = [
	// 		'user_id' => $newUser->_id,
	// 		'device' => strtoupper($this->device),
	// 		'action' => 'REGISTRATION-LOGIN',
	// 		'description' => 'User has been registered using caller id - ' .  $phoneNumber
	// 	];
	// 	$this->activityLogRepo->createActivityLog($logData);
		
	// 	$response = [
	// 		'error' => [
	// 			'no' => 0,
	// 			'text' => 'Successfully registered'
	// 		],
	// 		'user_data' => $newUser,
	// 		'api_key' => $apiToken,
	// 		'totp_token' => $totpToken
	// 	];
	// 	return response()->json($response); 
	// }


	/**
	 * Sync mobile app with website.
	 * POST /v1/mobile/auth/sync-with-website
	 *
	 * @param Request $request
	 * @param UserService $userRepo
	 * @return JSON
	 */
	// public function postSyncWithWebsite(
	// 	Request $request,
	// 	UserService $userRepo
	// 	)
	// {
	// 	$key = $request->get('key');
	// 	$checkedKey = $this->checkKey($key);
	// 	if(!$checkedKey){
	// 		$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
	// 		return response()->json($response);
	// 	}
	// 	$mobileApplicationUser = $checkedKey['user'];

	// 	$email = $request->get('email');
	// 	$password = $request->get('password');

	// 	$websiteUser = $userRepo->getUserByEmail($email);
	// 	if(!$websiteUser || !\Hash::check($password, $websiteUser->password))
	// 	{
	// 	    $response = $this->createBasicResponse(-1, 'Invalid credentials');
	// 	    return response()->json($response);
	// 	}

	// 	$websiteUser->balance = $websiteUser->balance + $mobileApplicationUser->balance;
	// 	$websiteUser->mobile_api_token = $mobileApplicationUser->mobile_api_token;

	// 	$mobileApplicationUser->campaigns()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->phonenumbers()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->invoices()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->coupons()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->files()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->number_files()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->recharges()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->tempBillings()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);
	// 	$mobileApplicationUser->numbers()->where('user_id', $mobileApplicationUser->_id)
	// 		->update(['user_id' => $websiteUser->_id]);

	// 	$mobileApplicationUser->delete();
	// 	$websiteUser->save();


	// 	$logData = [
	// 		'user_id' => $user->_id,
	// 		'device' => strtoupper($this->device),
	// 		'action' => 'ACCOUNT',
	// 		'description' => 'User has linked his device with website'
	// 	];
	// 	$this->activityLogRepo->createActivityLog($logData);

	// 	$response = [
	// 		'error' => [
	// 			'no' => 0,
	// 			'text' => 'Accounts are synchronized'
	// 		]
	// 	];
	// 	return response()->json($response);
	// }

}