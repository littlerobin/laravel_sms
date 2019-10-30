<?php namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class MobileController extends Controller{

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
		$userRepo = new \App\Services\UserService();
		$user = $userRepo->getUserByMobileApiToken($key);
		if(!$user){
			return false;
		}
		return [ 'type' => 'api_token', 'user' => $user];
	}

	/**
     * Create basic response to return to angular
     *
     * @param integer $code
     * @param string $text
     * @return array
     */
    protected function createBasicResponse($code, $text){
    	return [
    		'error' => [
    			'no' => $code,
    			'text' => $text
    		]
    	];
    }

    /**
     * Get amazon s3 url for the file
     *
     * @param File $file
     * @return string
     */
    protected function getAmazonS3Url($fileName)
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $s3 = \Storage::disk('s3');

        $s3Client = \Aws\S3\S3Client::factory(array(
            'credentials' => array(
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret')
            ),
            'region' => config('filesystems.disks.s3.region'),
            'signature' => 'v4',
            'version' => 'latest'
        ));

        $command = $s3Client->getCommand('GetObject', [
            'Bucket'                     => $bucket,
            'Key'                        => $fileName
        ]);
        return (string)$s3Client->createPresignedRequest($command, '+30 minutes')->getUri();
    }

    /**
     * Check if the call should be free .
     * If the call is going to be free increment users daily 
     * used free calls .
     * 
     * @param Tariff $tariff
     * @param CallerId $callerId
     * @param Campaign $campaign
     * @param User $user
     * @return bool
     */
    protected function shouldPhonenumberBeFree($tariff, $usersCallerId, $user)
    {
        $country = $usersCallerId->tariff->country;
        if($tariff->country_id != $usersCallerId->tariff->country_id){ return false;}
        if(!$user->country){return false;}

        if($tariff->best_margin < $country->free_call_minimum_margin){return false;}

        $freeMessagesPerDay = $country->mobile_free_messages_count_per_day;
        $freeMessageMaxDuration = $country->mobile_free_message_max_duration;

        if(!$user->last_used_free_credit_at || !Carbon::createFromFormat('Y-m-d H:i:s', $user->last_used_free_credit_at)->isToday()){
            $user->free_calls_made = 0;
        }
        if($user->free_calls_made >= $freeMessagesPerDay){return false;}
        $user->last_used_free_credit_at = Carbon::now();
        $user->free_calls_made++;
        $user->save();
        return true;
    }
}