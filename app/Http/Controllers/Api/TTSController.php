<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;

class TTSController extends ApiController{


	/**
	 * Create a new instance of AdderssBookApiController class.
	 *
	 * @param Request $request
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
     * Returns the TTS languages
     * GET /v1/api/tts
     *
     * @param Request $request
     * @return JSON
     */
    public function getIndex()
    {
		$ttsLanguages = config('tts.nuance_codes');
		$response = [
			'error' => [
				'no' => 0,
				'message' => 'List of TTS languages'
			],
			'tts_languages' => $ttsLanguages
		];
		return response()->json($response);
    }


}