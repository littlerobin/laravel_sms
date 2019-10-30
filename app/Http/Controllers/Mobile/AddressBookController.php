<?php namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Mobile\MobileController as Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\CampaignDbService;
use App\Helper;
use Illuminate\Support\Facades\DB;
use Validator;

class AddressBookController extends Controller{

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


    private $userService;

    /**
     * Create a new instance of AdderssBookApiController class.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(
        Request $request,
        UserService $userService
        )
    {
        $this->userService = $userService;
        $this->device = $request->get('device', 'WEBSITE');
        $this->activityLogRepo = new \App\Services\ActivityLogService();
    }

    /**
     * Synchronize mobile contacts to website avoiding duplicates.
     * POST /addressbook/sync-contacts
     *
     * @param Request $request
     * @return JSON
     */
    public function postSyncContacts(Request $request, CampaignDbService $campaignDbRepo)
    {
        $apiKey = $request->get('key');
        $checkedKey = $this->checkKey($apiKey);
        if(!$checkedKey){
            $response = $this->createBasicResponse(-10, 'Invalid or expired API key');
            return response()->json($response);
        }
        $user = $checkedKey['user'];

        $contacts = $request->get('contacts');

        $duplicates = 0;
        $success = 0;
        $notSupported = 0;
        $countries = \App\Models\Country::all();
        foreach ($contacts as $contact) {
            $phonenumber = isset($contact['phonenumber']) ? $contact['phonenumber'] : 'INVALID';
            $contactName = isset($contact['name']) ? $contact['name'] : '';
            //$label =  isset($contact['label']) ? $contact['label'] : NULL;
            $validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
            if(!$validationResponse['finalNumber']){
                $notSupported++;
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];
            $finalNumber = $validationResponse['finalNumber'];

            try{
                $contactData = [
                    'name' => $contactName,
                    'phone_number' => $finalNumber,
                    'tariff_id' => $tariff->_id,
                    'type' => $campaignDbRepo->checkIsPhoneNumberMobile($finalNumber),
                    'user_id' => $user->_id,
                ];
                \App\Models\AddressBookContact::create($contactData);
                $success++;
            } catch(\Exception $e){
                $duplicates++;
            }
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'Importing status',
            ],
            'success' => $success,
            'invalid' => $notSupported,
            'duplicate' => $duplicates
        ];
        return response()->json($response);
    }

    public function syncMobileContacts($token,Request $request,CampaignDbService $campaignDbRepo) {

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'Importing status',
            ],
            'success' => 0,
            'invalid' => 0,
            'duplicate' => 0
        ];

        \Log::info('-------------------Request START------------------------');
        \Log::info('URL: ' . $request->fullUrl());
        \Log::info('Method: ' . $request->getMethod());
        \Log::info('-------------------Request END------------------------');

        $contacts = $request->get('contacts');
        $user = $this->userService->getUserByUploadContactToken($token);

        if(!$user) {
            $now = Carbon::now();
            $notificationData = [
                'text' => 'Error When Uploading Contacts <br> Invalid or expired token.',
                'user_id' => $user->_id,
                'created_at' => $now,
                'updated_at' => $now
            ];

            \DB::table('notifications')->insert($notificationData);

            $response = $this->createBasicResponse(-10, 'Invalid or expired token.');
            return response()->json($response);
        }

        if(!$contacts || count($contacts) == 0) {
            $now = Carbon::now();
            $notificationData = [
                'text' => 'Error When Uploading Contacts <br> Contacts are missing.',
                'user_id' => $user->_id,
                'created_at' => $now,
                'updated_at' => $now
            ];

            \DB::table('notifications')->insert($notificationData);

            $response = $this->createBasicResponse(-20, 'Contacts are missing.');
            return response()->json($response);
        }

        $countries = \App\Models\Country::all();

        \DB::beginTransaction();

        $groupData = [
            'user_id' => $user->_id,
            'name' => 'uploaded_mobile'.date('YmdHis'),
            'in_progress' => 0,
            'should_put_three_asterisks' => 0,
            'type' => 'CREATED_AUTOMATICALLY',
        ];

        $groupModel = \App\Models\AddressBookGroup::create($groupData);

        foreach ($contacts as $contact) {
            $phoneNumber = isset($contact['phonenumber']) ? $contact['phonenumber'] : 'INVALID';
            $contactName = isset($contact['name']) ? $contact['name'] : '';
            $validationResponse = $campaignDbRepo->isValidNumber($phoneNumber, $user, $countries);
            if(!$validationResponse['finalNumber']){
                $response['invalid']++;
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];
            $finalNumber = $validationResponse['finalNumber'];

            $contactModel = null;
            try{
                //ToDo check number type before add
                $contactData = [
                    'name' => $contactName,
                    'phone_number' => $finalNumber,
                    'tariff_id' => $tariff->_id,
                    'user_id' => $user->_id,
                ];
                $contactModel = \App\Models\AddressBookContact::create($contactData);
                $response['success']++;
            } catch(\Exception $e){
                $response['duplicate']++;
            }
            if($contactModel) {
                'uploaded_mobile';
                $contactModel->groups()->attach($groupModel->_id);
            }
        }

        if($response['success']) {

            \DB::commit();
        } else {
            \DB::rollback();
        }

        $now = Carbon::now();
        $notificationData = [
            'text' => 'Mobile Contacts Upload Finished: '.
                '<br> Uploaded  - ' . $response['success'] .
                '<br> Invalid   - ' . $response['invalid'].
                '<br> Duplicate - ' . $response['duplicate'],
            'user_id' => $user->_id,
            'created_at' => $now,
            'updated_at' => $now
        ];

        \DB::table('notifications')->insert($notificationData);

        \Log::info('-------------------Response START------------------------');
        \Log::info(print_r($response,true));
        \Log::info('-------------------Response END------------------------');

        $response = [
            'status' => 'Success'
        ];
        return response()->json($response);
    }
}