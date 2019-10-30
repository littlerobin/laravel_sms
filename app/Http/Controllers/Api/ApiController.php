<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller{

	/**
	 * Check if api key is valid.
	 *
	 * @param string $key
	 * @return bool
	 */
	protected function checkKey($key)
	{
		if(!$key){
			return false;
		}
		$keyObject = \App\Models\ApiKey::where('key', $key)->first();
		if(!$keyObject){
			return false;
		}
		return $keyObject;
	}

	/**
     * Create basic response to return to angular
     *
     * @param integer $code
     * @param string $text
     * @return array
     */
    public function createBasicResponse($code, $text){
    	return [
    		'error' => [
    			'no' => $code,
    			'text' => $text
    		]
    	];
    }
}