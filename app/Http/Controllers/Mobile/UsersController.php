<?php namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Mobile\MobileController as Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

/**
 * This class is responsible for all user related things
 * Getting users info
 * Updating users info
 */
class UsersController extends Controller {

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
	 * Create a new instance of AdderssBookApiController class.
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
	 * Get user data by api key
	 * GET /v1/mobile/users/get-user
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function getGetUser(Request $request)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		} else{
			$response = [
				'error' => [
					'no' => 0,
					'text' => 'User info'
				],
				'user_data' => $checkedKey['user']
			];
			return response()->json($response);
		}
	}

	/**
	 * Enable/disable push notifications
	 * POST /v1/mobile/users/enable-disable-push-notifications
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postEnableDisablePushNotifications(Request $request)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$mobileDeliveryNotifications = $request->get('mobile_delivery_notifications', 'OFF');
		$firebaseId = $request->get('firebase_id', NULL);
		if($mobileDeliveryNotifications != 'OFF' && !$firebaseId){
			$response = $this->createBasicResponse(-1, 'Customer id is mandatory');
			return response()->json($response);
		}
		$user->mobile_delivery_notifications = $mobileDeliveryNotifications;
		$user->firebase_id = $firebaseId;
		$user->save();
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Updated'
			]
		];
		return response()->json($response);
	}

	/**
	 * Enable/disable push notifications
	 * POST /v1/mobile/users/enable-disable-newsletter
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postEnableDisableNewsletter(Request $request)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$sendNewsletter = $request->get('send_newsletter');
		$user->send_newsletter = $sendNewsletter;
		$user->save();
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Updated'
			]
		];
		return response()->json($response);
	}

	/**
	 * Update users data
	 *
	 * POST /v1/mobile/users/update-user
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postUpdateUser(Request $request)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$allowedFieldsToUpdate = [];
		$updateFields = $request->all();

		$intersectedFields = array_intersect($updateFields, $allowedFieldsToUpdate);
		$user->update($intersectedFields);
		$updateDescription = '';
		foreach ($intersectedFields as $key => $value) {
			$updateDescription .= $key . ' => ' . $value . '<br>';
		}
		if($updateDescription){
			$updateDescription = 'User has updated his main data: <br>' . $updateDescription;
			$logData = [
				'user_id' => $user->_id,
				'device' => strtoupper($this->device),
				'action' => 'ACCOUNT',
				'description' => $updateDescription
			];
			$this->activityLogRepo->createActivityLog($logData);
		}
		$response = $this->createBasicResponse(0, 'Data updated');
		return response()->json($response);
	}
}