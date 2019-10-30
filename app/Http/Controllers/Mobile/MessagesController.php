<?php namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Mobile\MobileController as Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helper;
use Validator;

class MessagesController extends Controller{

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
	 * Create a new campaign
	 * POST /v1/mobile/messages/create-message
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
		$apiKey = $request->get('key');
		$checkedKey = $this->checkKey($apiKey);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$randomName = str_random(12);
		$callerId = ltrim($request->get('caller_id'), '+');
		$voiceFileId = $request->get('voice_message_id');
		$voiceFile = $user->files()->find($voiceFileId);
		if(!$voiceFile){
			$response = $this->createBasicResponse(-5, 'Voice file not exists or not belongs to you');
			return response()->json($response);
		}

		$name = $request->get('campaign_name');
		$status = $request->get('status');
		$getEmailNotifications = $request->get('get_email_notifications', false);
		$phonenumbers = $request->get('phonenumbers', []);
		$finalPhonenumbers = $campaignDbRepo->getValidPhonenumbers($phonenumbers, $user);
		if($finalPhonenumbers->count() == 0){
			$response = $this->createBasicResponse(-6, 'There is no valid phonenumber');
			return response()->json($response);
		}

		$liveAnswersOnly = $request->get('live_answers_only', 0);
		$liveTransferLimit = $request->get('live_transfer_limit', 0);
		$schedulations = json_decode( $request->get('schedulations'), 1 );
		if(!$schedulations){
			$schedulations = [];
		}
		$mobileMessageGroupId = $request->get('mobile_message_group_id');
		if(!$mobileMessageGroupId){
			$mobileMessageGroupName = $request->get('mobile_message_group_name', 'Campaign');
			$mobileMessageGroupImage = $request->get('mobile_message_group_image');
			$mobileMessageGroup = \App\Models\MobileMessageGroup::create([
					'name' => $mobileMessageGroupName,
					'image' => $mobileMessageGroupImage,
					'user_id' => $user->_id
				]);
			$mobileMessageGroupId = $mobileMessageGroup->_id;
		}

		$validatorData = $request->all();
		$validator = \Validator::make(
		    $validatorData,
		    [
		        'caller_id' => 'required',
		        'voice_message_id' => 'required',
		        'phonenumbers' => 'array',
		    ]
		);
		if($validator->fails()){
			$errorNumber = -100;
			$errorMessage = 'Something went wrong';
			$failedRules = $validator->failed();
			if(isset($failedRules['caller_id']['Required'])){
				$errorNumber = -1;
				$errorMessage = 'Caller Id is required';
			} elseif(isset($failedRules['voice_message_id']['Required'])){
				$errorNumber = -2;
				$errorMessage = 'voice file is required and should be mp3,wav or gsm';
			}elseif(isset($failedRules['phonenumbers']['Array'])){
				$errorNumber = -3;
				$errorMessage = 'phonenumbers is mandatory and should be array';
			}
			$response = $this->createBasicResponse($errorNumber, $errorMessage);
			return response()->json($response);
		}

		$usersCallerId = $user->numbers()->where('phone_number', $callerId)->first();
		if(!$usersCallerId){
			$response = $this->createBasicResponse(-11, 'Caller id is not registered for the user.');
			return response()->json($response);
		}

		$randomBatchString = str_random(20);
		$campaignStatus = $status ? $status : 'start';

		$campaignData = [
			'campaign_name' => $name,
			'caller_id' => $callerId,
			'campaign_voice_file_id' => $voiceFileId,
			'live_answer_only' => 0,
			'retries' => 5,
			'live_transfer_limit' => 0,
			'total_phonenumbers_loaded' => 0,
			'user_id' => $user->_id,
			'type' => 'VOICE_MESSAGE',
			'max_concurrent_channels' => 10,
			'status' => $campaignStatus,
			'get_email_notifications' => $getEmailNotifications,
			'repeat_batch_grouping' => $randomBatchString,
			'created_from' => 'MOBILE',
			'mobile_message_group_id' => $mobileMessageGroupId
		];
		$campaign = $campaignRepo->createCampaign($campaignData);

