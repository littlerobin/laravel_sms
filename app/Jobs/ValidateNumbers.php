<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\InvalidContact;
use App\Services\CampaignDbService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ValidateNumbers extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $backgroundJob;

    private $groupsChecked;
    private $procedureDataForEachGroup;
    private $campaignDbRepo;
    private $columns;
    private $ignoreFirstLine;
    private $notificationRepo;
    public $delimiter;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        \App\Models\BackgroundJob $backgroundJob,
        $columns = [],
        $ignoreFirstLine = false,
        $delimiter
    )
    {
        $this->delimiter = $delimiter;
        $this->backgroundJob = $backgroundJob;
        $this->campaignDbRepo = new CampaignDbService();
        $this->groupsChecked = [];
        $this->procedureDataForEachGroup = [];
        $this->columns = [];
        $this->ignoreFirstLine = $ignoreFirstLine;
        foreach ($columns as $index => $column) {
            if (isset($column["id"]) && !empty($column["id"])) {
                $column['index'] = $index;
                array_push($this->columns, $column);
            }
        }
        $this->notificationRepo = new NotificationService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $user = $this->backgroundJob->user;
            $destinationPath = public_path() . '/uploads/csv/';
            $path = $destinationPath . $this->backgroundJob->file_path;
            $groupObject = $this->backgroundJob->group;
            $groupObjectId = $groupObject->_id;
            $invalidContactsCount = 0;
            $validContactsCount = 0;
            $totalRows = file($path);
            $totalRowsCount = count($totalRows);
            unset($totalRows);
            $countries = \App\Models\Country::all();
            $iteration = 0;
            $isAllHaveGroup = false;
            $reader = Reader::createFromPath($path);
            $reader->setDelimiter($this->delimiter);
            $groupIndex = null;
            $phoneNumberIndex = null;
            $nameIndex = null;
            $invalidContacts = [];
            $uniquesCount = 0;

            foreach ($this->columns as $column) {
                switch ($column['id']) {
                    case 'name':
                        $nameIndex = $column['index'];
                        break;
                    case 'phone':
                        $phoneNumberIndex = $column['index'];
                        break;
                    case 'group':
                        $groupIndex = $column['index'];
                        break;

                    default:
                        $phoneNumberIndex = 0;
                        break;
                }
            }

            $results = $reader->fetch();
            if ($this->ignoreFirstLine) {
                $totalRowsCount--;
                $invalidContactsCount--;
            }
            $data = [];
            $importationId = mt_rand(100000, 999999);


            $counter = 0;
            foreach ($results as $_fetchedList) {
                $counter++;
                if (!isset($_fetchedList[$phoneNumberIndex]) || empty($_fetchedList[$phoneNumberIndex])) {
                    continue;
                }
                $phonenumber = $_fetchedList[$phoneNumberIndex];
                $groupName = null;

                if(!is_null($groupIndex) && isset($_fetchedList[$groupIndex])) {
                    $isAllHaveGroup = true;
                    if($counter == 1 && $this->ignoreFirstLine ) {
                        $groupName = null;
                    } else {
                        $groupName = $_fetchedList[$groupIndex] ;
                        if(!isset($this->procedureDataForEachGroup[$groupName])) {
                            $this->procedureDataForEachGroup[$groupName] = [
                                'invalidsCount' => 0,
                                'importationId' => mt_rand(100000, 999999),
                                'groupObjectId' => null,
                            ];
                        }
                    }
                }

                if (!$groupName && !$isAllHaveGroup) {
                    $groupName = $groupObject->name;
                }
                $currentGroupId = null;
                if ($groupName && isset($this->groupsChecked[$groupName])) {
                    $currentGroupId = $this->groupsChecked[$groupName]->_id;
                } elseif ($groupName) {
                    if(!$isAllHaveGroup) {
                        $tempGroup = $user->addressBookGroups()->where('name',$groupName)->first();
                    } else {
                        $tempGroup = $user->addressBookGroups()->create([
                            'name' => $groupName,
                            'is_custom_name' => 1,
                            'in_progress' => 1,
                            'created_at' => Carbon::now(),
                        ]);
                    }
                    $currentGroupId = $tempGroup->_id;
                    $this->groupsChecked[$groupName] = $tempGroup;
                }

                $contactName = (!is_null($nameIndex) && !empty($_fetchedList[$nameIndex])) ? preg_replace('/\s+/', ' ', $_fetchedList[$nameIndex]) : null;

                $validationResponse = $this->campaignDbRepo->isValidNumber($phonenumber, $user, $countries);
                if (!$validationResponse['finalNumber'] || !$currentGroupId) {
                    $invalidContactsCount++;
                    if ($this->ignoreFirstLine && $counter == 1) {
                        continue;
                    }
                    $invalidContact = [
                        'user_id' => $user->_id,
                        'group_id' => $currentGroupId,
                        'upload_id' => null,
                        'file_importation_id' => $importationId,
                        'phone_number' => $phonenumber,
                        'name' => $contactName,
                    ];

                    if($isAllHaveGroup) {
                        $this->procedureDataForEachGroup[$groupName]['invalidsCount']++;
                        $invalidContact['file_importation_id'] = $this->procedureDataForEachGroup[$groupName]['importationId'];
                    }

                    $invalidContacts[] = $invalidContact;

                    continue;
                } else {
                    $validContactsCount++;
                }
                $tariff = $validationResponse['detectedTariff'];

                $tempContact = [
                    'user_id' => $user->_id,
                    'group_id' => $currentGroupId,
                    'tariff_id' => $tariff->_id,
                    'file_importation_id' => $importationId,
                    'phone_number' => $validationResponse['finalNumber'],
                    'name' => $contactName,
                    'type' => $this->campaignDbRepo->checkIsPhoneNumberMobile($validationResponse['finalNumber'],$user)
                ];

                if($isAllHaveGroup) {
                    $tempContact['file_importation_id'] = $this->procedureDataForEachGroup[$groupName]['importationId'];
                    $this->procedureDataForEachGroup[$groupName]['groupObjectId'] = $currentGroupId;
                }

                $data[] = $tempContact;



                if (count($data) == 1000) {
                    $uniquesCount += count(array_count_values(array_column($data, 'phone_number')));
                    \DB::table('temp_contacts')->insert($data);
                    $data = [];
                    $iteration++;
                }
            }

            $leftContactsCount = count($data);

            if ($leftContactsCount > 0) {
                $uniquesCount += count(array_count_values(array_column($data, 'phone_number')));
                \DB::table('temp_contacts')->insert($data);
            }


            $currentPercentage = ($leftContactsCount + ($iteration * 1000)) * 100 / $totalRowsCount;
            $this->backgroundJob->complete_percentage = $currentPercentage;
            $this->backgroundJob->save();

            $duplicates = $totalRowsCount - $uniquesCount - $invalidContactsCount;
            if($duplicates <= 0) {
                $duplicates = 0;
            }

            $tempData = [
                'valid' => $validContactsCount,
                'invalid' => $invalidContactsCount,
                'duplicate' => $duplicates,
            ];

            $this->backgroundJob->data = json_encode($tempData);
            $this->backgroundJob->complete_percentage = 100;
            $this->backgroundJob->status = 'FINISHED';
            $this->backgroundJob->save();

            $job = $this->backgroundJob;
            $groupId = $this->backgroundJob->group_id;
            $this->notificationRepo->deleteOnContactsFileUploadFinish($groupId, $user);

            $this->notificationRepo->createOnContactsFileUploadSuccess($job);

            event(new \App\Events\UserDataUpdated([
                'user_id' => $user->_id]));

            if ($isAllHaveGroup || $validContactsCount == 0) {
                $groupObject->delete();
            } else {
                $groupObject->in_progress = 0;
                $groupObject->save();
            }

            try{
                $invalidChunk = [];
                $invalidCounter = 0;
                foreach ($invalidContacts as $invalidContactToAdd) {
                    $invalidCounter++;
                    $invalidChunk[] = $invalidContactToAdd;
                    if(count($invalidChunk) == 1000 || $invalidCounter == count($invalidContacts)) {
                        \DB::table('invalid_contacts')->insert($invalidContacts);
                        $invalidChunk = [];
                    }
                }

            } catch (\Exception $e) {
                \Log::info($e->getMessage());
            }

            if ($this->groupsChecked && count($this->groupsChecked)) {
                foreach ($this->groupsChecked as $group) {
                    $group->in_progress = 0;
                    $group->save();
                }
            }

            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

            if(count($this->procedureDataForEachGroup)) {
                foreach ($this->procedureDataForEachGroup as $procedureData) {
                    DB::statement("call CALLBURN_PARSECONTACTS( ?, 0, ?, ? )",array($procedureData['importationId'],$procedureData['groupObjectId'],$procedureData['invalidsCount']));
                }
            } else {
                DB::select("call CALLBURN_PARSECONTACTS( ?, 0, ?, ? )",array($importationId,$groupObjectId,$invalidContactsCount));
            }

            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            event(new \App\Events\AddressBookUpdated([
                'user_id' => $user->_id]));

            return;

        } catch (\Exception $e) {
            \Log::info('Error In ValidateNumbers');
            \Log::error($e);

            $this->backgroundJob->status = 'FAILED';
            $this->backgroundJob->save();

            $job = $this->backgroundJob;
            $groupId = $this->backgroundJob->group_id;
            $this->notificationRepo->deleteOnContactsFileUploadFinish($groupId, $user);
            $this->notificationRepo->createOnContactsFileUploadFailure($job);
            event(new \App\Events\UserDataUpdated([
                'user_id' => $user->_id]));

            if ($groupObject) {
                $groupObject->delete();
            }
            foreach ($this->groupsChecked as $group) {
                $group->delete();
            }
            event(new \App\Events\AddressBookUpdated([
                'user_id' => $user->_id]));
        }
    }
}
