<?php namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Mobile\MobileController as Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * This class is responsible for all phonenumbers related things
 * validations, getting dialled phonenumbers etc.
 */
class PhonenumbersController extends Controller {

	/**
	 * The current device.
	 *
	 * @var string
	 */
	private $device;

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
	}

	
	/**
	 * Make validation for contacts adding
	 * POST /v1/mobile/phonenumbers/validate-phonenumbers
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postValidatePhonenumbers(
		Request $request,
		\App\Services\CampaignDbService $campaignDbRepo
		)
	{
		$key = $request->get('key');
		$phonenumbers = $request->get('phonenumbers', []);
		$phonenumbers = array_unique($phonenumbers);
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];

		$allCount = count($phonenumbers);
		$success = 0;
		$invalid = 0;
		$notSupported = 0;
		$freeCallsMaxDuration = 0;
		$respArray = [];

		$countries = \App\Models\Country::all();

		$maxCost = 0;
		foreach ($phonenumbers as $phonenumber) {
			$validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
			if(!$validationResponse['finalNumber']){
				$invalid++;
				$respArray[] = ['number' => $phonenumber, 'status' => 'not_supported'];
				continue;
			}
			$tariff = $validationResponse['detectedTariff'];
			$finalNumber = $validationResponse['finalNumber'];

			$shouldBeFree = $this->shouldPhonenumberBeFree($tariff, $user->numbers()->first(), $user);
			$tempCost = $tariff['country']['customer_price'] / 60;
			if(!$shouldBeFree){
				$maxCost += $tempCost;
			} else{
				$freeCallsMaxDuration = $user->country->mobile_free_message_max_duration;
			}
			$respArray[] = [
				'number' => $finalNumber, 
				'status' => 'success', 
				'tariff' => $tariff,
				'is_free' => $shouldBeFree,
				'cost' => $tempCost];
			$success++;
		}
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Phonenumbers with statuses'
			],
			'count' => $allCount,
			'success' => $success,
			'invalid' => $invalid,
			'not_supported' => $notSupported,
			'phonenumbers' => $respArray,
			'max_cost_per_sec' => $maxCost,
			'free_calls_max_duration' => $freeCallsMaxDuration
		];
		return response()->json($response);
	}

	/**
	 * Make validation of numbers array.
	 * POST /v1/mobile/phonenumbers/calculate-max-cost-and-filter
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postCalculateMaxCostAndFilter(
		Request $request,
		\App\Services\UserService $userRepo,
		\App\Services\CampaignDbService $campaignDbRepo,
		\App\Services\AserverService $aserverRepo
		)
	{
		$key = $request->get('key');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$phoneNumbers = $request->get('phonenumbers', []);
		if(!is_array($phoneNumbers)){
			$response = $this->createBasicResponse(-1, 'Phonenumbers should be array');
			return response()->json($response);
		}
		$fileId = $request->get('file_id');
		$finalNumbersArray = [];
		$responseArray = [];
		$countries = \App\Models\Country::all();
		foreach($phoneNumbers as $phonenumber) {
			$validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
			if(!$validationResponse['finalNumber']){
				continue;
			}
			$tariff = $validationResponse['detectedTariff'];
			$finalNumber = $validationResponse['finalNumber'];
			$finalNumbersArray[] = ['phonenumber' => $finalNumber, 'tariff' => $tariff];
			$responseArray[] = $finalNumber;
		}
		$maxCost = 0;
		$file = $user->files()->find($fileId);
		$length = $file->length;
		foreach ($finalNumbersArray as $finalNumber) {
			if($length < 20){
                $length = 20;
            }
            $maxCost += $finalNumber['tariff']['country']['customer_price'] * $length / 60;
		}

		/*$receipentsCount = count($responseArray);
		$availableChannelsCount = $aserverRepo->getAvailableChannelsCount();
		$totalFreeAsteriskServers = $availableChannelsCount['available'] != 0 ? $availableChannelsCount['available'] : 1;
		$queuedCampaigns = $availableChannelsCount['queued'];

		$totalSeconds = round( $receipentsCount / $totalFreeAsteriskServers * $length );
		$totalQueuedSeconds = $queuedCampaigns * 80;

		$totalSendingMinute = round( ($totalSeconds + $totalQueuedSeconds) / 60 );

		$finalSendingTime = 1;
		for($i = 1; $i <= $totalSendingMinute; $i++){
			if($totalSendingMinute < $i * 5){
				$finalSendingTime = $i * 5;
				break;
			}
		}
*/
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'max cost calculated'
			],
			'max_cost' => $maxCost,
			//'sending_time' => $finalSendingTime,
			'phonenumbers' => $responseArray
		];
		return response()->json($response);
	}

	/**
	 * Get phonenumbers of the campaign
	 * GET /v1/mobile/phonenumbers/phonenumbers
	 *
	 * @param Request $request
	 * @return JSON 
	 */
	public function getPhonenumbers(Request $request)
	{
		$key = $request->get('key');
		$messageId = $request->get('message_id');
		$checkedKey = $this->checkKey($key);
		if(!$checkedKey){
			$response = $this->createBasicResponse(-10, 'Invalid or expired API key');
			return response()->json($response);
		}
		$user = $checkedKey['user'];
		$message = $user->campaigns()->find($messageId);
		if(!$message){
			$response = $this->createBasicResponse(-1, 'Campaign does not exists or not belongs to you.');
			return response()->json($response);
		}
		$phonenumbers = $message->phonenumbers();
		$count = $phonenumbers->count();
		$phonenumbers = $phonenumbers->get();

		$responsePhonenumbers = [];
		foreach ($phonenumbers as $phonenumber) {
			$responsePhonenumbers[] = [
				'phoneNumber' => $phonenumber->phone_no,
				'status' => $phonenumber->call_status
			];
		}
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'List of recipients'
			],
			'recipients' => $phonenumbers,
			'count' => $count
		];
		return response()->json($response);
	}

}