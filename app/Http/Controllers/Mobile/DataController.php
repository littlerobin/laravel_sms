<?php namespace App\Http\Controllers\Mobile;


use App\Http\Controllers\Mobile\MobileController as Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * This class is responsible for handling requests
 * from android and ios applications for getting various 
 * data . 
 */
class DataController extends Controller {

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
	}

	/**
	 * Get call routes
	 * GET /v1/mobile/data/call-routes
	 *
	 * @param TariffSerivce $tariffRepo
	 * @return JSON
	 */
	public function getCallRoutes(
		)
	{
		$routes = \App\Models\Country::where('customer_price', '!=', 0)->get();
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'List of routes'
			],
			'routes' => $routes
		];
		return response()->json($response);
	}


	/**
	 * Get TTS Languages.
	 * GET /v1/mobile/data/tts-languages
	 *
	 * @return JSON
	 */
	public function getTtsLanguages()
	{
		$engine = config('tts.engine');
		if($engine == 'GOOGLE'){
			$voices = config('tts.google_codes');
		} elseif($engine == 'NUANCE'){
			$voices = config('tts.nuance_codes');
		}elseif($engine == 'BING'){
			$voices = config('tts.bing_codes');
		} else{
			$response = [
				'error' => [
					'no' => -1,
					'text' => 'No tts engine enabled'
				]
			];
			return response()->json($response);
		}

		$response = [
			'error' => [
				'no' => 0,
				'text' => 'languages'
			],
			'languages' => $voices
		];
		return response()->json($response);
	}
}