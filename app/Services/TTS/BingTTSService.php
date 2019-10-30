<?php namespace App\Services\TTS;

use App\Helper;
use App\Services\FileService;

class BingTTSService{

    /**
     * Create a new instance of BingTTSService class
     *
     * @return void
     */
    public function __construct()
    {
        $this->fileRepo = new FileService();
    }


	/*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
    private function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array (
                 'grant_type'    => $grantType,
                 'scope'         => $scopeUrl,
                 'client_id'     => $clientID,
                 'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
                $curlError = curl_error($ch);
                \Log::info($curlError);
                return false;
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if (isset($objResponse->error)){
                \Log::info($objResponse->error);
                return false;
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            //echo "Exception-".$e->getMessage();
            return false;
        }
    }

    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     *
     * @return string.
     *
     */
    private function curlRequest($url, $authHeader){
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
        	\Log::info($curlErrno);
            return false;
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }

    /**
     * Create an audio file from text with BING
     *
     * @param string $text
     * @param string $language
     * @param string $userId
     * @return File
     */
    public function createFromText($text, $language, $user, $ttsPrice, $savedFrom = null)
    {
        $uploadFolder = public_path() . '/uploads/audio/';
        $newName = str_random();
        $fileExtension = 'mp3';
        $path = $uploadFolder . $newName . '.' . $fileExtension;
        
        $text = urlencode($text);
        $status = $this->createTts($text, $language, $path);
        if(!$status){
            return false;
        }
        $file = \App\Models\File::create([
            'orig_filename' => $newName . '.' . $fileExtension,
            'map_filename' => $newName . '.' . $fileExtension,
            'extension' => $fileExtension,
            'stripped_name' => $newName,
            'tts_language' => $language,
            'tts_text' => $text,
            'user_id' => $user->_id,
            'type' => 'TTS',
            'saved_from' => $savedFrom,
            'is_template' => 1,
            'cost' => $ttsPrice
            ]);
        $gsmAudioFile = Helper::_stripFileExtension($newName).'.gsm';
        $cmd = 'sox ' . $path . ' -r 8000 -c 1 ' . $uploadFolder . $gsmAudioFile . ' silence 1 0.1 1%';
        $response = shell_exec( $cmd );
        $length = $this->fileRepo->getFileSizeByPK($file->_id);
        $file->length = ceil($length/1000);
        $file->save();
        return $file;
    }

    public function createTts($inputStr, $language, $path)
    {
    	//Client ID of the application.
	    $clientID       = config('tts.bing_client_id');
	    //Client Secret key of the application.
	    $clientSecret = config('tts.bing_client_secret');
	    //OAuth Url.
	    $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
	    //Application Scope Url
	    $scopeUrl     = "http://api.microsofttranslator.com";
	    //Application grant type
	    $grantType    = "client_credentials";

	    //Get the Access token.
	    $accessToken  = $this->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
	    if(!$accessToken){
	    	return false;
	    }
	    //Create the authorization Header string.
	    $authHeader = "Authorization: Bearer ". $accessToken;

	    //Set the params.
	    $params = "text=$inputStr&language=$language&format=audio/mp3";

	    //HTTP Speak method URL.
	    $url = "http://api.microsofttranslator.com/V2/Http.svc/Speak?$params";
	    
	    //Call the curlRequest.
	    $strResponse = $this->curlRequest($url, $authHeader);
	    if(!$strResponse){
	    	return false;
	    }
	    file_put_contents($path, $strResponse);
	    return true;
    }
}