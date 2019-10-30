<?php

namespace App\Http\Controllers\Website;

use App\Services\CampaignDbService;
use App\Services\NumberVerificationService;
use App\Services\RequestRateLimitService;
use App\Services\TariffService;
use Auth;
use Illuminate\Http\Request;
use App\Models\CallerId;
use App\Services\SlackNotificationService;
use App\Services\BlackListIpService;

class VerificationsController extends WebsiteController
{
    private $blackListIpRepo;

    public function __construct(BlackListIpService $blackListIpRepo)
    {
        $this->blackListIpRepo = $blackListIpRepo;
    }


    /**
     * Send request for receiving verification code.
     * POST /verifications/send-verification-code
     *
     * @param Request $request
     * @return JSON
     */
    public function postSendVerificationCode (
        Request $request,
        CampaignDbService $campaignDbRepo,
        TariffService $tariffRepo,
        NumberVerificationService $numVerificationRepo) {
        $ipAddress = $request->ip();
        $phonenumber = $request->get('phonenumber');
        $isLoginRecovery = $request->get('is_login_recovery');
        $errorNumber = -100;
        $errorMessage = 'something__went__wrong';
        $userId = null;
        $ifLogged = Auth::check();
        if ($ifLogged) {
            $userId = Auth::user()->_id;
        }
        //dd($phonenumber);
        $validationResponse = $campaignDbRepo->isValidNumber($phonenumber);

        //dd($validationResponse['finalNumber']);
        if (!$validationResponse['finalNumber']) {
            //dd(123);
            $errorNumber = -1;
            $errorMessage = 'invalid_phonenumber';
        } else {
            $tariff = $validationResponse['detectedTariff'];
            $phonenumber = $validationResponse['finalNumber'];
            if($isLoginRecovery && !CallerId::where('phone_number', $phonenumber)->first()) {
                $response = $this->createBasicResponse(-6, 'caller_id_not_exists');
                return response()->json(['resource' => $response]);
            }

            // if ($ifLogged && !$numVerificationRepo->checkIfAllCallerIdsHaveSameCountry(Auth::user(), $tariff)) {
            //     $response = $this->createBasicResponse(-3, 'all_caller_ids_should_belong_to');
            //     return response()->json(['resource' => $response]);
            // }
            if(!$ifLogged){
                $blacklistIp = $this->blackListIpRepo->getBlacklistIpByIp($ipAddress);
            }
            if(isset($blacklistIp) && $blacklistIp){
                $phonenumbers = $blacklistIp->json;
                if(gettype($phonenumbers) == 'string'){
                    $phonenumbers = json_decode($phonenumbers);
                }
                if(!in_array($phonenumber,$phonenumbers)){
                    array_push($phonenumbers,$phonenumber);
                }
                $phonenumbers = json_encode($phonenumbers);
                $this->blackListIpRepo->update($blacklistIp->_id,array('json' => $phonenumbers));
                $response = $this->createBasicResponse(-5, 'daily_max_limit_expired');
                SlackNotificationService::notify("IP $ipAddress BLOCKED trying to verify $phonenumber");
                return response()->json(['resource' => $response]);
            }
            $requestRateLimitRepo = new RequestRateLimitService();
            if (!$requestRateLimitRepo->canMakeVerification($phonenumber, $ipAddress)) {
                if(!$ifLogged){
                    $blacklist_ip_array = [];
                    array_push($blacklist_ip_array, $phonenumber);
                    $json = json_encode($blacklist_ip_array);
                    $data = [
                        'ip' => $ipAddress,
                        'user_id' => isset($userId) ? $userId : NULL,
                        'phonenumber' => $phonenumber,
                        'json' => $json
                    ];
                    $this->blackListIpRepo->createBlacklistIp($data);
                }
                $response = $this->createBasicResponse(-5, 'daily_max_limit_expired');
                SlackNotificationService::notify("IP $ipAddress BLOCKED trying to verify $phonenumber");
                return response()->json(['resource' => $response]);

            }

            if ($ifLogged) {
                $callerId = CallerId::where('phone_number', $phonenumber)
                    ->where('user_id', $userId)->first();
                if ($callerId) {
                    $response = $this->createBasicResponse(-4, 'you_already_have_this_caller_id');
                    return response()->json(['resource' => $response]);
                }
            }

            $phonenumberObj = new \stdClass;
            $phonenumberObj->tariff = $tariff;
            $isp = $tariffRepo->detectIsp($phonenumberObj);
            if (!$isp) {
                $errorNumber = -2;
                $errorMessage = 'not__supported_1';
            } else {
                SlackNotificationService::notify("IP $ipAddress is trying to verify $phonenumber");
                $phonenumberData = [
                    'ip_address' => $ipAddress,
                    'user_id' => $userId,
                    'phone_no' => $phonenumber,
                    'aserver_id' => null,
                    'isp_id' => $isp->_id,
                    'tariff_id' => $tariff->_id,
                    'cost' => 0,
                    'action_type' => "VERIFICATION_CALL",
                ];
                $finalPhonenumber = \App\Models\Phonenumber::create($phonenumberData);
                $requestRateLimitRepo->inrementVerificationCache($phonenumber, $ipAddress);

                $response = [
                    'error' => [
                        'no' => 0,
                        'text' => 'system_Is_making_verification_call',
                    ],
                    'phonenumber' => $phonenumber,
                    'phonenumber_id' => $finalPhonenumber->_id,
                ];
                return response()->json(['resource' => $response]);
            }
        }
        $response = [
            'error' => [
                'no' => $errorNumber,
                'text' => $errorMessage,
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Check call status
     * GET /verifications/check-call-status
     *
     * @param Request $request
     * @return JSON
     */
    public function getCheckCallStatus(Request $request)
    {
        $phonenumberId = $request->get('phonenumber_id');
        $phonenumber = \App\Models\Phonenumber::find($phonenumberId);
        if (!$phonenumber) {
            $response = $this->createBasicResponse(-1, 'not__exist_1');
            return response()->json(['resource' => $response]);
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'status__2',
            ],
            'status' => $phonenumber->call_status,
        ];
        return response()->json(['resource' => $response]);

    }

    /**
     * Send request for checking if phonenumber code is valid
     * POST /verifications/check-voice-code-validation
     *
     * @param Request $request
     * @return JSON
     */
    public function postCheckVoiceCodeValidation(Request $request, NumberVerificationService $numVerificationRepo)
    {
        $phonenumber = $request->get('phonenumber');
        $code = $request->get('voice_code');
        $numberField = $numVerificationRepo->getNumberVerification($code, $phonenumber);
        if (!$numberField) {
            $response = $this->createBasicResponse(-1, 'Invalid__code__and_phonenumber');
            return response()->json(['resource' => $response]);
        }
        $response = $this->createBasicResponse(0, 'Phonenumber__is__valid');
        return response()->json(['resource' => $response]);
    }
}
