<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Services\YoutubeService;

class DataController extends WebsiteController
{

    /**
     * Create a new instance of CampaignsController class.
     *
     * @return void
     */
    public function __construct(YoutubeService $youtubeService)
    {
       // $this->middleware('auth', ['only' => ['getDashboard']]);
        $this->middleware('jwt.headers');
        $this->middleware('jwt.auth', ['only' => [
                'getDashboard',
                'getDownloadToken',
                'getTaxData',
            ]
        ]);
        $this->youtubeService = $youtubeService;
    }

    /**
     * Get all timezones supported by API .
     *
     * @return JSON
     */
    public function getTimezones()
    {
        $timezones = timezone_identifiers_list();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'all__timezones'
            ],
            'timezones' => $timezones
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get all cities supported by API.
     * GET /data/cities
     *
     * @param Request $request
     * @return JSON
     */
    public function getCities(Request $request)
    {
        $search = $request->get('val');
        $countryCode = $request->get('country_code');
        if( (!$search || strlen($search) < 3 ) && !$countryCode ){
            $response = $this->createBasicResponse(-1, 'fill_at_least_3_letters_for_autocomplete_or_use_country_code');
            return response()->json(['resource' => $response]);
        }
        $cities = \App\Models\City::select(['city', '_id', 'country_code']);
        if($search){
            $cities = $cities->where('city', 'like', '%' . $val . '%');
        }
        if($countryCode){
            $cities = $cities->where('country_code', strtoupper($countryCode));
        }
        $cities = $cities->get();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'listing_of_cities'
            ],
            'cities' => $cities
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get all countries supported by API.
     * GET /data/countries
     *
     * @return JSON
     */
    public function getCountries()
    {
        $countries = \App\Models\Country::all();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'listing_of_cities'
            ],
            'countries' => $countries
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get all languages supported by API.
     * GET /data/languages
     *
     * @return JSON
     */
    public function getLanguages()
    {
        $languages = \App\Models\Language::where('is_active', 1)->get();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'list_of_languages'
            ],
            'languages' => $languages
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get all languages supported by TTS API.
     * GET /data/tts-languages
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
                    'text' => 'no_tts__engine_enabled_1'
                ]
            ];
            return response()->json(['resource' => $response]);
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'languages__3'
            ],
            'languages' => $voices
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get all call routes supported by API.
     * GET /data/call-routes
     *
     * @return JSON
     */
    public function getCallRoutes(Request $request)
    {
        $routes = \App\Models\Country::select([
            '_id',
            'code',
            'name',
            'customer_price',
            'sms_customer_price as sms_price',
            'phonenumber_prefix',
            'original_name',
            'phonenumber_example',
            'verification_call_language_code',
        ])
            ->where('customer_price', '!=', 0)
            ->orderByRaw('cast(phonenumber_prefix as unsigned) ASC')
            ->get();

        $ip = $request->ip();

        if(config('app.SHOULD_USE_GEOIP')) {
            $countryCode = strtolower(trim(@geoip_country_code_by_name($ip)));

        }
        else {
            $countryCode = 'am';
        }

       // $countryCode = 'am';

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'list__of_routes'
            ],
            'routes' => $routes,
            'countryCode' => $countryCode
        ];
        return response()->json(['resource' => $response]);
    }

    
    /**
     * Get dashboard data.
     * GET /data/dashboard
     * 
     * @return JSON
     */
    public function getDashboard()
    {
        $user = \Auth::user();
        $userRepo = new \App\Services\UserService();
        $savedMessages = $user->campaigns()
            ->where('status', 'saved')->count(\DB::raw('DISTINCT repeat_batch_grouping'));
        
        $scheduledMessages = $user->campaigns()
            ->where('status', 'scheduled')->count(\DB::raw('DISTINCT repeat_batch_grouping'));
        
        $retainedStandardBalance = $userRepo->getRetainedBalance($user);
        $retainedGiftBalance = $userRepo->getRetainedGiftBalance($user);
        $retainedBalance = $retainedGiftBalance + $retainedStandardBalance;

        $sentMessages = $user->phonenumbers()
            ->voiceMessage()
            ->where('status', '!=', 'IN_PROGRESS')
            ->count();

        $deliveredMessages = $user->phonenumbers()
            ->voiceMessage()
            ->where('status',  'SUCCEED')
            ->count();

        $liveTransferCount = $user->calls()->where('call_status', 'TRANSFER')->count();

        $callbackCount = $user->phonenumbers()->whereHas('actions', function($query){
            $query->where('call_status', 'CALLBACK_REQUESTED');
        })->count();
        
        $blacklistCount = $user->phonenumbers()->whereHas('actions', function($query){
            $query->where('call_status', 'DONOTCALL_REQUESTED');
        })->count();

        $phoneBookCount = $user->addressBookContacts()->count();
        $callerIdsCount = $user->numbers()->count();
        $messageTemplateCounts = $user->files()->where('is_template', 1)->count();

        if($sentMessages == 0){
            $deliveryRate = 0;
        } else{
            $deliveryRate = round( ($deliveredMessages/$sentMessages)*100, 2);
        }

        $clickToCallSnippetsCount = $user->snippets()->count();

        //$load = sys_getloadavg()[0];
        //\Log::info('load at the end is - ' . $load);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'dashboard_data'
            ],
            'saved_messages' => $savedMessages,
            'scheduled_messages' => $scheduledMessages,
            'retained_balance' => $retainedBalance ? $retainedBalance : 0,
            'sent_messages' => $sentMessages,
            'delivered_messages' => $deliveredMessages,
            'deliver_rate' => $deliveryRate,
            'phonebook_count' => $phoneBookCount,
            'caller_ids_count' => $callerIdsCount,
            'message_templates_count' => $messageTemplateCounts,
            'transfer_count' => $liveTransferCount,
            'callback_count' => $callbackCount,
            'clickToCall_snippets_count' => $clickToCallSnippetsCount,
            'blacklist_count' => $blacklistCount
        ];
        return response()->json(['resource' => $response]);
    }


    /**
     * Get clients tax data using IP.
     * GET /data/tax-data
     * 
     * @param Request $request
     * @return JSON
     */
    public function getTaxData(Request $request)
    {
        $user = \Auth::user();
        $countryCode = $user->country_code ? $user->country_code : $user->caller_id_country_code;
        $countryCode = strtoupper($countryCode);
        // $countryCode = 'ES';
        $vatJson = json_decode( file_get_contents( public_path() . '/rates.json' ), 1);
        $taxData = [];
        if(isset($vatJson['rates'][$countryCode])){
            $taxData = $vatJson['rates'][$countryCode];
        } else{
            $taxData = ['standard_rate' => 0];
        }
        $taxData['fixed_standard_rate'] = $taxData['standard_rate'];
        //$taxData = ['standard_rate' => 0];

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'users_tax__data'
            ],
            'taxData' => $taxData
        ];
        return response()->json(['resource' => $response]);
    }

    public function getCurrentCountry(Request $request)
    {
        $ip = $request->ip();

        if(config('app.SHOULD_USE_GEOIP')) {
            $countryCode = strtolower(trim(@geoip_country_code_by_name($ip)));

        }
        else {
            $countryCode = 'am';
        }


        $response = [
            'error' => [
                'no' => 0,
                'text' => 'list__of_routes'
            ],
           'countryCode' =>  $countryCode,
           'countryCode' =>  false

        ];
        return response()->json(['resource' => $response]);
    }



    public function getDownloadToken() {

        $user = \Auth::user();

        $existDownload =  \App\Models\Download::where('_id', $user->_id)->first();
        $download = new \App\Models\Download;
        $download->user_id = $user->_id;
        $download->token = str_random(50);

        if($download->save() and !$existDownload) {

            $response = [
                'error' => [
                    'no' => 0,
                    'text' => ''
                ],

                'token' =>  $download->token,

            ];
        } else {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => ''
                ],

                'token' =>  $download->token,

            ];
        }


        return response()->json(['resource' => $response]);

    }

    public function getYoutubeTitles(Request $request) {

        $ids = json_decode($request->name);
        $local = $request->lang;

        $titles = \App\Services\YoutubeService::getTitles($ids, $local);

        if($titles) {
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => ''
                ],
                'titles' => $titles
            ];

            return response()->json(['resource' => $response]);
        }

        $response = [
            'error' => [
                'no' => -10,
                'text' => ''
            ],

        ];

        return response()->json(['resource' => $response]);
    }

    public function getYoutubePlaylists(Request $request)
    {
        $language = $request->lang;
        $final = $this->youtubeService->getPlaylists($language);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'success',
            ],
            'playlists' => $final
        ];

        return response()->json(['resource' => $response]);
    }


    public function getServerTimezone(Request $request)
    {
        $now = \Carbon\Carbon::now();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'success',
            ],
            'timezone' =>$now
        ];

        return response()->json(['resource' => $response]);
    }


}
