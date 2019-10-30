<?php
namespace App\Http\Controllers\ClickToCall;

use App\Http\Controllers\Controller;
use App\Models\Phonenumber;
use App\Models\Snippet;
use App\Services\CampaignDbService;
use App\Services\CampaignService;
use App\Services\RequestRateLimitService;
use App\Services\SnippetService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    /**
     * Create a new instance of ApiController class
     *
     * @param SnippetService $snippetRepo
     * @return void
     */
    public function __construct(Request $request, SnippetService $snippetRepo, RequestRateLimitService $requestRateLimitRepo)
    {
        //$this->middleware(['snippet']);
        $this->snippetRepo = $snippetRepo;
        $this->requestRateLimitRepo = $requestRateLimitRepo;
        $this->referrer = $request->headers->get('referer');

    }

    protected $referrer;

    /**
     * Create a new message for click-to-call
     * POST /ctc/create-message
     *
     * @param Request $request
     * @return JSON
     */
    public function postCreateMessage(Request $request, CampaignService $campaignRepo, CampaignDbService $campaignDbRepo)
    {
        $offset = $request->get('offset');

        $userDateTime = Carbon::now()->setTimezone('UTC')->addHour($offset);

        $customDay = $userDateTime->format('l');
        $customHour = $userDateTime->format('H');
        $customMin = $userDateTime->format('i');

        $token = $request->get('token');
        $recipient = $request->get('recipient');
        $callWeekDay = $request->get('callburn_week_day', $customDay);
        $callHour = $request->get('callburn_hour', $customHour);
        $callMinute = $request->get('callburn_minute', $customMin);
        $status = $request->get('status');
        $siteLanguage = $request->get('site_language', null);
        $snippet = $this->snippetRepo->getSnippetByToken($token);

        $fullDate = $request->get('full_date');
        $ipAddress = $request->ip();

        if (!$snippet) {
            $response = $this->createBasicResponse(-1, 'snippet_not_exists');
            return response()->json(['resource' => $response]);
        }
        $isAvailableTime = true;
        if (!$this->isAvailableTime($snippet, $fullDate, $callWeekDay, $callHour, $callMinute, $offset)) {
            $isAvailableTime = false;
        }

        $validationResponse = $campaignDbRepo->isValidNumber($recipient);

        if (!$validationResponse['finalNumber']) {
            $response = $this->createBasicResponse(-4, 'phonenumber_is_not_valid_or_not_supported');
            return response()->json(['resource' => $response]);
        }

        $tariff = $validationResponse['detectedTariff'];
        $recipient = $validationResponse['finalNumber'];

        if ($snippet->addressBookGroup) {
            $addressBookContact = $snippet->user->addressBookContacts()->where('phone_number', $recipient)->first();

            if ($addressBookContact) {
                try {
                    $addressBookContact->groups()->attach($snippet->addressBookGroup->_id);
                } catch (\Exception $e) {

                }
            } else {
                $addressBookContact = new \App\Models\AddressBookContact;
                $addressBookContact->user_id = $snippet->user->_id;
                $addressBookContact->phone_number = $recipient;
                $addressBookContact->tariff_id = $tariff->_id;
                $addressBookContact->save();
                $addressBookContact->groups()->attach($snippet->addressBookGroup->_id);
            }
        }

        if (!$this->requestRateLimitRepo->canCallRecipient($snippet->_id, $recipient, $ipAddress)) {
            $response = $this->createBasicResponse(-6, 'daily_max_limit_expired');
            return response()->json(['resource' => $response]);
        }

        $firstScheduleDate = null;
        $schedulationData = null;
        if ($status == 'scheduled') {
            //$firstScheduleDate =  Carbon::parse('next ' . $callWeekDay);
            $firstScheduleDate = Carbon::createFromFormat('d/m/Y', $fullDate);

            $firstScheduleDate->hour = $callHour;
            $firstScheduleDate->minute = $callMinute;

            $firstScheduleDate = $firstScheduleDate->format('Y-m-d H:i:s');

            $firstScheduleDate = Carbon::createFromFormat('Y-m-d H:i:s', $firstScheduleDate, 'UTC')->addHour(-$offset)->setTimezone(Carbon::now()->tzName);

            if (Carbon::now()->format('l') == $callWeekDay) {

                $firstScheduleDateToday = Carbon::parse($callWeekDay);
                $firstScheduleDateToday->hour = $callHour;
                $firstScheduleDateToday->minute = $callMinute;
                $firstScheduleDateToday = $firstScheduleDateToday->format('Y-m-d H:i:s');
                $firstScheduleDateToday = Carbon::createFromFormat('Y-m-d H:i:s', $firstScheduleDateToday, 'UTC')->addHour(-$offset)->setTimezone(Carbon::now()->tzName);
                if ($firstScheduleDateToday->gt(Carbon::now())) {

                    $firstScheduleDate = $firstScheduleDateToday;
                }
            }

            if ($this->isAnotherSchedulationExists($snippet, $firstScheduleDate)) {
                $response = $this->createBasicResponse(-5, 'schedulation_already_reserved');
                return response()->json(['resource' => $response]);
            }
        }

        $phonenumberToadd = [
            'ip_address' => $ipAddress,
            'phone_no' => $recipient,
            'tariff_id' => $tariff->_id,
            'user_id' => $snippet->user_id,
            'is_from_not_eu_to_eu' => false,
            'first_scheduled_date' => $firstScheduleDate,
            'action_type' => 'VOICE_MESSAGE',
            'snippet_id' => $snippet->_id,
            'site_language' => $siteLanguage,
        ];

        if ($status == 'scheduled') {
            $phonenumberToadd['is_call_scheduled'] = 1;
        }
        if (!$isAvailableTime) {
            $phonenumberToadd['is_pending'] = true;
            $phonenumberToadd['status'] = 'OUT_OF_DATE';
        }

        try {
            $phonenumberObject = Phonenumber::create($phonenumberToadd);
            $this->requestRateLimitRepo->inrementCallerInfo($snippet->_id, $recipient, $ipAddress);
            if (!$isAvailableTime) {
                $response = $this->createBasicResponse(-3, 'time_is_out_of_ranges');
                return response()->json(['resource' => $response]);
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $response = $this->createBasicResponse(-7, 'something_went_wrong');
            $response['message'] = $e->getMessage();
            return response()->json(['resource' => $response])->header('Access-Control-Allow-Origin', '*');
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'created',
            ],
            'phonenumber_id' => $phonenumberObject->_id,
        ];
        return response()->json(['resource' => $response])->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Show the page with the snippet
     * This can be used for the clients who does not have any snippet
     *
     * @param string $subdomain
     * @return view
     */
    public function getShowCallburnHostedSnippet(Request $request, $snippetid = null)
    {
        $languages = \App\Models\Language::select('code')->where('is_active', 1)->get()->toArray();

        $lang = trim(strtolower(substr($request->header('Accept-Language'), 0, 2)));

        if (!in_array($lang, array_column($languages, 'code'))) {

            $lang = \Config::get('app.locale');
        }

        \App::setLocale($lang);

        $isEUMember = \App\Services\CookieService::checkEU($request);

        if (!$snippetid) {
            return view('clickToCall.put-snippet-id');
        }
        $snippet = $this->snippetRepo->getSnippetBySubdomain($snippetid);
        if (!$snippet) {
            return response()->view('errors.404', ['isEUMember' => $isEUMember, 'tab' => ''], 404);
        }
        return view('clickToCall.hosted', ['type' => 1, 'token' => $snippet->api_token, 'url' => config('app.url'), 'isEUMember' => $isEUMember]);
    }

    /**
     * Check if the time is available for snippet's custom range
     *
     * @param Snippet $snippet
     * @param string $weekDay
     * @param string $hour
     * @param string $minute
     * @return bool
     */

    private function isAvailableTimeForCustom($snippet, $fullDate, $weekDay, $hour, $minute, $offset)
    {
        $dateRange = $snippet->custom_date_times;

        $holidayMode = $snippet->holiday_mode;
        $holidayModeIsActive = $snippet->is_active_holiday_mode;

        if ($holidayMode and $holidayModeIsActive) {
            $holidayModeStart = Carbon::createFromFormat('d/m/Y', trim(explode('-', $holidayMode)[0]));
            $holidayModeEnd = Carbon::createFromFormat('d/m/Y', trim(explode('-', $holidayMode)[1]));
        } else {
            $holidayModeStart = null;
            $holidayModeEnd = null;
        }

        $customerTimezone = $snippet->user->timezone ? $snippet->user->timezone : 'UTC';

        $weekDatesCustom = SnippetService::createCustomSnippetDateRange($dateRange, $offset, $customerTimezone);

        $firstScheduleDate = Carbon::createFromFormat('d/m/Y', $fullDate);

        $WeekDays = array_keys($weekDatesCustom);
        $weekDay = $firstScheduleDate->format("l");
        //If today is not in allowed days return false

        if (!in_array($weekDay, $WeekDays)) {

            return false;
        }

        foreach ($weekDatesCustom as $day => $dates) {
            foreach ($dates as $date) {

                $startDateArray = explode(':', $date->start);
                //Parse the date range end
                $endDateArray = explode(':', $date->end);
                //Get the carbon date from the schdeuled date

                $scheduledDate = Carbon::createFromTime($hour, $minute, 0, 'UTC')->addHour(-$offset); // user time to utc

                // dd($scheduledDate);
                //Get the carbon date from the start allowed date
                $startCarbonDate = Carbon::createFromTime(intval($startDateArray[0]), intval($startDateArray[1]), 0, $customerTimezone)->setTimezone('UTC');
                //Get the carbon date from the end allowed date
                $endCarbonDate = Carbon::createFromTime(intval($endDateArray[0]), intval($endDateArray[1]), 0, $customerTimezone)->setTimezone('UTC');

                //return true if the scheduled date is between allowed ranges

                if ($holidayMode and $holidayModeIsActive and $scheduledDate->between($holidayModeStart, $holidayModeEnd)) {
                    return false;
                }

                if ($scheduledDate->between($startCarbonDate, $endCarbonDate)) {
                    return true;
                }
            }
        }

        //and false in other case .
        return false;

    }

    /**
     * Check if the time is available for snippet's custom range
     *
     * @param Snippet $snippet
     * @return bool
     */

    private function checkAvailableTimeByCustomer($snippet)
    {
        $dateRange = $snippet->custom_date_times;
        $timezone = $snippet->user->timezone;

        $nowByCustomer = Carbon::now()->setTimezone($timezone);

        $offset = $nowByCustomer->offset / 3600;
        $dayByCustomer = $nowByCustomer->format('l');

        $isActiveHolidayMode = $snippet->is_active_holiday_mode;
        // if holiday mode is active
        if ($isActiveHolidayMode) {
            $holidayMode = explode('-', $snippet->holiday_mode);
            $holidayModeStart = Carbon::createFromFormat('d/m/Y', trim($holidayMode[0]));
            $holidayModeEnd = Carbon::createFromFormat('d/m/Y', trim($holidayMode[1]));
            //return false if the $nowByCustomer is between holiday mode ranges
            if ($nowByCustomer->between($holidayModeStart, $holidayModeEnd)) {
                return false;
            }
        }

        $weekDatesCustom = SnippetService::createCustomSnippetDateRange($dateRange, $offset, $timezone, true);

        $WeekDays = array_keys($weekDatesCustom);

        //If today is not in allowed days return false
        if (!in_array($dayByCustomer, $WeekDays)) {
            return false;
        }

        foreach ($weekDatesCustom[$dayByCustomer] as $date) {
            $startDateArray = explode(':', $date->start);
            //Parse the date range end
            $endDateArray = explode(':', $date->end);
            //Get the carbon date from the start allowed date
            $startCarbonDate = Carbon::createFromTime(intval($startDateArray[0]), intval($startDateArray[1]), 0, $timezone);
            //Get the carbon date from the end allowed date
            $endCarbonDate = Carbon::createFromTime(intval($endDateArray[0]), intval($endDateArray[1]), 0, $timezone);
            //return true if the $nowByCustomer is between allowed ranges
            if ($nowByCustomer->between($startCarbonDate, $endCarbonDate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the time is available for snippet
     *
     * @param Snippet $snippet
     * @param string $weekDay
     * @param string $hour
     * @param string $minute
     * @return bool
     */

    private function isAvailableTime($snippet, $fullDate, $weekDay, $hour, $minute, $offset)
    {
        if ($snippet->custom_date_times) {
            return $this->isAvailableTimeForCustom($snippet, $fullDate, $weekDay, $hour, $minute, $offset);
        }

        $customerTimezone = $snippet->user->timezone ? $snippet->user->timezone : 'UTC';

        //Parse allowed dates of the snippet
        $allowedDateTimes = json_decode($snippet->allowed_date_times);
        //Get the allowed week days of the snippet
        $allowedWeekDays = $allowedDateTimes->weekDays;
        //If today is not in allowed days return false

        if (!in_array($weekDay, $allowedWeekDays)) {return false;}
        //Parse the date range start
        $startDateArray = explode(':', $allowedDateTimes->dateRangeStart);
        //Parse the date range end
        $endDateArray = explode(':', $allowedDateTimes->dateRangeEnd);
        //Get the carbon date from the schdeuled date
        $scheduledDate = Carbon::createFromTime($hour, $minute, 0, 'UTC')->addHour(-$offset); // user time to utc
        // dd($scheduledDate);
        //Get the carbon date from the start allowed date
        $startCarbonDate = Carbon::createFromTime(intval($startDateArray[0]), intval($startDateArray[1]), 0, $customerTimezone)->setTimezone('UTC');
        //Get the carbon date from the end allowed date
        $endCarbonDate = Carbon::createFromTime(intval($endDateArray[0]), intval($endDateArray[1]), 0, $customerTimezone)->setTimezone('UTC');

        return $scheduledDate->between($startCarbonDate, $endCarbonDate);
    }

    /**
     * Check if there is any other call scheduled in the same time
     *
     * @param Snippet $snippet
     * @param Carbon $firstScheduleDate
     * @return bool
     */
    private function isAnotherSchedulationExists($snippet, $firstScheduleDate)
    {
        $scheduledCall = $snippet->ctcPhonenumbers()
            ->where('first_scheduled_date', $firstScheduleDate)
            ->first();
        return $scheduledCall ? true : false;
    }

    protected function createBasicResponse($code, $text)
    {
        return [
            'error' => [
                'no' => $code,
                'text' => $text,
            ],
        ];
    }

    public function postMainJavascript(Request $request, SnippetService $snippetService, $token, $offset)
    {
        $offset = -$offset;

        $local = $request->get('local');
        $site_language = $request->get('site_language');

        $browserLanguage = $request->get('language', 'en');

        $snippet = Snippet::isPublished(1)->with(['user'])->notBlocked()->with(['country', 'callerId'])->where('api_token', $token)->first();

        if (!$snippet) {
            $response = [
                'error' => [
                    'no' => -10,
                    'text' => 'snippet_not_found',
                ],

            ];

            return response()->json(['resource' => $response]);
        }

        if (!$snippet->is_active) {
            $response = [
                'error' => [
                    'no' => -20,
                    'text' => 'This snippet was disabled. Go on Callburn.com to activate it.',
                ],
            ];

            return response()->json(['resource' => $response], 403);
        }

        if ($snippet->image_name) {
            $snippet->image_url = $this->getAmazonS3Url($snippet->image_name);
        }

        $countries = $snippet->country()->select(['code', 'phonenumber_prefix'])->get()->toArray();

        $customerTimezone = $snippet->user->timezone ? $snippet->user->timezone : 'UTC';

        $dateRange = $snippet->allowed_date_times;

        if ($dateRange) {
            $weekDates = SnippetService::createSnippetDateRange($dateRange, $offset, $customerTimezone);
        } else {
            $weekDates = [];
        }

        $dateRange = $snippet->custom_date_times;

        if ($dateRange) {
            $weekDatesCustom = SnippetService::createCustomSnippetDateRange($dateRange, $offset, $customerTimezone, false, false);
        } else {
            $weekDatesCustom = [];
        }

        $weekDates = array_merge($weekDates, $weekDatesCustom);

        if ($snippet->user->balance < 0.20) {
            $today = Carbon::now()->format('l');
            unset($weekDates[$today]);
        }

        $snippet->allowed_date_times = $weekDates;

        $variables = [
            'countries' => json_encode($countries),
            'imagePath' => url('/assets/clickToCall/'),
            'baseUrl' => url(''),
            'snippet' => json_encode($snippet),
            'token' => $token,
            'type' => $request->get('type'),
            'logoImage' => '',
            'local' => $local,
            'site_language' => $site_language,
        ];

        $libJavascript = view('clickToCall.lib', $variables)->render();
        $mainJavascript = view('clickToCall.main', $variables)->render();

        $momentTimezones = file_get_contents(public_path('assets/clickToCall/moment-timezone-with-data.min.js')); //put the right path of
        $momentWithLocales = file_get_contents(public_path('assets/clickToCall/moment-with-locales.min.js')); //put the right path of
        //$obfuscator = new \App\Libs\Obfuscator\Packer($mainJavascript, 'Normal', true, false, true);
        //$mainJavascript = $obfuscator->pack();

        if ($request->get('changeTimezone')) {
            return response()->json([
                'updatedDate' => $snippet->allowed_date_times,
            ])->header('Access-Control-Allow-Origin', '*');
        }

        return response()->json([

            'script' => $momentWithLocales . $momentTimezones . $libJavascript . $mainJavascript,
            'styles' => file_get_contents(public_path('assets/clickToCall/css/helper.css')),
        ])->header('Access-Control-Allow-Origin', '*');

    }

    public function postCheckSnippetDateRange(Request $request, SnippetService $snippetService, $token)
    {
        $snippet = Snippet::isPublished(1)->isActive(1)->with('user')->notBlocked()->where('api_token', $token)->first();

        if (!$snippet) {
            $response = $this->createBasicResponse(-10, 'snippet_not_exist');
            return response()->json(['resource' => $response]);
        }

        if ($snippet->user->balance < 0.20) {
            $response = $this->createBasicResponse(-5, 'low_balance');
            return response()->json(['resource' => $response]);
        }

        $check = $this->checkAvailableTimeByCustomer($snippet);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'allowed',
            ],
            'check' => $check,
        ];

        return response()->json(['resource' => $response]);
    }

    protected function getAmazonS3Url($fileName)
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $s3 = \Storage::disk('s3');

        $s3Client = \Aws\S3\S3Client::factory(array(
            'credentials' => array(
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ),
            'region' => config('filesystems.disks.s3.region'),
            'signature' => 'v4',
            'version' => 'latest',
        ));

        $command = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $fileName,
        ]);
        return (string) $s3Client->createPresignedRequest($command, '+30 minutes')->getUri();
    }
}
