<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\ArchivedPhonenumberAction;
use App\Models\Country;
use App\Models\PhonenumberAction;
use App\Models\UserSmsCost;
use App\Services\CampaignDbService;
use App\Services\UserService;
use App\Services\NotificationService;
use App\Services\CampaignService;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Writer;
use Illuminate\Support\Facades\DB;
use App\Services\PhonenumberService;


class PhonenumbersController extends WebsiteController
{
    /**
     * Create a new instance of CampaignsController class.
     *
     * @return void
     */
    public function __construct(UserService $userRepo,
                                CampaignService $campaignRepo,
                                PhonenumberService $phonenumberRepo
    )
    {
        $this->middleware('jwt.headers');
        $this->middleware('jwt.auth', ['except' => ['getExportStatistics']]);
        $this->userRepo = $userRepo;
        $this->campaignRepo = $campaignRepo;
        $this->notificationRepo = new NotificationService();
        $this->phonenumberRepo = $phonenumberRepo;
    }

    /**
     * Add phonenumbers manually.
     * POST /phonenumbers/add-numbers
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddPhonenumbers(Request $request)
    {
        $phonenumbers = $request->get('phonenumbers');
        $phonenumbers = str_replace(chr(13), ',', $phonenumbers);
        $phonenumbers = str_replace(chr(10), ',', $phonenumbers);
        $phonenumbers = str_replace(';', ',', $phonenumbers);
        $phonenumbers = str_replace('|', ',', $phonenumbers);
        $phonenumbers = str_replace(' ', '', $phonenumbers);
        $phonenumbers = explode(',', $phonenumbers);

        $isCampaignCreate = $request->get('is_campaign_create', false);
        $defaultGroupName = 'created_on ' . date('Y-m-d H:i:s');

        $finalNumbers = [];
        foreach ($phonenumbers as $phonenumber) {
            $finalNumbers[] = ['phonenumber' => $phonenumber, 'name' => $request->get('group', null), 'group' => $request->get('group', null)];
        }

        $response = $this->checkNumbersValidation($finalNumbers, $isCampaignCreate);

        return response()->json(['resource' => $response]);
    }

    /**
     * Add phonenumber comment.
     * POST /phonenumbers/add-comment
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddComment(Request $request){
        $phonenumberId = $request->get('phonenumberId');
        $phonenumber = $this->phonenumberRepo->getPhonenumberById($phonenumberId);
        $archivedPhonenumber = $this->phonenumberRepo->getArchivedPhonenumberById($phonenumberId);
        $data = [
            'comment' => json_encode($request->get('comments')),
        ];
        if($phonenumber){
            $updated = $this->phonenumberRepo->updatePhonenumber($phonenumberId,$data);
        }else if($archivedPhonenumber){
           $updated =  $this->phonenumberRepo->updateArchivedPhonenumber($phonenumberId,$data);
        }
        if(isset($updated) && $updated){
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => 'Comment Successfully Added',
                ],
                'comment' => $request->get('comments'),
            ];
        }else{
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'Something Went Wrong!',
                ],
            ];
        }

        return response()->json(['resource' => $response]);

    }


    /**
     * Upload phonenumbers for campaign
     * POST /phonenumbers/upload-phonenumbers-for-campaign
     *
     * @param Request $request
     * @return JSON
     */
    public function postUploadPhonenumbersForCampaign(Request $request)
    {
        $file = $request->file('file');
        $isCampaignCreate = true;
        if (!$file) {
            $response = [
                'error' => [
                    'no' => -11,
                    'text' => 'file_is_not__specified',
                ],
            ];
            return response()->json(['resource' => $response]);
        }
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['csv', 'txt', 'xls', 'xlsx'])) {
            $response = [
                'error' => [
                    'no' => -12,
                    'text' => 'not_supported__file_format_1',
                ],
            ];
            return response()->json(['resource' => $response]);
        }
        $path = $file->getRealPath();
        $csv = Reader::createFromPath($path);
        $delimeterArray = $csv->detectDelimiterList(1, [" ", "\r\n", "\r", "\n", "\t", ",", ";"]);
        foreach ($delimeterArray as $delim) {
            $csv->setDelimiter($delim);
            break;
        }
        //$fetchedList = $csv->fetchAll();
        $phonenumbers = [];
        foreach ($csv as $list) {
            if (isset($list[0])) {
                //$phonenumbers[] = $list[0];
                $phonenumbers[] = [
                    'phonenumber' => $list[0],
                    'group' => isset($list[1]) ? $list[1] : '',
                    'name' => isset($list[2]) ? $list[2] : '',
                ];
            }
        }
        $isCampaignCreate = $request->get('is_campaign_create', false);
        $response = $this->checkNumbersValidation($phonenumbers, $isCampaignCreate);
        return response()->json(['resource' => $response]);
    }

    /**
     * Upload phonenumbers file.
     * POST /phonenumbers/validate-phone-numbers
     *
     * @param Request $request
     * @return JSON
     */
    public function postValidatePhoneNumbers(Request $request)
    {
        $user = \Auth::user();
        $countries = Country::all();
        $columns = $request->get("columns", []);
        $contacts = $request->get("contacts", []);
        $rows = [];
        foreach ($contacts as $contactIndex => $contact) {
            if ($request->ignoreFirstLine && $contactIndex == 0) {
                continue;
            }
            $row = [];
            foreach ($columns as $index => $column) {
                $columnName = $column['id'];
                if (isset($contact[$index])) {
                    if ($columnName == "phone") {
                        $result = app(CampaignDbService::class)->isValidNumber($contact[$index], $user, $countries);
                        if (isset($result['finalNumber']) && $result['finalNumber']) {
                            $row['phone'] = $result['finalNumber'];
                            $row['flag_code'] = $result['detectedTariff']['country']['code'];
                        } else {
                            $row['phone'] = $contact[$index];
                            $row['flag_code'] = null;
                        }
                    } elseif ($columnName == "name") {
                        $row['name'] = $contact[$index];
                    } elseif ($columnName == "group") {
                        $row['group'] = $contact[$index];
                    }
                }
            }
            if (!empty($row)) {
                $rows[] = $row;
            }
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'system_is_validating_your_file',
            ],
            'contacts' => $rows,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Upload phonenumbers file.
     * POST /phonenumbers/text-phonenumber
     *
     * @param Request $request
     * @return JSON
     */
    public function postTextPhonenumbers(Request $request)
    {
        //dd($request->text);
//        substr_count()
        $delimiters = array(
            ',' => 0,
            ';' => 0,
            "|" => 0,
            '.' => 0
        );
            foreach ($delimiters as $delimiter => $count) {
                $delimiters[$delimiter] += substr_count($request->text,$delimiter);
            }

        $maxs = array_keys($delimiters, max($delimiters));
        $delimiter = $maxs[0];
        $user = \Auth::user();
//        $delimiter = $request->get('selectDelimiter', ',');
//        $delimiter = ($delimiter == "automatic" || strlen($delimiter) > 1) ? "," : $delimiter;

        $rows = explode("\n", $request->text);
        $destinationPath = public_path() . '/uploads/csv/';
        $fileName = str_random() . '.csv';
        $path = $destinationPath . $fileName;

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter($delimiter);
        foreach ($rows as $row) {
            $csv->insertOne($row);
        }
        file_put_contents($path, $csv->__toString());
        chmod($path, 0777);
        $reader = Reader::createFromPath($path);
        $reader->setDelimiter($delimiter);
        $rows = $reader->setLimit(15)->fetchAll();
        $total = count($reader->fetchAll());
        $columnsCount = count($reader->fetchOne());

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'system_is_validating_your_file',
            ],
            'fileName' => $fileName,
            'originalFileName' => null,
            'columnsCount' => $columnsCount,
            'total' => $total,
            'rows' => $rows,
            'delimiter' => $delimiter
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Upload phonenumbers file.
     * POST /phonenumbers/upload-phonenumber-first-step
     *
     * @param Request $request
     * @return JSON
     */
    public function postUploadPhonenumbersFirstStep(Request $request)
    {
        $user = \Auth::user();
        $file = $request->file('file');
        if (!$file) {
            $response = [
                'error' => [
                    'no' => -11,
                    'text' => 'file_is_not_specified__1',
                ],
            ];
            return response()->json(['resource' => $response]);
        }
        $extension = $file->getClientOriginalExtension();
        $originalFileName = $file->getClientOriginalName();
        if (!in_array($extension, ['csv', 'txt', 'xls', 'xlsx'])) {
            $response = [
                'error' => [
                    'no' => -12,
                    'text' => 'not_supported__file_format_1',
                ],
            ];
            return response()->json(['resource' => $response]);
        }
        $destinationPath = public_path() . '/uploads/csv/';
        $fileName = str_random() . '.' . $extension;
        $csvFileName = str_random() . '.csv';
        $file->move($destinationPath, $fileName);
        $path = $destinationPath . $fileName;

        $csvPath = $destinationPath . $csvFileName;
        chmod($path, 0777);

        $inputFileType = \PHPExcel_IOFactory::identify($path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType)->setIncludeCharts();

        $delimiter = $this->getDelimiter($path,$inputFileType);
        if($inputFileType == 'CSV') {
            $objReader->setDelimiter($delimiter);
        }

        $objPHPExcel = $objReader->load($path);
        unlink($path);

        $objPHPExcel->getActiveSheet()->getStyle($objPHPExcel->setActiveSheetIndex(0)->calculateWorksheetDimension())
        ->getNumberFormat()
        ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $sheets = $objPHPExcel->getActiveSheet()->toArray();
        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        $csv->setDelimiter($delimiter);
        foreach ($sheets as $sheet) {
            $sheet = array_filter($sheet);
            $csv->insertOne($sheet);
        }
        //dd($csv,$sheets);
        file_put_contents($csvPath, $csv->__toString());
        chmod($csvPath, 0777);
        $reader = Reader::createFromPath($csvPath);
        $reader->setDelimiter($delimiter);
        $rows = $reader->setLimit(15)->fetchAll();

        $total = count($reader->fetchAll());
        $columnsCount = count($reader->fetchOne());
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'system_is_validating_your_file',
            ],
            'fileName' => $csvFileName,
            'originalFileName' => $originalFileName,
            'columnsCount' => $columnsCount,
            'total' => $total,
            'rows' => $rows,
            'delimiter' => $delimiter
        ];
        return response()->json(['resource' => $response]);
    }

    private function getDelimiter($path,$inputFileType) {
        $delimiter = '.';
        if($inputFileType == 'CSV'){
            $delimiters = array(
                ',' => 0,
                ';' => 0,
                "|" => 0,
                '.' => 0
            );
            $handle = fopen($path, "r");
            $lineCount = 0;
            while(! feof($handle))
            {
                $firstLine = fgets($handle);
                foreach ($delimiters as $key => $value) {
                    $delimiters[$key] += substr_count($firstLine,$key);
                }
                $lineCount++;
            }
            arsort($delimiters);
            $detected = false;

            foreach ($delimiters as $key => $value) {
                if($value == $lineCount || $value == ($lineCount -1)) {
                    $delimiter = $key;
                    $detected = true;
                }
            }
            if(!$detected) {
                $max = array_keys($delimiters,max($delimiters));
                $delimiter = $max[0];
            }
        }
        return $delimiter;
    }

    /**
     * Upload phonenumbers file.
     * POST /phonenumbers/upload-phonenumbers
     *
     * @param Request $request
     * @return JSON
     */
    public function postUploadPhonenumbers(Request $request)
    {
        $columnss = $request->get("columns", []);
        $fileName = $request->fileName;
        $originalFileName = $request->originalFileName;
        $user = \Auth::user();
        $groupObject = null;
        if($request->delimiter){
            $delimiter = $request->delimiter;
        }
        else{
            $delimiter = '.';
        }
        if ($request->selectGroup) {
            switch ($request->selectGroup) {
                case 'selected_group':
                    $groupId = $request->selectedGroup["id"];
                    $groupObject = $user->addressBookGroups()->where('address_book_groups._id', $groupId)->first();
                    break;
                case 'new_group':
                    $groupObject = $user->addressBookGroups()->create([
                        'name' => $request->groupName,
                        'type' => 'CREATED_BY_USER_MANUALLY',
                        'in_progress' => 1,
                    ]);
                    break;
                default:
                    break;
            }
        }

        if (is_null($groupObject)) {
            $groupObject = $user->addressBookGroups()->create([
                'name' => $originalFileName,
                'type' => 'CREATED_BY_USER_FROM_FILE',
                'in_progress' => 1,
            ]);
        }

        $queueData = [
            'type' => 'PHONEBOOK',
            'file_path' => $fileName,
            'user_id' => $user->_id,
            'group_id' => $groupObject->_id,
            'original_file_name' => $originalFileName,
        ];

        $this->notificationRepo->createOnContactsFileUpload($queueData, $groupObject);

        $queueJob = \App\Models\BackgroundJob::create($queueData);
        $this->dispatch(new \App\Jobs\ValidateNumbers($queueJob, $columnss, $request->ignoreFirstLine, $delimiter));
//        $campaignDbRepo = new CampaignDbService();
//        $groupsChecked = [];
//        $ignoreFirstLine = $request->ignoreFirstLine;
//        //dd($columns);
//        $columns = [];
//
//        try{
//            foreach ($columnss as $index => $column) {
//                if (isset($column["id"]) && !empty($column["id"])) {
//                    $column['index'] = $index;
//                    array_push($columns, $column);
//                }
//            }
//            //dd($columns);
//            $notificationRepo = new NotificationService();
//        }catch (\Exception $e){
//            //dd($e->getMessage());
//            \Log::info($e->getMessage());
//        }
//
//        try {
//
//            $user = $queueJob->user;
//            $destinationPath = public_path() . '/uploads/csv/';
//            $path = $destinationPath . $queueJob->file_path;
//            $groupObject = $queueJob->group;
//            $groupObjectId = $groupObject->_id;
//            $invalidContactsCount = 0;
//            $validContactsCount = 0;
//            $totalRows = file($path);
//            $totalRowsCount = count($totalRows);
//            unset($totalRows);
//            $countries = \App\Models\Country::all();
//            $iteration = 0;
//            $isAllHaveGroup = false;
//            $reader = Reader::createFromPath($path);
//            $reader->setDelimiter($delimiter);
//            //$objReader->setDelimiter(',');
//            $groupIndex = null;
//            $phoneNumberIndex = null;
//            $nameIndex = null;
//            $invalidContacts = [];
//
//            foreach ($columns as $column) {
//                switch ($column['id']) {
//                    case 'name':
//                        $nameIndex = $column['index'];
//                        break;
//                    case 'phone':
//                        $phoneNumberIndex = $column['index'];
//                        break;
//                    case 'group':
//                        $groupIndex = $column['index'];
//                        break;
//
//                    default:
//                        $phoneNumberIndex = 0;
//                        break;
//                }
//            }
//            //dd($phoneNumberIndex);
//            $results = $reader->fetch();
//            if ($ignoreFirstLine) {
//                $totalRowsCount--;
//                $invalidContactsCount--;
//            }
//
//            $data = [];
//            $importationId = mt_rand(100000, 999999);
//
//
//            $counter = 0;
//            foreach ($results as $_fetchedList) {
//
//                $counter++;
//                if (!isset($_fetchedList[$phoneNumberIndex]) || empty($_fetchedList[$phoneNumberIndex])) {
//                    continue;
//                }
//                $phonenumber = $_fetchedList[$phoneNumberIndex];
//                $groupName = (!is_null($groupIndex) && isset($_fetchedList[$groupIndex])) ? $_fetchedList[$groupIndex] : '';
//
//                if (!$groupName) {
//                    $groupName = $groupObject->name;
//                }
//                $currentGroupId = null;
//
//                if ($groupName && isset($this->groupsChecked[$groupName])) {
//                    $currentGroupId = $groupsChecked[$groupName]->_id;
//                } elseif ($groupName) {
//                    $tempGroup = $user->addressBookGroups()->where('name', $groupName)
//                        ->first();
//                    if (!$tempGroup) {
//                        $tempGroup = $user->addressBookGroups()->create([
//                            'name' => $groupName,
//                            'is_custom_name' => 1,
//                            'in_progress' => 1,
//                            'created_at' => Carbon::now(),
//                        ]);
//                    }
//                    $currentGroupId = $tempGroup->_id;
//                    $groupsChecked[$groupName] = $tempGroup;
//                }
////                dd($_fetchedList,$groupIndex);
////                if (isset($_fetchedList[$groupIndex])) {
////                    $isAllHaveGroup = true;
////                }
//                $contactName = (!is_null($nameIndex) && !empty($_fetchedList[$nameIndex])) ? preg_replace('/\s+/', ' ', $_fetchedList[$nameIndex]) : null;
//                $validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
//
//                if (!$validationResponse['finalNumber']) {
//                    $invalidContactsCount++;
//                    if ($ignoreFirstLine && $counter == 1) {
//                        continue;
//                    }
//                    $invalidContacts[] = [
//                        'user_id' => $user->_id,
//                        'group_id' => $currentGroupId,
//                        'upload_id' => null,
//                        'file_importation_id' => $importationId,
//                        'phone_number' => $phonenumber,
//                        'name' => $contactName,
//                    ];
//
//                    continue;
//                } else {
//                    $validContactsCount++;
//                }
//                $tariff = $validationResponse['detectedTariff'];
//
//                $data[] = [
//                    'user_id' => $user->_id,
//                    'group_id' => $currentGroupId,
//                    'tariff_id' => $tariff->_id,
//                    'file_importation_id' => $importationId,
//                    'phone_number' => $validationResponse['finalNumber'],
//                    'name' => $contactName,
//                    'type' => $campaignDbRepo->checkIsPhoneNumberMobile($validationResponse['finalNumber'],$user)
//                ];
//                if (count($data) == 500) {
//                    \DB::table('temp_contacts')->insert($data);
//                    $data = [];
//                    $iteration++;
//                }
//            }
//            $leftContactsCount = count($data);
//            if ($leftContactsCount > 0) {
//                \DB::table('temp_contacts')->insert($data);
//            }
//
//
//            $currentPercentage = ($leftContactsCount + ($iteration * 500)) * 100 / $totalRowsCount;
//            $queueJob->complete_percentage = $currentPercentage;
//            $queueJob->save();
//
//            $tempData = [
//                'valid' => $validContactsCount,
//                'invalid' => $invalidContactsCount,
//            ];
//
//            $queueJob->data = json_encode($tempData);
//            $queueJob->complete_percentage = 100;
//            $queueJob->status = 'FINISHED';
//            $queueJob->save();
//
//            $job = $queueJob;
//            $groupId = $queueJob->group_id;
//
//            $notificationRepo->deleteOnContactsFileUploadFinish($groupId, $user);
//            $notificationRepo->createOnContactsFileUploadSuccess($job);
//            //dd($groupObject);
//            event(new \App\Events\UserDataUpdated([
//                'user_id' => $user->_id]));
//
//            if ($isAllHaveGroup || $validContactsCount == 0) {
//                $groupObject->delete();
//            } else {
//                $groupObject->in_progress = 0;
//                $groupObject->save();
//            }
//            if ($groupsChecked && count($groupsChecked)) {
//                foreach ($groupsChecked as $group) {
//                    $group->in_progress = 0;
//                    $group->save();
//                }
//            }
//            \DB::table('invalid_contacts')->insert($invalidContacts);
//            $pdo = DB::connection()->getPdo();
//
//            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
////dd(array($importationId,$groupObjectId,$invalidContactsCount));
//            $testData = DB::select("call CALLBURN_PARSECONTACTS( ?, 0, ?, ? )",array($importationId,$groupObjectId,$invalidContactsCount));
//
//            event(new \App\Events\AddressBookUpdated([
//                'user_id' => $user->_id]));
//
//            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
//            //return;
//        } catch (\Exception $e) {
//            //dd($e);
//            \Log::info('Error In ValidateNumbers');
//            \Log::error($e);
//
//            $queueJob->status = 'FAILED';
//            $queueJob->save();
//
//            $job = $queueJob;
//            $groupId = $queueJob->group_id;
//            $notificationRepo->deleteOnContactsFileUploadFinish($groupId, $user);
//            $notificationRepo->createOnContactsFileUploadFailure($job);
//            event(new \App\Events\UserDataUpdated([
//                'user_id' => $user->_id]));
//
//            if ($groupObject) {
//                $groupObject->delete();
//            }
//            foreach ($groupsChecked as $group) {
//                $group->delete();
//            }
//        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'system_is_validating_your_file',
            ],
            'queue_job' => $queueJob,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting only valid numbers, with calculation of maximum cost .
     * POST /phonenumbers/add-numbers-and-calculate-cost-manually
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddNumbersAndCalculateCostManually(Request $request)
    {
        $user = Auth::user();
        $fileId = $request->get('file_id');
        $phonenumbers = $request->get('data');
        $campaignType = $request->get('type');
        $smsText = $request->get('sms_text');
        $phonenumbers = array_unique($phonenumbers);

        if (count($phonenumbers) > 500) {
            $response = $this->createBasicResponse(-15, 'forbidden');
            return response()->json(['resource' => $response], 403);
        }

        $response = $this->calculateCostForNumbers($user, $fileId, $phonenumbers,$campaignType,$smsText);
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting only valid numbers, with calculation of maximum cost using contact ids.
     * POST /phonenumbers/add-numbers-and-calculate-cost-contacts
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddNumbersAndCalculateCostContacts(Request $request)
    {
        $user = Auth::user();
        $fileId = $request->get('file_id');
        $contactIds = array_keys(array_filter($request->get('data', [])));
        $contacts = $user->addressBookContacts()->whereIn('_id', $contactIds)->with('tariff')->get();
        $finalNumbersArray = [];
        foreach ($contacts as $contact) {
            if ($contact->tariff) {
                $finalNumbersArray[] = '+' . $contact->phone_number;
            }
        }
        $response = $this->calculateCostForNumbers($user, $fileId, $finalNumbersArray);
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting only valid numbers, with calculation of maximum cost using group ids.
     * POST /phonenumbers/add-numbers-and-calculate-cost-groups
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddNumbersAndCalculateCostGroups(Request $request)
    {
        $user = Auth::user();
        $userSmsTariffs = UserSmsCost::where('user_id',$user->_id)->get();
        $fileId = $request->get('file_id');
        $campaignType = $request->get('type');
        $smsText = $request->get('sms_text');
        $groupIds = array_keys(array_filter($request->get('data', [])));

        //$shouldUseAllContacts = in_array('ALL', $groupIds);
        $shouldUseAllContacts = $request->get('all_contacts');

        $file = $user->files()->find($fileId);

        $length = $file ? $file->length : 0;
        if ($length < 20) {
            $length = 20;
        }
        $maxCost = 0;
        $maxGiftCost = 0;
        $receipentsCount = 0;
        $maxCostWithSms = 0;
        $smsNotSupported = 0;

        $minimumMargin = $user->bonus_criteria;
        $availableGiftBalance = $user->bonus;

        $contacts = $user->addressBookContacts();

        if (!$shouldUseAllContacts) {
            $contacts = $contacts->whereHas('groups', function ($query) use ($groupIds) {
                $query->whereIn('address_book_groups._id', $groupIds);
            }, '>', 0);
        }
        $contacts = $contacts->selectRaw('count(*) as count, tariff_id, user_id, type , tariffs.prefix, tariffs.country_id, tariffs.best_margin, countries.customer_price ,countries.sms_customer_price, countries.best_sms_isp_id')
            ->groupBy('tariff_id')
            ->leftJoin('tariffs', 'address_book_contacts.tariff_id', '=', 'tariffs._id')
            ->leftJoin('countries', 'tariffs.country_id', '=', 'countries._id')
            ->get();

        if($campaignType != 'SMS') {

            $userCanAll = true;
            foreach ($contacts as $contact) {
                $receipentsCount += $contact->count;
                $tempCost = $contact->customer_price * $length * $contact->count / 60;

                if ($contact->best_margin >= $minimumMargin && $availableGiftBalance > 0) {
                    $leftAmount = $availableGiftBalance - $tempCost;
                    if ($leftAmount < 0) {
                        $maxGiftCost += $availableGiftBalance;
                        $availableGiftBalance = 0;
                        $maxCost += abs($leftAmount);
                    } else {
                        $maxGiftCost += $tempCost;
                        $availableGiftBalance -= $tempCost;
                    }
                } else {
                    if ($contact->best_margin < $minimumMargin) {
                        $userCanAll = false;
                    }
                    $maxCost += $tempCost;
                }
            }

            if ($maxCost > 0 && !$userCanAll) {
                $maxCost += $maxGiftCost;
                $maxGiftCost = 0;
            }

            if (!$file) {
                $response = [
                    'error' => [
                        'no' => -5,
                        'text' => 'file_not_exist',
                    ],
                    'max_cost' => 0,
                    'max_cost_with_sms' => 0,
                    'max_gift_cost' => 0,
                    'sending_time' => 0,
                    'recipients_count' => $receipentsCount,
                ];

                return response()->json(['resource' => $response]);
            }
        }

        if($campaignType == 'VOICE_WITH_SMS' || $campaignType == 'SMS') {
            $smsLength = ceil(strlen($smsText)/160);
            foreach ($contacts as $contact) {
                // todo check the phonenumber type
//                if($contact->type == 'MOBILE' || $contact->type == 'FIXED_LINE_OR_MOBILE') {
                    $smsCost = $contact->sms_customer_price * $smsLength;
                    if($userSmsTariffs->count()) {
                        $currentCountryTariff = null;
                        foreach ($userSmsTariffs as $userSmsTariff) {
                            if($userSmsTariff->country_id == $contact->_id) {
                                $currentCountryTariff = $userSmsTariff;
                            }
                        }
                        if($currentCountryTariff) {
                            $smsCost = $currentCountryTariff->cost * ceil(strlen($smsText)/160);
                        }
                    }

                    if($contact->best_sms_isp_id) {
                        $maxCostWithSms += $smsCost * $contact->count;
                        if(!$smsCost) {
                            $smsNotSupported++;
                        }
                    } else {
                        $smsNotSupported++;
                    }

                    if($campaignType == 'SMS') {
                        $receipentsCount += $contact->count;
                    }

//                } else {
//                    $smsNotSupported++;
//                }
            }
            $maxCostWithSms += $maxCost;
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'max__cost_calculated__1',
            ],
            'max_cost' => $maxCost,
            'max_cost_with_sms' => $maxCostWithSms,
            'max_gift_cost' => $maxGiftCost,
            'sending_time' => 3,
            'sms_not_supported' => $smsNotSupported,
            'recipients_count' => $receipentsCount,
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing phonenumbers from phonebook
     * POST /phonenumbers/remove-from-phonebook
     *
     * @param Request $request
     * @return JSON
     */
    public function postRemoveFromPhonebook(Request $request)
    {
        $user = Auth::user();
        $phonenumberIds = array_keys(array_filter($request->get('phonenumber_ids')));
        $phonenumbers = $user->phonenumbers()->select('phone_no')->whereIn('_id', $phonenumberIds)->get()->toArray();
        $archivedPhonenumbers = $user->archivedPhonenumbers()->select('phone_no')->whereIn('original_id', $phonenumberIds)->get()->toArray();
        $phonenumbers = array_merge($phonenumbers,$archivedPhonenumbers);
        $user->addressBookContacts()->whereIn('phone_number', $phonenumbers)->delete();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'Removed__1',
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Export campaign statistics
     * POST /phonenumbers/export-statistics
     *
     * @param Request $request
     * @return JSON
     */
    public function postExportStatistics(Request $request)
    {
        $user = Auth::user();

        $campaignId = $request->get('campaign_id');
        $fileFormat = $request->get('file_format', 'csv');
        $fileFormat = in_array($fileFormat, config('export_files.allowed_for_export'))
            ? $fileFormat
            : 'csv';
        $statuses = json_decode($request->get('statuses', "[]"), 1);
        $actions = json_decode($request->get('actions', "[]"), 1);
        $searchKey = $request->get('phonenumber');
        $otherData = [
            'statuses' => $statuses,
            'actions' => $actions,
            'searchKey' => $searchKey,
        ];
        \App\Models\EmailNotification::create([
            'user_id' => $user->_id,
            'campaign_id' => $campaignId,
            'other_data' => json_encode($otherData),
            'type' => 'MESSAGE_COMPLETED',
            'file_format' => $fileFormat
        ]);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'statistics_will_be_emailed_to_you',
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get phonenumbers of the campaign.
     * GET /phonenumbers/campaign-phonenumbers
     *
     * @param integer $id
     * @param Request $request
     * @return JSON
     */
    public function postCampaignPhonenumbers(Request $request)
    {
        $user = Auth::user();
        $campaignId = $request->get('campaign_id');
        $page = $request->get('page', 0);
        $orderField = $request->get('order_field');
        $order = $request->get('order');
        $statuses = json_decode($request->get('statuses', "[]"), 1);
        $actions = json_decode($request->get('actions', "[]"), 1);
        $searchKey = $request->get('phonenumber');
        $only = $request->get('only');
        $except = $request->get('except');
        $schedulationId = $request->get('schedulation_id');
        $type = isset($request->type) ? $request->get('type') : null;
        $search_name = isset($request->search_name) ? $request->get('search_name') : null;
        $response = $this->getPhonenumbers(
            $user, //$user,
            $campaignId, //$campaignId,
            $page, //$page,
            30, //$perPage,
            $orderField, //$orderField,
            $order, //$order,
            $statuses, //$statuses,
            $actions, //$actions,
            $searchKey, //$searchKey,
            false,
            $only,
            $except,
            $schedulationId,
            $type,
            $search_name
            ); //$ifWithoutPagination
//        dd($response);
        return response()->json(['resource' => $response]);
    }

    /**
     * Get phonenumbers of the batch campaign.
     * GET /phonenumbers/batch-campaign-phonenumbers/{code}
     *
     * @param string $code
     * @param Request $request
     * @return JSON
     */
    public function getBatchCampaignPhonenumbers($code, Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page');
        $perPage = 7;
        $orderField = $request->get('order_field');
        $order = $request->get('order');
        $status = $request->get('status');
        $campaigns = $user->campaigns()->where('repeat_batch_grouping', $code);

        if ($status) {
            $campaigns = $campaigns->whereHas('batchPhonenumber', function ($query) use ($status) {
                $query->where('status', $status);
            }, '=', 1);
        }

        $count = $campaigns->count();
        $campaigns = $campaigns->skip($page * $perPage)->take($perPage)
            ->orderBy($orderField, $order)->with(['batchPhonenumber', 'voiceFile'])->get();

        $response = [
            'error' => [
                'no' => 0,
                'message' => 'campaigns__with__phonenumbers',
            ],
            'phonenumbers' => $campaigns,
            'page' => $page + 1,
            'phonenumbers_count' => $count,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Check if the array of numbers passed validation .
     *
     * @param array $phonenumbers
     * @return JSON
     */
    private function checkNumbersValidation($phonenumbers, $isCampaignCreate)
    {
        //set_time_limit(600);
        $user = Auth::user();
        $campaignDbRepo = new \App\Services\CampaignDbService();
        $allCount = count($phonenumbers);
        $success = 0;
        $duplicates = 0;
        $invalid = 0;
        $notSupported = 0;
        $respArray = [];
        $countries = \App\Models\Country::all();

        //  dd($phonenumbers);
        foreach ($phonenumbers as $phonenumberArray) {
            $phonenumber = $phonenumberArray['phonenumber'];
            $name = isset($phonenumberArray['name']) ? $phonenumberArray['name'] : '';
            $group = isset($phonenumberArray['group']) ? $phonenumberArray['group'] : 'addressbook_import_added_manually (' . date('Y-m-d H:i:s') . ')';
            $groupObject = ['name' => $group, '_id' => uniqid()];
            $validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
            if (!$validationResponse['finalNumber']) {
                $invalid++;
                $respArray[] = ['number' => $phonenumber, 'status' => 'not_supported', 'name' => $name, 'group' => $groupObject];
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];
            $finalNumber = $validationResponse['finalNumber'];
            //$isCampaignCreate = $request->get('is_campaign_create');
            if (!$isCampaignCreate) {
                $isExist = $user->addressBookContacts()->where('phone_number', $finalNumber)->where('tariff_id', $tariff->_id)->first();
                if ($isExist) {
                    $duplicates++;
                    $respArray[] = ['number' => $finalNumber, 'status' => 'duplicate', 'tariff' => $tariff, 'name' => $name, 'group' => $groupObject];
                } else {
                    $respArray[] = ['number' => $finalNumber, 'status' => 'success', 'tariff' => $tariff, 'name' => $name, 'group' => $groupObject];
                    $success++;
                }
            } else {
                $respArray[] = ['number' => $finalNumber, 'status' => 'success', 'tariff' => $tariff, 'name' => $name, 'group' => $groupObject];
                $success++;
            }
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'phonenumbers__with__statuses',
            ],
            'count' => $allCount,
            'success' => $success,
            'duplicates' => $duplicates,
            'invalid' => $invalid,
            'not_supported' => $notSupported,
            'phonenumbers' => $respArray,
        ];
        return $response;
    }

    /**
     * Get calculate cost for user's caller id.
     * GET /phonenumbers/calculate-cost-for-caller-id
     *
     * @param string $code
     * @param Request $request
     * @return JSON
     */

    public function getCalculateCostForCallerId(Request $request)
    {
        $user = Auth::user();
        $fileId = $request->get('file_id');
        $callerId = $request->get('caller_id');
        $phoneNumbers = ['+' . $callerId];
        $response = $this->calculateCostForNumbers($user, $fileId, $phoneNumbers);

        return response()->json(['resource' => $response]);
    }

    /**
     * Calculate max cost for numbers
     */
    private function calculateCostForNumbers($user, $fileId, $phoneNumbers,$campaignType = null,$smsText = null)
    {
        $campaignDbRepo = new \App\Services\CampaignDbService();
        $finalNumbersArray = [];
        $responseArray = [];
        $statusesArray = [];
        $countries = \App\Models\Country::all();
        $userSmsTariffs = UserSmsCost::where('user_id',$user->_id)->get();

        foreach ($phoneNumbers as $phonenumber) {
            $validationResponse = $campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
            if (!$validationResponse['finalNumber']) {
                $statusesArray[] = ['number' => $phonenumber, 'status' => 'not_supported'];
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];
            $finalNumber = $validationResponse['finalNumber'];
            $numberType = $validationResponse['detectedType'];
            $finalNumbersArray[] = ['phonenumber' => $finalNumber, 'tariff' => $tariff, 'type' => $numberType];
            $statusesArray[] = ['number' => '+' . $finalNumber, 'status' => 'success'];
            $responseArray[] = '+' . $finalNumber;

        }

        $maxCost = 0;
        $maxCostWithSms = 0;
        $maxGiftCost = 0;
        $smsNotSupported = 0;

        if(!$campaignType || $campaignType != 'SMS') {
            $file = $user->files()->find($fileId);
            $length = $file ? $file->length : 0;
            $userCanAll = true;
            foreach ($finalNumbersArray as $finalNumber) {
                if ($length < 20) {
                    $length = 20;
                }
                $cost = $finalNumber['tariff']['country']['customer_price'] * $length / 60;
                if ($this->userRepo->canUseGift($finalNumber, $user, $maxGiftCost, $cost)) {
                    $maxGiftCost += $cost;
                } else {
                    if (!$user->bonus_criteria || $user->bonus_criteria > $finalNumber['tariff']['best_margin']) {
                        $userCanAll = false;
                    }
                    $maxCost += $cost;
                }
            }

            if ($maxCost > 0 && !$userCanAll) {
                $maxCost += $maxGiftCost;
                $maxGiftCost = 0;
            }
            if (!$file) {
                $response = [
                    'error' => [
                        'no' => -5,
                        'text' => 'file_not_exist',
                    ],
                    'max_cost' => 0,
                    'max_cost_with_sms' => 0,
                    'max_gift_cost' => 0,
                    'sending_time' => 0,
                    'phonenumbers' => $responseArray,
                    'statuses' => $statusesArray,
                ];
                return $response;
            }
        }

        if($campaignType && $campaignType != 'VOICE_MESSAGE') {
            $smsLength = ceil(strlen($smsText)/160);

            foreach ($finalNumbersArray as $finalNumber) {

                // todo check the phonenumber type
//                if($finalNumber['type'] == 'MOBILE' || $finalNumber['type'] == 'FIXED_LINE_OR_MOBILE') {

                    $smsCost = $finalNumber['tariff']['country']['sms_customer_price'] * $smsLength;
                    if($userSmsTariffs->count()) {
                        $currentCountryTariff = null;
                        foreach ($userSmsTariffs as $userSmsTariff) {
                            if($userSmsTariff->country_id == $finalNumber['tariff']['country']['_id']) {
                                $currentCountryTariff = $userSmsTariff;
                            }
                        }
                        if($currentCountryTariff) {
                            $smsCost = $currentCountryTariff->cost * ceil(strlen($smsText)/160);
                        }
                    }

                    if($finalNumber['tariff']['country']['best_sms_isp_id']) {
                        $maxCostWithSms += $smsCost;
                        if(!$smsCost) {
                            $smsNotSupported++;
                        }
                    } else {
                        $smsNotSupported++;
                    }

//                } else {
//                    $smsNotSupported++;
//                }
            }
            $maxCostWithSms += $maxCost;
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'max__cost_calculated__1',
            ],
            'max_cost' => $maxCost,
            'max_cost_with_sms' => $maxCostWithSms,
            'max_gift_cost' => $maxGiftCost,
            'sms_not_supported' => $smsNotSupported,
            'sending_time' => 3,
            'phonenumbers' => $responseArray,
            'statuses' => $statusesArray,
        ];
        return $response;
    }

    /**
     * Get phonenumbers for export
     *
     *
     */
    private function getPhonenumbers(
        $user,
        $campaignId,
        $page,
        $perPage,
        $orderField,
        $order,
        $statuses,
        $actions,
        $searchKey,
        $ifWithoutPagination,
        $only = null,
        $except = null,
        $schedulationId,
        $type,
        $search_name
    )
    {

        if (!$page) {
            $page = 0;
        }
        if (!$perPage) {
            $perPage = 30;
        }
        if (!$orderField) {
            $orderField = '_id';
        }
        if (!$order) {
            $order = 'ASC';
        }

        $phonenumberNeededData = [
            '_id', 'user_id', 'tariff_id', 'campaign_id', 'schedulation_id', 'to_be_called_at', 'delivered_on',
            'phone_no', 'status', 'action_type','should_put_three_asterisks', 'status', 'created_at', 'updated_at', 'total_duration', 'total_cost', 'comment'
        ];

        if ($campaignId) {
            $phonenumbers = $user->phonenumbers()
                ->select($phonenumberNeededData)
                ->where('campaign_id', $campaignId);

            $phonenumberNeededData[0] = 'original_id as _id';
            array_push($phonenumberNeededData, 'original_id');

            $archivedPhonenumbers = $user->archivedPhonenumbers()
                ->select($phonenumberNeededData)
                ->where('campaign_id', $campaignId);

            if($only) {
                if($only == 'status') {
                    $phonenumbers = $phonenumbers->where($only,'SUCCEED');
                    $archivedPhonenumbers = $archivedPhonenumbers->where($only,'SUCCEED');
                } else {
                    $archivedPhonenumbers = $archivedPhonenumbers->whereNotNull($only);
                    $phonenumbers = $phonenumbers->whereNotNull($only);
                }
            }

            if(gettype($schedulationId) == "integer"){
                $phonenumbers = $phonenumbers->where("schedulation_id",$schedulationId);
                $archivedPhonenumbers = $archivedPhonenumbers->where("schedulation_id",$schedulationId);
            }

            if($type == 'sms'){
                $phonenumbers = $phonenumbers->where("status", 'CALL_FAILED_SMS_SUCCEED');
                $archivedPhonenumbers = $archivedPhonenumbers->where("status",'CALL_FAILED_SMS_SUCCEED');
            }
            else if($type == 'callmessage'){
                $phonenumbers = $phonenumbers->where("status",'SUCCEED');
                $archivedPhonenumbers = $archivedPhonenumbers->where("status",'SUCCEED');
            }

            if(ctype_digit($search_name)){
                $phonenumbers = $phonenumbers->where('phone_no', 'like', '%' . $search_name . '%');
                $archivedPhonenumbers = $archivedPhonenumbers->where('phone_no', 'like', '%' . $search_name . '%');
            }else if ($search_name != null){
                $phonenumbers = $phonenumbers->whereHas('contacts', function ($q) use ($search_name){
                   $q->where('name' , 'like' ,  $search_name.'%');
                });
                $archivedPhonenumbers = $archivedPhonenumbers->whereHas('contacts', function ($q) use ($search_name){
                    $q->where('name' , 'like' , $search_name.'%');
                });
            }
            if($except) {
                if($except == 'status') {
                    $phonenumbers = $phonenumbers->whereNotIn($except,['SUCCEED']);
                    $archivedPhonenumbers = $archivedPhonenumbers->whereNotIn($except,['SUCCEED']);
                } else {
                    $phonenumbers = $phonenumbers->whereNull($except);
                    $archivedPhonenumbers = $archivedPhonenumbers->whereNotNull($except);
                }
            }
            $campaignn = $this->campaignRepo->getCampaignByPK($campaignId);

            $campaign = $user->campaigns()->select([
                '_id', 'snippet_id', 'campaign_name', 'caller_id', 'type', 'sms_text',
                'campaign_voice_file_id', 'callback_digit_file_id', 'do_not_call_digit_file_id', 'grouping_type',
                'created_from', 'disable_answering_machine_detection', 'status', 'total_phonenumbers_loaded',
                'is_prototype', 'created_at', 'updated_at', 'amount_spent', 'is_archived', 'replay_digit', 'callback_digit', 'do_not_call_digit' , 'transfer_digit'
            ])
                ->with([
                    'schedulations' => function ($query) {
                        $query->where('is_finished', 0)->select([
                            '_id', 'campaign_id', 'scheduled_date',
                        ]);
                    },
                    'voiceFile' => function ($query) {
                        $query->select([
                            '_id', 'type', 'tts_text',
                            'length', 'orig_filename',
                            'user_id', 'map_filename',
                        ]);
                    },
                    $campaignn['is_archived'] == 1 ? 'archivedSuccessPhonenumbers' : 'successPhonenumbers',
                    //'successPhonenumbers',
                    'callsCount',
                    'smsCount',
                    'archivedSmsCount',
                    //'totalPhonenumbers',
                    $campaignn['is_archived'] == 1 ? 'archivedTotalPhonenumbers' : 'totalPhonenumbers',
                ])
                ->where('_id', $campaignId)
                ->first();
                if($campaignn['attributes']['is_archived'] == 1){
                    $campaign['total_phonenumbers'] = $campaign['archivedTotalPhonenumbers'];
                    $campaign['success_phonenumbers'] = $campaign['archivedSuccessPhonenumbers'];
                    unset($campaign['archivedTotalPhonenumbers'],$campaign['archivedSuccessPhonenumbers']);
                }
               // dd($campaign);
//            if($campaignn['attributes']['is_archived']){
//                $campaign = $user->campaigns()->select([
//                    '_id', 'snippet_id', 'campaign_name', 'caller_id', 'type', 'sms_text',
//                    'campaign_voice_file_id', 'callback_digit_file_id', 'do_not_call_digit_file_id', 'grouping_type',
//                    'created_from', 'disable_answering_machine_detection', 'status', 'total_phonenumbers_loaded',
//                    'is_prototype', 'created_at', 'updated_at', 'amount_spent', 'is_archived'
//                ])
//                    ->with([
//                        'schedulations' => function ($query) {
//                            $query->where('is_finished', 0)->select([
//                                '_id', 'campaign_id', 'scheduled_date',
//                            ]);
//                        },
//                        'voiceFile' => function ($query) {
//                            $query->select([
//                                '_id', 'type', 'tts_text',
//                                'length', 'orig_filename',
//                                'user_id', 'map_filename',
//                            ]);
//                        },
//                        'archivedSuccessPhonenumbers',
//                        'callsCount',
//                        'smsCount',
//                        'archivedTotalPhonenumbers',
//                    ])
//                    ->where('_id', $campaignId)
//                    ->first();
//                //dd($campaign);
//                $campaign['total_phonenumbers'] = $campaign['archivedTotalPhonenumbers'];
//                $campaign['success_phonenumbers'] = $campaign['archivedSuccessPhonenumbers'];
//
//            }
//            else{
//             $campaign = $user->campaigns()->select([
//                '_id', 'snippet_id', 'campaign_name', 'caller_id', 'type', 'sms_text',
//                'campaign_voice_file_id', 'callback_digit_file_id', 'do_not_call_digit_file_id', 'grouping_type',
//                'created_from', 'disable_answering_machine_detection', 'status', 'total_phonenumbers_loaded',
//                'is_prototype', 'created_at', 'updated_at', 'amount_spent', 'is_archived'
//            ])
//                ->with([
//                    'schedulations' => function ($query) {
//                        $query->where('is_finished', 0)->select([
//                            '_id', 'campaign_id', 'scheduled_date',
//                        ]);
//                    },
//                    'voiceFile' => function ($query) {
//                        $query->select([
//                            '_id', 'type', 'tts_text',
//                            'length', 'orig_filename',
//                            'user_id', 'map_filename',
//                        ]);
//                    },
//                    'successPhonenumbers',
//                    'callsCount',
//                    'smsCount',
//                    'totalPhonenumbers',
//                ])
//                ->where('_id', $campaignId)
//                ->first();
//            }

            //dd($campaign);
            //dd($campaign['attributes']['is_archived']);
        } else {
            $phonenumbers = $user->phonenumbers()->select($phonenumberNeededData);
            array_push($phonenumberNeededData, 'original_id');
            $phonenumberNeededData[0] = 'original_id as _id';
            $archivedPhonenumbers = $user->archivedPhonenumbers()->select($phonenumberNeededData);
        }

        $phonenumbers = $phonenumbers->with('actionsLog','blackList');
        $archivedPhonenumbers = $archivedPhonenumbers->with('actionsLog','blackList');

        $total_count = $phonenumbers->count();
        $total_count += $archivedPhonenumbers->count();

        if ($total_count === 0 && is_null($campaign)) {
            $response = [
                'error' => [
                    'no' => -13,
                    'message' => 'this_campaign_doesnt_exist',
                ],
            ];
            return $response;
        }

        $interactions = [
            'reply' => 0,
            'transfer' => 0,
            'callback' => 0,
            'blacklist' => 0,
        ];
        //dd($campaign->transfer_digit);

        if(!is_null($campaign)) {
            if($campaign->is_archived) {
                    $interactions['transfer'] = \DB::table('archived_phonenumber_actions')
                        ->where('archived_phonenumbers.campaign_id',$campaignId)
                        ->where('archived_phonenumber_actions.call_status','TRANSFER_REQUESTED')
                        ->join('archived_phonenumbers','archived_phonenumber_actions.phonenumber_id','=','archived_phonenumbers.original_id')
                        ->count();


                    $interactions['callback'] = \DB::table('archived_phonenumber_actions')
                        ->where('archived_phonenumbers.campaign_id',$campaignId)
                        ->where('archived_phonenumber_actions.call_status','CALLBACK_REQUESTED')
                        ->join('archived_phonenumbers','archived_phonenumber_actions.phonenumber_id','=','archived_phonenumbers.original_id')
                        ->count();


                    $interactions['reply'] = \DB::table('archived_phonenumber_actions')
                        ->where('archived_phonenumbers.campaign_id',$campaignId)
                        ->where('archived_phonenumber_actions.call_status','REPLAY_REQUESTED')
                        ->join('archived_phonenumbers','archived_phonenumber_actions.phonenumber_id','=','archived_phonenumbers.original_id')
                        ->count();


                    $interactions['blacklist'] = \DB::table('archived_phonenumber_actions')
                        ->where('archived_phonenumbers.campaign_id',$campaignId)
                        ->where('archived_phonenumber_actions.call_status','DONOTCALL_REQUESTED')
                        ->join('archived_phonenumbers','archived_phonenumber_actions.phonenumber_id','=','archived_phonenumbers.original_id')
                        ->count();

            } else {

                    $interactions['transfer'] = \DB::table('phonenumber_actions')
                        ->where('phonenumbers.campaign_id',$campaignId)
                        ->where('phonenumber_actions.call_status','TRANSFER_REQUESTED')
                        ->join('phonenumbers','phonenumber_actions.phonenumber_id','=','phonenumbers._id')
                        ->count();

                    $interactions['callback'] = \DB::table('phonenumber_actions')
                        ->where('phonenumbers.campaign_id',$campaignId)
                        ->where('phonenumber_actions.call_status','CALLBACK_REQUESTED')
                        ->join('phonenumbers','phonenumber_actions.phonenumber_id','=','phonenumbers._id')
                        ->count();

                    $interactions['reply'] = \DB::table('phonenumber_actions')
                        ->where('phonenumbers.campaign_id',$campaignId)
                        ->where('phonenumber_actions.call_status','REPLAY_REQUESTED')
                        ->join('phonenumbers','phonenumber_actions.phonenumber_id','=','phonenumbers._id')
                        ->count();

                    $interactions['blacklist'] = \DB::table('phonenumber_actions')
                        ->where('phonenumbers.campaign_id',$campaignId)
                        ->where('phonenumber_actions.call_status','DONOTCALL_REQUESTED')
                        ->join('phonenumbers','phonenumber_actions.phonenumber_id','=','phonenumbers._id')
                        ->count();
            }
        }
        $phoneNumbersData = $this->checkPhoneNumbers($statuses, $actions, $searchKey, $ifWithoutPagination, $page, $perPage, $orderField, $order, $user, $phonenumbers);
        $archivedPhoneNumbersData = $this->checkPhoneNumbers($statuses, $actions, $searchKey, $ifWithoutPagination, $page, $perPage, $orderField, $order, $user, $archivedPhonenumbers);

        $finalPhoneNumbers = $phoneNumbersData['phonenumbers'];
        $finalPhoneNumbers = $finalPhoneNumbers->merge($archivedPhoneNumbersData['phonenumbers']);

        $count = $archivedPhoneNumbersData['phonenumbers_count'] + $phoneNumbersData['phonenumbers_count'];
        $totalCostOfFiltered = $archivedPhoneNumbersData['total_cost'] + $phoneNumbersData['total_cost'];

        $response = [
            'error' => [
                'no' => 0,
                'message' => 'phonenumbers__of__campaign',
            ],
            'phonenumbers' => collect($finalPhoneNumbers),
            'campaign' => $campaign,
            'page' => $page + 1,
            'phonenumbers_count' => $count,
            'total_phonenumbers_count' => $total_count,
            'interactions' => $interactions,
            'total_cost' => $totalCostOfFiltered ? $totalCostOfFiltered : 0,
            'server_timezone' => config('app.timezone')
        ];

        //dd($response);
       // dd($response['campaign']->successPhonenumbers, 1, $response['campaign']->totalPhonenumbers);
        return $response;
    }

    private function checkPhoneNumbers($statuses, $actions, $searchKey, $ifWithoutPagination, $page, $perPage, $orderField, $order, $user, $phonenumbers,$forArchived = false)
    {
        if ($statuses != null && count($statuses) > 0) {
            $phonenumbers = $phonenumbers->where(function ($query) use ($statuses) {
                $query->orWhere('status', 'NOT_EXISTING_STATUS');
                if (in_array('success', $statuses)) {
                    $query->orWhere('status', 'SUCCEED');
                }
                if (in_array('failed', $statuses)) {
                    $query->orWhereNotIn('status', ['SUCCEED', 'IN_PROGRESS']);
                }
                //Calling in progress
                if (in_array('sent', $statuses)) {
                    $query->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'IN_PROGRESS')
                            ->where(function ($scheduledDateQuery) {
                                $now = Carbon::now();
                                $scheduledDateQuery->whereNull('to_be_called_at')
                                    ->orWhere('to_be_called_at', '<', $now);
                            });
                    });
                }
                //Waiting for schedulations
                if (in_array('IN_PROGRESS', $statuses)) {
                    $query->orWhere(function ($subQuery) {
                        $now = Carbon::now();
                        $subQuery->where('status', 'IN_PROGRESS')
                            ->where('to_be_called_at', '>', $now);
                    });
                }
                if (in_array('IDLE', $statuses)) {
                    $query->orWhere('status', 'IDLE');
                }
                if (in_array('CANT_CALL_DUE_TO_EU', $statuses)) {
                    $query->orWhere('status', 'CANT_CALL_DUE_TO_EU');
                }
            });
        }

        if (count($actions) > 0) {
            $phonenumbers = $phonenumbers->whereHas('actions', function ($query) use ($actions) {
                $query->where('log_type', 'ACTION')->whereIn('call_status', $actions);
            }, '>', 0);
        }

        if ($searchKey) {
            $phonenumbers = $phonenumbers->where('phone_no', 'LIKE', '%' . $searchKey . '%');
        }

        $count = $phonenumbers->count();
        $totalCostOfFiltered = $phonenumbers->sum('total_cost');

        if (!$ifWithoutPagination) {
            $phonenumbers = $phonenumbers->skip($page * $perPage)->take($perPage);
        }

        $phonenumbers = $phonenumbers->orderBy($orderField, $order)
            ->with([
                'tariff' => function ($query) {
                    $query->with([
                        'country' => function ($query) {
                            $query->select([
                                '_id', 'code',
                            ]);
                        },
                    ])
                        ->select([
                            '_id', 'country_id',
                        ]);
                },
                //'totalCost',
                'actionsLog' => function ($query) {
                    $query->whereNotIn('call_status', ['TRANSFER_ENDED', 'TRANSFER_CONNECTED']);
                },
                'calls' => function ($query) {
                    $query->select([
                        '_id', 'phonenumber_id', 'cost', 'duration', 'call_status',
                        'dialled_datetime',
                    ]);
                },
                'smsAction' => function ($query) use($forArchived) {
                if($forArchived) {
                    $query->select([
                        '_id', 'archived_phonenumber_id as phonenumber_id', 'customer_cost as cost', 'status',
                    ]);
                } else {
                    $query->select([
                        '_id', 'phonenumber_id', 'customer_cost as cost', 'status',
                    ]);
                }

                },
                'contacts' => function ($query) use ($user) {
                    $query->where('user_id', $user->_id)->select('_id', 'phone_number', 'should_put_three_asterisks', 'name');
                },
            ]);

        if ($ifWithoutPagination) {
            $finalPhonenumbers = array();
            $phonenumbers->chunk(1000, function ($phonenumbers) use (&$finalPhonenumbers) {
                foreach ($phonenumbers as &$phonenumber) {
                    if ($phonenumber->should_put_three_asterisks && count($phonenumber->actionsLog) == 0) {
                        unset($phonenumber->contacts->phone_number);
                        $phonenumber->phone_no = \App\Services\AddressBookService::addThreeAsterisks($phonenumber->phone_no);
                    }
                    $finalPhonenumbers[] = $phonenumber;
                }
            });
        } else {
            $finalPhonenumbers = $phonenumbers->get();
            foreach ($finalPhonenumbers as &$phonenumber) {
                // foreach ($phonenumber->contacts as &$contact) {
                //     unset($contact->phone_number);
                // }
                if ($phonenumber->should_put_three_asterisks && count($phonenumber->actionsLog) == 0) {
                    unset($phonenumber->contacts->phone_number);
                    $phonenumber->phone_no = \App\Services\AddressBookService::addThreeAsterisks($phonenumber->phone_no);
                }
            }
        }

        $response = [
            'phonenumbers' => $finalPhonenumbers,
            'page' => $page + 1,
            'phonenumbers_count' => $count,
            'total_cost' => $totalCostOfFiltered ? $totalCostOfFiltered : 0,
            'server_timezone' => config('app.timezone')
        ];
        return $response;
    }
}