		$cacheService = new \App\Services\Cache\UserDataRedisCacheService();
		$cacheService->incrementMessages($campaign->user_id, 1);
		
		$phonenumbersToadd = [];
		foreach ($finalPhonenumbers as $phonenumber) {
			$tariff = $phonenumber['tariff'];
			$finalNumber = $phonenumber['phonenumber'];

			$isUserNumberFromEu = $usersCallerId->tariff->country->is_eu_member;
			$isNumberFromEu = $tariff->country->is_eu_member;
			$isFromNotEuToEu = !$isUserNumberFromEu && $isNumberFromEu;

			$shouldBeFree = $this->shouldPhonenumberBeFree($tariff, $usersCallerId, $user);
			$phonenumbersToadd[] = [
				'campaign_id' => $campaign->_id,
				'phone_no' => $finalNumber ,
				'created_at' => date('Y-m-d H:i:s'),
				'retries' => 0,
				'tariff_id' => $tariff->_id,
				'action_type' => 'VOICE_MESSAGE',
				'user_id' => $user->_id,
				'is_free' => $shouldBeFree,
				'is_from_not_eu_to_eu' => $isFromNotEuToEu
			];
		}

		
		$schedulationsToAdd = [];
		foreach ($schedulations as $schedulation) {
			if(!isset($schedulation['date']) || !isset($schedulation['max']) ){
				continue;
			}
			$schedData = [
				'campaign_id' => $campaign->_id,
				'scheduled_date' => $schedulation['date'],
				'calls_limit' => $schedulation['max']
			];
			if( isset($schedulation['sending_time']) && $schedulation['sending_time'] ){
				$schedData['calling_interval_minutes'] = round( $schedulation['sending_time'] / $schedulation['max'] );
			}
			$schedulationsToAdd[] = $schedData;
		}

		
		if(count($phonenumbersToadd) > 0){
			\App\Models\Phonenumber::insert($phonenumbersToadd);
		}
		if(count($schedulationsToAdd) > 0){
			\App\Models\Schedulation::insert($schedulationsToAdd);
			$campaign->first_scheduled_date = $schedulationsToAdd[0]['scheduled_date'];
			$campaign->status = 'scheduled';
			$campaign->save();
		}

