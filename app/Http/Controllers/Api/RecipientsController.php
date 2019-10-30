<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController as Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecipientsController extends Controller {

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
		$this->device = 'API';
		$this->activityLogRepo = new \App\Services\ActivityLogService();
	}


	/**
	 * Get receipents of the message
	 * GET /v1/api/recipients
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function getIndex(Request $request)
	{
		$apiKey = $request->get('key');
		$messageId = $request->get('message_id');
		$checkedKey = $this->checkKey($apiKey);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];

		$phonenumbers = $user->phonenumbers()
			->select(['phone_no', 'call_status', 'retries', 'duration', 'dialled_datetime', 'cost']);
		if($messageId){
			$phonenumbers = $phonenumbers->where('campaign_id', $messageId);
		}
		$phonenumbers = $phonenumbers->get();
		
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Recipients'
			],
			'recipients' => $phonenumbers
		];
		return response()->json($response);

	}
}