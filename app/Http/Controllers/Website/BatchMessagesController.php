<?php

        /**
     * Send request for validating batch file and counting max cost.
     * POST /phonenumbers/validate-batch-file
     *
     * @param Request $request
     * @return JSON
     */
    public function postValidateBatchFile(Request $request, \App\Services\CampaignDbService $campaignDbRepo)
    {
        $file = $request->file('file');
        $user = Auth::user();
        if(!$file){
            return response()->json(['status' => 'error', 'message' => 'file_is_not_specified__1']);
        }
        $extension = $file->getClientOriginalExtension();
        if(!in_array($extension, ['csv', 'txt', 'xls', 'xlsx'])){
            return response()->json(['status' => 'error', 'message' => 'file_format_not_supported']);
        }
        $path = $file->getRealPath();
        $csv = Reader::createFromPath($path);
        $delimeterArray = $csv->detectDelimiterList(1, [".", ";", ":", "|", "\t"]);
        foreach ($delimeterArray as $delim) {
            $csv->setDelimiter($delim);
            break;
        }
        $fetchedList = $csv->fetchAll();
        $phonenumbersWithText = [];
        $countries = \App\Models\Country::all();
        foreach ($fetchedList as $list) {
            if(isset($list[0]) && isset($list[1])){

                $validationResponse = $campaignDbRepo->isValidNumber($list[0], $countries);
                if(!$validationResponse['finalNumber']){
                    continue;
                }
                $tariff = $validationResponse['detectedTariff'];
                $finalNumber = $validationResponse['finalNumber'];
                $phonenumbersWithText[] = ['phonenumber' => $finalNumber, 'country_code' => $tariff->country->code, 'text' => $list[1]];
            }
        }
        if(count( $phonenumbersWithText) == 0 ){
            $response = $this->createBasicResponse(-1, 'please_make_sure_your_batch_file_is_valid');
            return response()->json(['resource' => $response]); 
        }
        $ttsLanguage = $request->get('tts_language');
        $response = $this->validateBatchFileData($user, $phonenumbersWithText, $ttsLanguage, 0);
        $response['tts_language'] = $ttsLanguage;
        return response()->json(['resource' => $response]);
    }

    /**
     * Batch send campaigns.
     * POST /campaigns/batch-send
     *
     * @param Request $request
     * @return JSON
     */
    public function postBatchSend(Request $request, CampaignDbService $campaignDbRepo, CampaignService $campaignRepo)
    {
        $user = Auth::user();
        $campaignName = $request->get('campaign_name');
        $callerId = $request->get('caller_id');
        $transferDigit = $request->get('transfer_digit');
        $transferOptions = $request->get('transfer_options');
        $replayDigit = $request->get('replay_digit');
        $callbackDigit = $request->get('callback_digit');
        $callbackVoiceFileId = $request->get('callback_voice_file_id');
        $doNotCallDigit = $request->get('do_not_call_digit');
        $doNotCallVoiceFileId = $request->get('do_not_call_voice_file_id');
        $liveAnswersOnly = $request->get('live_answer_only');
        $playbackCount = $request->get('playback_count');
        $totalRetries = 5;
        $liveTransferLimit = $request->get('live_transfer_limit');
        $timezone = $request->get('timezone');
        $status = $request->get('status');
        $getEmailNotifications = $request->get('get_email_notifications', 1);
        $repeatBatchGrouping = str_random(20);
        $phonenumbersWithText = $request->get('phonenumbers_with_text');
        $ttsLanguage = $request->get('tts_language');
        $response = $this->validateBatchFileData($user, $phonenumbersWithText, $ttsLanguage, 1);
        $batchData = $response['phonenumbers'];

        if(count($batchData) == 0){
            $response = $this->createBasicResponse(-13, 'there_is_no_valid_phonenumber_in_your_batch');
            return response()->json(['resource' => $response]);
        }

        $maxCost = $response['max_cost'];
        $maxGiftCost = $response['max_gift_cost'];
        $remainingRepeats = $request->get('remaining_repeats');
        $repeatDaysInterval = $request->get('repeat_days_interval');
        $playbackCount = $request->get('playback_count');
        $schedulations = $request->get('schedulations');
        $schedulations = json_encode($schedulations);

        $validatorData = $request->all();

        $Errors = MessageValidationService::Validator($validatorData,'BatchSend');
        if($Errors) {
            $response = $this->createBasicResponse($Errors->errorNumber, $Errors->errorMessage);
            return $response;
        }
        $usersCallerId = $user->numbers()->where('phone_number', $callerId)->with('tariff')->first();
        if(!$usersCallerId){
            $response = $this->createBasicResponse(-11, 'caller_id_is_not_registered_for_the_user');
            return response()->json(['resource' => $response]);
        }

        if(!MessageValidationService::haveEnoughBalanceForCall('saved', $maxCost, $maxGiftCost, $user)){
            $response = $this->createBasicResponse(-12, 'balance_is_not_enough_for_creating_message');
            return response()->json(['resource' => $response]);
        }


        $finalResponse = [
            'error' => [
                'no' => 0,
                'text' => 'batch__created'
            ],
        ];
        $countries = \App\Models\Country::all();
        foreach ($batchData as $batch) {
            /**********************************************/
            $campaignData = [
                'campaign_name' => $campaignName,
                'caller_id' => $callerId,
                'campaign_voice_file_id' => $batch['file_id'],
                'transfer_digit' => $transferDigit,
                'transfer_option' => $transferOptions,
                'replay_digit' => $replayDigit,
                'callback_digit' => $callbackDigit,
                'callback_digit_file_id' => $callbackVoiceFileId,
                'do_not_call_digit' => $doNotCallDigit,
                'do_not_call_digit_file_id' => $doNotCallVoiceFileId,
                'live_answer_only' => $liveAnswersOnly,
                'retries' => $totalRetries,
                'live_transfer_limit' => $liveTransferLimit,
                'total_phonenumbers_loaded' => 0,
                'user_id' => $user->_id,
                'max_concurrent_channels' => 10,
                'timezone' => $timezone,
                'status' => $status,
                'get_email_notifications' => $getEmailNotifications,
                'repeat_batch_grouping' => $repeatBatchGrouping,
                'remaining_repeats' => 0,
                'repeat_days_interval' => NULL,
                'grouping_type' => 'BATCH',
                'playback_count' => $playbackCount,
                'schedulation_original_data' => NULL,
                'retained_balance' => $maxCost,
                'retained_gift_balance' => $maxGiftCost,

            ];
            $campaign = $campaignRepo->createCampaign($campaignData);

            $cacheService = new \App\Services\Cache\UserDataRedisCacheService();
            $cacheService->incrementMessages($campaign->user_id, 1);

            //$phonenumbersToadd = [];
            $protoPhonenumbersToadd = [];

            $phonenumber = $batch['phonenumber'];
            $validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $countries);
            if(!$validationResponse['finalNumber']){
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];

            $isUserNumberFromEu = $usersCallerId->tariff->country->is_eu_member;
            $isNumberFromEu = $tariff->country->is_eu_member;
            $isFromNotEuToEu = !$isUserNumberFromEu && $isNumberFromEu;

            $phonenumbersToadd = [
                'campaign_id' => $campaign->_id,
                'phone_no' => $validationResponse['finalNumber'],
                'created_at' => date('Y-m-d H:i:s'),
                'retries' => 0,
                'tariff_id' => $tariff->_id,
                'user_id' => $user->_id,
                'is_from_not_eu_to_eu' => $isFromNotEuToEu
            ];
            \App\Models\Phonenumber::create($phonenumbersToadd);

            $logData = [
                'campaign_id' => $campaign->_id,
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'MESSAGES',
                'description' => 'User created campaign'
            ];
            $this->activityLogRepo->createActivityLog($logData);
            /**********************************************/
        }
        return response()->json(['resource' => $finalResponse]);
    }



    /**
     * Validate batch file data
     *
     */
    private function validateBatchFileData($user, $phonenumbersWithText, $ttsLanguage, $isNeedToCreateFiles)
    {

        $campaignDbRepo = new \App\Services\CampaignDbService();
        $fileRepo = new \App\Services\FileService();
        $userRepo = new \App\Services\UserService();
        $user->giftWithCriteria = $userRepo->getUsersGiftWithCriteria($user);

        $countries = \App\Models\Country::all();
        $maxCost = 0;
        $maxGiftCost = 0;
        $finalNumbersArray = [];
        $phonenumbersWithText = array_map("unserialize", array_unique(array_map("serialize", $phonenumbersWithText)));
        foreach ($phonenumbersWithText as $phonenumberWithText) {
            $validationResponse = $campaignDbRepo->isValidNumber($phonenumberWithText['phonenumber'], $countries);
            if(!$validationResponse['finalNumber']){
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];
            if($isNeedToCreateFiles){
                $fileResponse = $fileRepo->createFromText($phonenumberWithText['text'], $ttsLanguage, $user->_id);
                if(!$fileResponse->file){
                    continue;
                }
                $file = $fileResponse->file;
                $finalNumbersArray[] = ['phonenumber' => $phonenumberWithText['phonenumber'], 'file_id' => $file->_id ];
                $length = $file->length;
            } else{
                $finalNumbersArray[] = ['phonenumber' => $phonenumberWithText['phonenumber'], 'file_id' => '' ];
                $length = ceil( strlen($phonenumberWithText['text']) / 10 );
            }
            if($length < 20){
                $length = 20; 
            }
            $cost = $tariff->country->customer_price * $length / 60;
            $newPhonenumberObject = (object)['tariff' => $tariff];
            if($userRepo->canUseGift($newPhonenumberObject, $user, $maxGiftCost, $cost)){
                $maxGiftCost += $cost;
            } else{
                $maxCost += $cost;
            }
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'batch_file_data_verified'
            ],
            'max_cost' => $maxCost,
            'max_gift_cost' => $maxGiftCost,
            'phonenumbers' => $finalNumbersArray,
            'phonenumbers_with_text' => array_values( $phonenumbersWithText )
        ];
        return $response;
    }