		$logData = [
			'campaign_id' => $campaign->_id,
			'user_id' => $user->_id,
			'device' => strtoupper($this->device),
			'action' => 'MESSAGES',
			'description' => 'User created campaign'
		];
		$this->activityLogRepo->createActivityLog($logData);

		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Campaign created'
			],
			'campaign_id' => $campaign->_id,
			'group_id' => $mobileMessageGroupId
		];
		return response()->json($response);
	}

	/**
	 * Update message name
	 * POST /v1/mobile/messages/update-message-name
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postUpdateMessageName(Request $request)
	{
		$key = $request->get('key');
		$messageId = $request->get('message_id');
		$newName = $request->get('new_name');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$status = $user->campaigns()->where('_id', $messageId)->update(['campaign_name' => $newName]);
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Updated'
			]
		];
		return response()->json($response);
	}

	/**
	 * Create an image for the group
	 * POST /v1/mobile/messages/upload-group-image
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postUploadGroupImage(Request $request)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$groupImage = $request->file('group_image');
		$groupId = $request->get('group_id');
		if(!$groupImage){
			$response = $this->createBasicResponse(-1, 'File is required');
			return response()->json($response);
		}
		$group = \App\Models\MobileMessageGroup::find($groupId);
		if(!$group || $group->user_id != $user->_id){
			$response = $this->createBasicResponse(-2, 'Group does not exist');
			return response()->json($response);
		}
		$uploadPath = public_path() . '/uploads/files/group_images/';
		$fileName = 'groupimage' . str_random(20);
		$groupImage->move($uploadPath, $fileName);
		$status = \Storage::put($fileName, file_get_contents($uploadPath . $fileName) );
		$group->image = $fileName;
		$group->save();
		$amazonUrl = $this->getAmazonS3Url($fileName);
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Updated'
			],
			'amazonUrl' => $amazonUrl,
			'group' => $group
		];
		return response()->json($response);
	}

	/**
	 * Get all message groups of the user
	 * GET /v1/mobile/messages/groups
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function getGroups(Request $request)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$groups = $user->mobileGroups;

		$responseGroups = [];
		foreach ($groups as $group) {
			$lastCampaign = $group->campaigns->last();
			$responseGroups[] = [
				'onlineID' => $group->_id,
				'title' => $group->name,
				'thumbImage' => $group->image,
				'amazonUrl' => $group->image? $this->getAmazonS3Url($group->image): NULL,
				'success_phonenumbers' => isset( $lastCampaign->successPhonenumbers[0] ) ? $lastCampaign->successPhonenumbers[0]->count: 0,
				'total_phonenumbers' => isset( $lastCampaign->totalPhonenumbers[0] ) ? $lastCampaign->totalPhonenumbers[0]->count: 0,
				'messages_count' => count($group->campaigns),
				'last_message_actual_cost' => isset( $message->costPhonenumbers[0] ) ? $message->costPhonenumbers[0]->sum: 0,
				'last_message' => $lastCampaign
			];
		}
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Groups of the user'
			],
			'groups' => $responseGroups
		];
		return response()->json($response);
	}

	/**
	 * Get all messages of the user.
	 * GET /v1/mobile/messages/messages
	 *
	 * @param Request $request
	 * @return response
	 */
	public function getMessages(
		Request $request
		)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$groupId = $request->get('group_id');

		$messages = $user->campaigns()->where('mobile_message_group_id', $groupId)->with('voiceFile')->get();

		$responseMessages = [];
		foreach ($messages as $message) {
			$responseMessages[] = [
				'id' => $message->_id,
				'title' => $message->campaign_name,
				'price' => isset( $message->costPhonenumbers[0] ) ? $message->costPhonenumbers[0]->sum: 0,
				'length' => $message->voiceFile->length,
				'createdAt' => $message->created_at,
				'amazonUrl' => $this->getAmazonS3Url($message->voiceFile->map_filename)
			];
		}

		$response = [
			'error' => [
				'no' => 0,
				'message' => 'Messages of the group'
			],
			'messages' => $responseMessages
		];
		return response()->json($response);
	}

	/**
	 * Create an audio file from text
	 * POST /v1/mobile/messages/create-audio-from-text 
	 *
	 * @param Request $request
	 * @param FileService $fileRepo
	 * @return response
	 */
	public function postCreateAudioFromText(
		Request $request,
		\App\Services\FileService $fileRepo
		)
	{
		$key = $request->get('key');
		$text = $request->get('text');
		$language = $request->get('language');

		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];

		$fileResponse = $fileRepo->createFromText($text, $language, $user->_id);
		if(!$fileResponse->file){
			$response = $this->createBasicResponse(-1, $fileResponse->error);
			return response()->json($response);
		}
		$file = $fileResponse->file;
		$file['amazon_s3_url'] = $this->getAmazonS3Url($file->map_filename);
		$logData = [
			'user_id' => $user->_id,
			'device' => strtoupper($this->device),
			'action' => 'MESSAGES',
			'description' => 'User used tts service'
		];
		$this->activityLogRepo->createActivityLog($logData);

		$response = [
			'error' => [
				'no' => 0,
				'message' => 'File created'
			],
			'file' => $file
		];
		return response()->json($response);
	}

	/**
	 * Upload audio file
	 * POST /v1/mobile/messages/upload-audio-file
	 * 
	 * @param Request $request
	 * @param FileService $fileRepo
	 * @return string
	 */
	public function postUploadAudioFile(
		Request $request,
		\App\Services\FileService $fileRepo
		)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$originalName = $request->get('original_name');
		$type = $request->get('type');
		$voiceFile = $request->file('audio_file');
		$isTemplate = $request->get('is_template');
		$fileId = $this->uploadVoiceFile($voiceFile, $fileRepo, $user->_id, $originalName, $type, $isTemplate);
		if(!$fileId){
			$response = $this->createBasicResponse(-1, 'Something went wrong');
			return response()->json($response);
		}

		if($isTemplate){
			$cacheService = new \App\Services\Cache\UserDataRedisCacheService();
			$cacheService->incrementMessages($user->_id, 1);
		}

		$file = $fileRepo->getFileByPK($fileId);
		$file['amazon_s3_url'] =  $this->getAmazonS3Url($file->map_filename);
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'File created'
			],
			'file' => $file
		];
		return response()->json($response);
	}


	/**
	 * Upload a voice file
	 *
	 * @param FIle $voiceFile
	 * @param FileService $fileRepo
	 * @param string $userId
	 * @return mix
	 */
	private function uploadVoiceFile($voiceFile, $fileRepo, $userId, $origNameFromServer, $type, $isTemplate = false)
	{
		$extension = $voiceFile->getClientOriginalExtension();
		if(!in_array($extension, ['mp3','wav','gsm', 'm4a'])){
			return null;
		} else{
			$uploadFolder = public_path() . '/uploads/audio/';
			$newName = str_random();
			$originalName = $voiceFile->getClientOriginalName();
			$fileExtension = $voiceFile->getClientOriginalExtension();
			$voiceFile->move($uploadFolder, $newName . '.' . $fileExtension);
			$file = $fileRepo->createFile([
				'orig_filename' => $origNameFromServer ? $origNameFromServer : $originalName,
				'map_filename' => $newName . '.' . $fileExtension,
				'extension' => $fileExtension,
				'stripped_name' => $newName,
				'user_id' => $userId
				]);
			$isConvertedFromM4a = false;
			if($extension == 'm4a'){
				$isConvertedFromM4a = true;
				$cmd = 'ffmpeg -i ' . $uploadFolder . $newName . '.' . $extension . ' ' . $uploadFolder . $newName . '.wav';
            	$response = shell_exec( $cmd );
				$extension = 'wav';
			}
			if( $extension != 'gsm' ){
				$gsmAudioFile = $newName .'.gsm';
				$cmd = 'sox ' . $uploadFolder . $newName . '.' . $extension . ' -r 8000 -c 1 ' . $uploadFolder . $gsmAudioFile . ' silence 1 0.1 1%';
				$response = shell_exec( $cmd );
				$audioFilename = Helper::_extractFileName( $gsmAudioFile );
	        }
	        else {
				$gsmAudioFile = Helper::_extractFileName( $newName ) . '.gsm';
	        }
	        if($isConvertedFromM4a){
	        	//unlink($uploadFolder . $newName . '.' . $extension);
	        }
	        $length = $fileRepo->getFileSizeByPK($file->_id);
	        $file->length = floor($length/1000);
	        $file->type = $type;
	        $file->is_template = $isTemplate;
	        $file->save();

        	$fileRepo->moveAudioFileToAmazon($file->map_filename);
        	$fileRepo->moveAudioFileToAmazon($file->stripped_name . '.gsm');
	        return $file->_id;
		}
		
	}


	/**
	 * Get template audio files of the user
	 * GET /v1/mobile/messages/audio-templates
	 * 
	 * @param Request $request
	 * @return JSON
	 */
	public function getAudioTemplates(
		Request $request
		)
	{
		$key = $request->get('key');
		$type = $request->get('type');
		$page = $request->get('page');
		$searchKey = $request->get('search_key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$files = $user->files()->where('is_template', 1);

		if($type && $type != 'ALL'){
			$files = $files->where('type', $type);
		}
		if($searchKey){
			$files = $files->where(function($query) use ($searchKey){
				$query->where('orig_filename', 'LIKE', '%' . $searchKey . '%')
					->orWhere('tts_text', 'LIKE', '%' . $searchKey . '%');
			});
		}
		$count = $files->count();
		if($page >= 0){
			$files = $files->skip($page * 10)->take(10);
		}
		$files = $files->get();
		foreach ($files as $file) {
			$file['amazon_s3_url'] =  $this->getAmazonS3Url($file->map_filename);
		}
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Audio templates'
			],
			'files' => $files,
			'page' => $page,
			'count' => $count,
		];
		return response()->json($response);
	}


}