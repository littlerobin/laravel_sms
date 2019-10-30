<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helper;
use Validator;

class MessagesController extends ApiController{

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
	 * Create a new campaign
	 * POST /v1/api/messages
	 *
	 * @param Request $request
	 * @param CampaignService $campaignRepo
	 * @return response
	 */
	public function postCreateMessage(
		Request $request,
		\App\Services\CampaignService $campaignRepo,
		\App\Services\FileService $fileRepo,
		\App\Services\CampaignDbService $campaignDbRepo
		)
	{
		$apiKey = $request->header('Authorization');
		//$apiKey = $request->get('key');
		$checkedKey = $this->checkKey($apiKey);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey->user;

		$name = $request->get('name');
		$callerId = ltrim($request->get('caller_id'), '+');
		$getEmailNotifications = $request->get('get_email_notifications', false);
		$phonenumbers = explode(',', $request->get('recipients', ''));
		$finalPhonenumbers = $campaignDbRepo->getValidPhonenumbers($phonenumbers, $user);
		if($finalPhonenumbers->count() == 0){
			$response = $this->createBasicResponse(-6, 'There is no valid phonenumber');
			return response()->json($response);
		}

		$usersCallerId = $user->numbers()->where('phone_number', $callerId)->first();
		if(!$usersCallerId){
			$response = $this->createBasicResponse(-11, 'Caller id is not registered for the user.');
			return response()->json($response);
		}

		$audioText = $request->get('audio_text');
		$audioLang = $request->get('audio_lang');
		
		if(!$audioText || !$audioLang){
			$response = $this->createBasicResponse(-2, 'TTS data missing');
			return response()->json($response);
		} else{
			$voiceFileResponse = $fileRepo->createFromText($audioText, $audioLang, $user->_id);
			if(!$voiceFileResponse->file){
				$response = $this->createBasicResponse(-3, $voiceFileResponse->error);
				return response()->json($response);
			}
			$voiceFileId = $voiceFileResponse->file->_id;
		}
		$randomBatchString = str_random(20);
		$campaignData = [
			'campaign_name' => $name,
			'caller_id' => $callerId,
			'campaign_voice_file_id' => $voiceFileId,
			//'retries' => 3,
			'user_id' => $user->_id,
			'type' => 'VOICE_MESSAGE',
			'status' => 'start',
			'get_email_notifications' => $getEmailNotifications,
			'repeat_batch_grouping' => $randomBatchString,
			'created_from' => 'API',
			'api_key_id' => $checkedKey['_id'],
			'is_first_run' => 0
		];
		$campaign = $campaignRepo->createCampaign($campaignData);
		
		$phonenumbersToadd = [];
		foreach ($finalPhonenumbers as $phonenumber) {
			$tariff = $phonenumber['tariff'];
			$finalNumber = $phonenumber['phonenumber'];

			$isUserNumberFromEu = $usersCallerId->tariff->country->is_eu_member;
			$isNumberFromEu = $tariff->country->is_eu_member;
			$isFromNotEuToEu = !$isUserNumberFromEu && $isNumberFromEu;

			$phonenumbersToadd[] = [
				'campaign_id' => $campaign->_id,
				'phone_no' => $finalNumber,
				'created_at' => date('Y-m-d H:i:s'),
				'retries' => 0,
				'tariff_id' => $tariff->_id,
				'user_id' => $user->_id,
				'is_from_not_eu_to_eu' => $isFromNotEuToEu
			];
		}

		
		if(count($phonenumbersToadd) > 0){
			\App\Models\Phonenumber::insert($phonenumbersToadd);
		} 

		// $logData = [
		// 	'campaign_id' => $campaign->_id,
		// 	'user_id' => $user->_id,
		// 	'device' => strtoupper($this->device),
		// 	'action' => 'MESSAGES',
		// 	'description' => 'User created Message'
		// ];
		// $this->activityLogRepo->createActivityLog($logData);

		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Message created'
			],
			'message_id' => $campaign->_id
		];
		return response()->json($response);
	}

	/**
     * Returns the collection of messages of the user.
     * GET /v1/api/messages
     *
     * @param Request $request
     * @return JSON
     */
    public function getIndex()
    {
        //$key = $request->get('key');
		$apiKey = $request->header('Authorization');
        $page = $request->get('page', 0);
		$perPage = $request->get('per_page', 10);

		$checkedKey = $this->checkKey($apiKey);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey->user;
		$campaigns = $checkedKey->campaigns()->where('is_prototype', 0);
		$count = $campaigns->count(\DB::raw('DISTINCT repeat_batch_grouping'));

		$campaigns = $campaigns->groupBy('repeat_batch_grouping')
			->selectRaw('_id, campaign_name as name, caller_id, status, created_at')
			->skip($page * $perPage)->take($perPage)->get();
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Messages of the user'
			],
			'messages' => $campaigns,
			'count' => $count
		];
		return response()->json($response);
    }

    /**
     * Get data of specific message using its id
     * GET /v1/mobile/messages/{id}
     *
     * @param Request $request
     * @return JSON
     */
    public function getShow($id, Request $request)
    {
    	//$key = $request->get('key');
		$apiKey = $request->header('Authorization');
    	$checkedKey = $this->checkKey($apiKey);
    	if(!$checkedKey){
    		$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
    		return response()->json($response);
    	}
    	$user = $checkedKey->user;

    	$message = $checkedKey->campaigns()
			->selectRaw('_id, campaign_name as name, campaign_voice_file_id, caller_id, status, created_at')
			->find($id);
		if(!$message){
			$response = $this->createBasicResponse(-1, 'Message does not exists or not belongs to you');
    		return response()->json($response);
		}

		$finalMessageObject = clone $message;
		$finalMessageObject->success_phonenumbers_count = $message->successPhonenumbersCount;
		$finalMessageObject->unfinished_phonenumbers_count = $message->unfinishedPhonenumbersCount;
		$finalMessageObject->total_phonenumbers_count = $message->totalPhonenumbersCount;
		$finalMessageObject->voice_file_text = $message->voiceFileText;
		$finalMessageObject->voice_file_language = $message->voiceFileLanguage;
		$finalMessageObject->recipients = $message->phonenumbersForApi;

		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Message data'
			],
			'message' => $finalMessageObject
		];
		return response()->json($response);
    }


	/**
	 * Remove message. Can remove only messages which status is scheduled or draft
	 * DELETE /v1/api/messages/remove/{id}
	 *
	 * @return JSON
	 */
	public function deleteRemove($id)
	{
		$apiKey = $request->header('Authorization');
		//$apiKey = $request->get('key');
		$checkedKey = $this->checkKey($apiKey);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey->user;

		$message = $checkedKey->campaigns()->find($id);
		if(!$message){
			$response = $this->createBasicResponse(-1, 'Message is not exists or not belongs to you');
			return response()->json($response);
		}
		if(!in_array($message->status, ['scheduled', 'saved', 'dialing_completed'])){
			$response = $this->createBasicResponse(-2, 'You can not remove messages which are in progress!');
			return response()->json($response);
		}

		$message->delete();
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Message Removed'
			]
		];
		return response()->json($response);
	}

}