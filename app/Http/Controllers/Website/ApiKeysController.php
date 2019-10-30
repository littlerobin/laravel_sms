<?php

namespace App\Http\Controllers\Website;

use App\Models\ApiKey;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Auth;
use PhpParser\Node\Expr\Cast\Object_;

class ApiKeysController extends WebsiteController
{
    /**
     * Create a new instance of ApiKeysController class
     *
     * @return void
     */
    public function __construct()
    {
        $this->activityLogRepo = new ActivityLogService();
    }

    /**
     * Send request for creating new api key.
     * POST /api-keys/create-api-key
     *
     * @param Request $request
     * @return JSON
     */
    public function postCreateApiKey(Request $request)
    {
        $user = Auth::user();
        $description = $request->get('description');
        $type = $request->get('type');
        $generatedApiKey = str_random(40);
        $data = [
            'user_id' => $user->_id,
            'description' => $description,
            'type' => $type,
            'key' => $generatedApiKey
        ];
        $apiKeyObject = \App\Models\ApiKey::create($data);
        $apiKeyObject->services()->sync($request->get('services', []));
        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'API',
            'description' => 'User has created new API key - ' . $generatedApiKey
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'successfully_created'
            ],
            'api_key_object' => $apiKeyObject
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for etting all API keys.
     * GET /api-keys/api-keys
     *
     * @return JSON
     */
    public function getApiKeys()
    {
        $user = Auth::user();
        $apiKeys = ApiKey::where('user_id',$user->_id)->with('campaigns')->get();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'api_keys_of_the_user'
            ],
            'api_keys' => $apiKeys
        ];
        return response()->json(['resource' => $response]);
    }


    public function getApiKeyStatistics($id)
    {
        $statistics = [];
        $campaigns = Campaign::where('api_key_id',$id)->with('totalPhonenumbers','archivedTotalPhonenumbers','costPhonenumbers','callsCount','archivedCallsCount','smsCount')->get();
        foreach ($campaigns as $campaign) {

            $recipients = 0;
            $campaign->totalPhonenumbers && $campaign->totalPhonenumbers->count() ? $recipients += $campaign->totalPhonenumbers[0]->count : true;
            $campaign->archivedTotalPhonenumbers && $campaign->archivedTotalPhonenumbers->count() ? $recipients += $campaign->archivedTotalPhonenumbers[0]->count : true;

            $cost = 0;
            $campaign->costPhonenumbers && $campaign->costPhonenumbers->count() ? $cost = $campaign->costPhonenumbers[0]->sum : true;

            $callsCount = 0;
            $campaign->costPhonenumbers && $campaign->costPhonenumbers->count() && count($campaign->callsCount) ? $callsCount = $campaign->callsCount[0]->count : true;
            $campaign->archivedCallsCount && $campaign->archivedCallsCount->count() ? $callsCount = $campaign->archivedCallsCount[0]->count : true;
            $campaign->smsCount && $campaign->smsCount->count() && isset($campaign->smsCount[0]) ? $callsCount = $campaign->smsCount[0]->count : true;

            $date = Carbon::parse($campaign->created_at);
            $hours = 0;
            $minutes = 0;
            if($campaign->last_called) {
                $hours = $date->diffInHours(Carbon::parse($campaign->last_called));
                $minutes = $date->diffInMinutes(Carbon::parse($campaign->last_called));
            }

            $duaration = '';
            $hours > 0 ? $duaration .= $hours.' Hours ' : true;
            $minutes = $minutes % 60;
            $duaration .= $minutes.' Minutes';

            $statistics[] = (object) [
                'type' => $campaign->type,
                'recipients' => $recipients,
                'name' => $campaign->campaign_name,
                'delivered_on' => $campaign->last_called,
                'calls_made' => $callsCount,
                'interactions' => 'No Iteraction',
                'total_time' => $duaration,
                'cost' => $cost,
            ];
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'api_key_statistics'
            ],
            'statistics' => $statistics
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing API keys.
     * DELETE /api-keys/remove-api-key/{$id}
     *
     * @param integer $id
     * @return JSON
     */
    public function deleteRemoveApiKey($id)
    {
        $user = Auth::user();
        $apiKeyObject = $user->apiKeys()->find($id);
        if(!$apiKeyObject){
            $response = $this->createBasicResponse(-1, 'api_ key_is_not_exists_or_not_belongs');
            return response()->json(['resource' => $response]);
        }

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'API',
            'description' => 'User removed - ' . $apiKeyObject->key . ' from API keys'
        ];
        $this->activityLogRepo->createActivityLog($logData);
        
        $apiKeyObject->delete();
        
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'successfully_removed'
            ],
            'api_key_id' => $id
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing API token (session)
     * DELTE /api-keys/remove-api-token
     *
     * @param Request $request
     * @return JSON
     */
    public function deleteRemoveApiToken($id)
    {
        $apiToken = Auth::user()->apiTokens()->find($id);
        if(!$apiToken){
            $response = $this->createBasicResponse(-1, 'api_ token_is_not_exists');
            return response()->json(['resource' => $response]);
        }
        $redis = \LaravelRedis::connection();
        $redis->del('laravel:' . $apiToken->session_id);
        $response = $this->createBasicResponse(0, 'token__removed');
        $apiToken->delete();
        return response()->json(['resource' => $response]);
    }

}