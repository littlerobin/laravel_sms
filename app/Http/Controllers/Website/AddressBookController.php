<?php

namespace App\Http\Controllers\Website;


use App\Models\ArchivedPhonenumber;
use App\Models\Campaign;
use App\Models\Phonenumber;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CampaignDbService;
use App\Services\ActivityLogService;
use App\Services\AddressBookService;
use App\Models\AddressBookContact;
use App\Models\AddressBookGroup;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Blacklist;

class AddressBookController extends WebsiteController
{

    /**
     * Object of CallburnAddressBook class
     *
     * @var CallburnAddressBook
     */
    private $callburnAddressBook;

    /**
     * Create a new instance of AddressBookController class
     *
     * @return void
     */
    public function __construct()
    {
        $this->activityLogRepo = new ActivityLogService();

        $this->middleware('jwt.headers');
        $this->middleware('jwt.auth', ['except' => [
            'getExportContacts',
        ]]);

        $this->middleware('active.user', ['except' => [
            'getExportContacts',
        ]]);
    }

    /**
     * Send request for getting one contact.
     * GET /address-book/show-contact/{id}
     *
     * @param integer $id
     * @return JSON
     */
    public function getShowContact($id)
    {
        $user = Auth::user();
        $contact = $user->addressBookContacts()->find($id);
        if (!$contact) {
            $response = $this->createBasicResponse(-1, 'contact_does_not__exists');
            return response()->json(['resource' => $response]);
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_data'
            ],
            'contact' => $contact
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting all black list contacts of the user .
     * GET /address-book/index-black-list
     *
     * @param Request $request
     * @return JSON
     */
    public function getIndexBlackList(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 0);
        $perPage = $request->get('per_page', 30);
        $searchKey = $request->get('phone_number', null);
        $orderField = $request->get('order_field', 'updated_at');
        $order = $request->get('order', 'DESC');
        //$blackList =  \App\Models\BlackList::where('user_id',$user->_id)->with('campaign');
        $blackList =  \App\Models\BlackList::where('user_id',$user->_id)->with([
            'campaign' => function ($query) {
                $query->select([
                    '_id','campaign_name'
                ]);
            },
        ]);
        $allCount = $blackList->count();

        if($searchKey) {
            $blackList->where(function ($where) use ($searchKey) {
                $where->where('phonenumber','LIKE','%'.$searchKey.'%')->orWhere('name','LIKE','%'.$searchKey.'%');
            });
        }

        $blackList->orderBy($orderField,$order)->skip($page * $perPage)->take($perPage);
        $count = $blackList->count();
        $blackList = $blackList->get()->toArray();
        //dd($blackList);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_data'
            ],
            'page' => $page + 1,
            'count' => $count,
            'contacts' => $blackList,
            'allCount' => $allCount,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Remove blacklist item
     * POST /address-book/remove-black-list
     *
     * @param Request $request
     * @return JSON
     */
    public function postRemoveBlackList(Request $request)
    {
        $user  = Auth::user();
        $ids = $request->get('phonenumber_ids', 0);
        $status = \App\Models\BlackList::where('user_id',$user->_id)->whereIn('_id',$ids)->delete();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'remove contacts'
            ],
            'status' => 'success',
            'removedCount' => $status,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * add blacklist items
     * POST /address-book/add-black-list
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddBlackList(Request $request, CampaignDbService $campaignDbRepo)
    {
        $user  = Auth::user();
        $phoneNumbers = $request->get('phonenumbers', []);
        $name = $request->get('name');
        $campaignId = $request->get('campaign_id');

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'add contacts'
            ],
            'status' => 'success',
            'addedCount' => 0,
            'failed' => 0,
        ];

        if(!count($phoneNumbers)) {
            return response()->json(['resource' => $response]);
        }

        foreach ($phoneNumbers as $phoneNumber) {
            $campaign = null;
            $phonenumber = null;
            $archivedPhonenumber = null;
            $blackListItem = [];

            if($campaignId) {
                $campaign = Campaign::where('_id',$campaignId)->first();
                if($campaign->is_archived) {
                    $archivedPhonenumber = ArchivedPhonenumber::where('user_id',$user->_id)->where('campaign_id',$campaignId)->where('phone_no',$phoneNumber)->first();
                } else {
                    $phonenumber = Phonenumber::where('campaign_id',$campaignId)->where('phone_no',$phoneNumber)->first();
                }
            }

            if($campaign) {
                if($campaign->is_archived) {
                    $blackListItem['archived_phonenumber_id'] = $archivedPhonenumber->original_id;
                } else {
                    $blackListItem['phonenumber_id'] = $phonenumber->_id;
                }
            }

            if(substr($phoneNumber,0,1) != '+' && substr($phoneNumber,0,1) != '0') {
                $phoneNumber = '+'.$phoneNumber;
            }
            if(substr($phoneNumber,0,1) == '0' && substr($phoneNumber,1,1) == '0') {
                $phoneNumber = substr($phoneNumber,2);
                $phoneNumber = '+'.$phoneNumber;
            }
            if(substr($phoneNumber,0,1) != '+') {
                $response['failed']++;
                continue;
            }

            $validationResponse = $campaignDbRepo->isValidNumber($phoneNumber, $user);
            if(!isset($validationResponse['detectedTariff'])) {
                $response['failed']++;
                continue;
            }
            $tariff = $validationResponse['detectedTariff'];
            $countryCode = $tariff->country->code;
            $type = $campaignDbRepo->checkIsPhoneNumberMobile($validationResponse['finalNumber']);

            if(substr($phoneNumber,0,1) == '+') {
                $phoneNumber = substr($phoneNumber,1);
            }

            $blackListItem['phonenumber'] = $phoneNumber;
            $blackListItem['name'] = $name;
            $blackListItem['type'] = $type;
            $blackListItem['country_code'] = $countryCode;
            $blackListItem['user_id'] = $user->_id;
            if(isset($request->campaign_id)){
                $blackListItem['blacklist_type'] = 'MANUALLY_ADDED';
                $blackListItem['campaign_id'] = $request->campaign_id;
            }
            else{
                $blackListItem['blacklist_type'] = 'MANUALLY_TYPED';
            }
            try {
                \App\Models\BlackList::create($blackListItem);
                $response['addedCount']++;

            } catch (\Exception $e) {
                \Log::error($e);
                $response['failed']++;
            }
        }

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting all contacts of the user .
     * GET /address-book/index-contacts
     *
     * @param Request $request
     * @return JSON
     */
    public function getIndexContacts(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 0);
        $perPage = $request->get('per_page', 14);
        $searchKey = $request->get('phone_number', null);
        $groupId = $request->get('group_id');
        $orderField = $request->get('order_field', '_id');
        $order = $request->get('order', 'ASC');
        $currentBookGroup = collect([
            '_id' => 'all'
        ]);
        $shouldPutThreeAsterisksForAll = false;
        $allContactsOfGroupCount = 0;
        $count = 0;

        if ($groupId and $groupId != 'all') {
//            $currentBookGroup = \App\Models\AddressBookGroup::where('_id', $groupId)->with('campaigns');
            $currentBookGroup = \App\Models\AddressBookGroup::where('_id', $groupId);

            $succeeded = DB::select('SELECT COUNT(`phonenumbers`.`_id`) as count from phonenumbers
                        INNER JOIN campaigns ON phonenumbers.campaign_id = campaigns._id
                        INNER JOIN campaign_groups ON campaigns._id = campaign_groups.campaign_id
                        WHERE phonenumbers.status = "SUCCEED" AND campaign_groups.address_book_group_id = :groupid',['groupid' => $groupId]);

            $failed = DB::select('SELECT COUNT(`phonenumbers`.`_id`) as count from phonenumbers
                        INNER JOIN campaigns ON phonenumbers.campaign_id = campaigns._id
                        INNER JOIN campaign_groups ON campaigns._id = campaign_groups.campaign_id
                        WHERE phonenumbers.status = "FAILED" AND campaign_groups.address_book_group_id = :groupid',['groupid' => $groupId]);

            $succeededCount = $succeeded[0]->count;
            $failedCount = $failed[0]->count;
            $allContactsOfGroupCount = $currentBookGroup->count();
            $currentBookGroup = $currentBookGroup->with([
                'contacts' => function ($query) use ($searchKey, $page, $perPage, $orderField, $order, &$allContactsOfGroupCount, &$count) {
                $query->select(
                    'address_book_contacts._id', 'address_book_contacts.user_id', 'address_book_contacts.phone_number',
                    'address_book_contacts.name', 'address_book_contacts.tariff_id',
                    'address_book_contacts.should_put_three_asterisks', 'address_book_contacts.created_at', 'address_book_contacts.updated_at');
                $allContactsOfGroupCount = $query->count();
                if ($searchKey) {
                    $query = $query->where(function ($serachQuery) use ($searchKey) {
                        $serachQuery->where('address_book_contacts.name', 'LIKE', '%' . $searchKey . '%')
                            ->orWhere('address_book_contacts.phone_number', 'LIKE', '%' . $searchKey . '%');
                    });
                }
                $count = $query->count();
                $query->skip($page * $perPage)->take($perPage)
                    ->orderBy('address_book_contacts.' . $orderField, $order)->with([

                        'tariff' => function ($query) {
                            $query->with([
                                'country' => function ($query) {
                                    $query->select([
                                        '_id', 'code'
                                    ]);
                                }
                            ])->select([
                                '_id', 'country_id'
                            ]);
                        },
                        'phonenumbers' => function ($q) {
                            $q->whereNotNull('last_called_at')->has('campaign')->with([
                                'campaign' => function ($quer) {
                                    $quer->select([
                                        '_id','campaign_name'
                                    ]);
                                }
                            ])->select([
//                                '_id', 'campaign_id', 'last_called_at'
                            '*'
                            ]);
                        },
                    ]);
            }])->first();

            if ($currentBookGroup->should_put_three_asterisks) {
                $shouldPutThreeAsterisksForAll = true;
            }
            $addressBookContacts = $currentBookGroup->contacts;
//            dd($addressBookContacts);

        }
        else {
            $addressBookContacts = \App\Models\AddressBookContact::where('user_id', $user->_id);

            $succeeded = DB::select('SELECT COUNT(`_id`) as count from phonenumbers
                        WHERE status = "SUCCEED" AND user_id = :userId',['userId' => $user->_id]);

            $failed = DB::select('SELECT COUNT(`_id`) as count from phonenumbers
                        WHERE status = "FAILED" AND user_id = :userId',['userId' => $user->_id]);

            $succeededCount = $succeeded[0]->count;
            $failedCount = $failed[0]->count;

            $allContactsOfGroupCount = $addressBookContacts->count();

            if ($searchKey) {
                $addressBookContacts = $addressBookContacts
                    ->where(function ($serachQuery) use ($searchKey) {
                        $serachQuery->where('name', 'LIKE', '%' . $searchKey . '%')
                            ->orWhere('phone_number', 'LIKE', '%' . $searchKey . '%');
                    });
            }

            $count = $addressBookContacts->count();
            $addressBookContacts = $addressBookContacts->skip($page * $perPage)->take($perPage)
                ->orderBy($orderField, $order)->with([

                    'tariff' => function ($query) {
                        $query->with([
                            'country' => function ($query) {
                                $query->select([
                                    '_id', 'code'
                                ]);
                            }
                        ])->select([
                            '_id', 'country_id'
                        ]);
                    },


                ])->get();
            //dd($addressBookContacts);
        }

        foreach ($addressBookContacts as &$addressBookContact) {
            //dd($addressBookContact->toArray());
            $addressBookContact->phonenumber = NULL;
            if($addressBookContact->phonenumbers->count()){
                $addressBookContact->phonenumber = $addressBookContact->phonenumbers->first();
            }
            unset($addressBookContact->phonenumbers);
            if ($addressBookContact->should_put_three_asterisks || $shouldPutThreeAsterisksForAll) {
                $addressBookContact->phone_number = AddressBookService::addThreeAsterisks($addressBookContact->phone_number);
            }
        }


        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_data'
            ],
            'page' => $page + 1,
            'count' => $count,
            'contacts' => $addressBookContacts->toArray(),
            'currentGroup' => $currentBookGroup,
            'allContactsOfGroupCount' => $allContactsOfGroupCount,
            'succeed' => $succeededCount,
            'failed' => $failedCount
        ];
        //dd($response['contacts']);
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for creating a new contact .
     * POST /address-book/create-contact
     *
     * @param Request $request
     * @return JSON
     */
    public function postCreateContact(Request $request, CampaignDbService $campaignDbRepo)
    {
        $user = Auth::user();
        $phoneNumber = $request->get('phone_number');
        $name = $request->get('name');

        $contactData = ['name' => $name];

        $validationResponse = $campaignDbRepo->isValidNumber($phoneNumber, $user);
        if ($validationResponse['finalNumber']) {
            $contactData['phone_number'] = $validationResponse['finalNumber'];
            $contactData['type'] = $campaignDbRepo->checkIsPhoneNumberMobile($phoneNumber, $user);
            $contactData['tariff_id'] = $validationResponse['detectedTariff']->_id;
        } else {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'phonenumber_is_not_supported'
                ]
            ];
            return response()->json(['resource' => $response]);
        }

        $contactData['user_id'] = $user->_id;
        try {
            $contact = AddressBookContact::create($contactData);
        } catch (\Illuminate\Database\QueryException $e) {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'contact_exists'
                ]
            ];
            return response()->json(['resource' => $response]);
        }
        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'PHONEBOOK',
            'description' => 'user__added ' . $contactData['phone_number'] . ' this__contact_to_phonenbook'
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_created'
            ],
            'contact' => $contact
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for updating existing contact
     * PUT /address-book/update-contact/{id}
     *
     * @param integer $id
     * @param Request $request
     * @return JSON
     */
    public function putUpdateContact($id, Request $request)
    {
        $user = Auth::user();
        $phoneNumber = $request->get('phone_number');
        $name = $request->get('name');
        $contact = $user->addressBookContacts->find($id);
        if (!$contact) {
            $response = $this->createBasicResponse(-1, 'contact_does_not_exists');
            return response()->json(['resource' => $response]);
        }
        $contact->update([
            'name' => $name
        ]);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_updated'
            ],
            'contact' => $contact
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for attaching contacts to group
     * POST /address-book/attach-contacts-to-group
     *
     * @param Request $request
     * @return JSON
     */
    public function postAttachContactsToGroup(Request $request)
    {
        $user = Auth::user();
        $contactIds = array_keys(array_filter($request->get('contact_ids')));
        $groupId = $request->get('group_id');
        $contacts = $user->addressBookContacts()->whereIn('_id', $contactIds)->get();
        foreach ($contacts as $contact) {
            try {
                $contact->groups()->attach($groupId);
            } catch (\Exception $e) {

            }
        }
        $response = $this->createBasicResponse(0, 'group_added_to_contacts');
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for detaching contacts from group
     * POST /address-book/detach-contacts-from-group
     *
     * @param Request $request
     * @return JSON
     */
    public function postDetachContactsFromGroup(Request $request)
    {
        $user = Auth::user();
        $contactIds = $request->get('contact_ids', []);
        $groupId = $request->get('group_id');
        $group = $user->addressBookGroups()->find($groupId);
        if (!$group) {
            $response = $this->createBasicResponse(-1, 'group_not_exists_or_not_belongs_to_you');
            return response()->json(['resource' => $response]);
        }
        $group->contacts()->detach($contactIds);
        $response = $this->createBasicResponse(0, 'detached');
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing contact.
     * DELETE /address-book/remove-contact/{id}
     *
     * @param integer $id
     * @return JSON
     */
    public function deleteRemoveContact($id)
    {
        \App\Models\AddressBookContact::where('_id', $id)->delete($id);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_removed'
            ],
            'contact_id' => $id
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing multiple contacts
     * DELETE /address-book/remove-contacts
     *
     * @param Request $request
     * @return JSON .
     */
    public function postRemoveContacts(Request $request)
    {
        $groupIsRemoved = false;
        $contactIds = $request->get('contact_ids');
        $currentGroupId = $request->get('group_id', null);

        $queryIds = ' ';
        foreach ($contactIds as $contactId) {
            $queryIds .= $contactId . ',';
        }
        $queryIds = rtrim($queryIds, ',');

        if($currentGroupId != 'all') {
            DB::delete("DELETE FROM `address_book_group_contact`  WHERE address_book_group_contact.address_book_contact_id IN (".$queryIds.") AND address_book_group_contact.address_book_group_id = ".$currentGroupId);
            DB::delete("DELETE t1 FROM  address_book_contacts t1
                      LEFT JOIN address_book_group_contact t2 ON t1._id = t2.address_book_contact_id
                      WHERE (SELECT COUNT(*) FROM address_book_groups WHERE address_book_groups._id = t2.address_book_contact_id ) = 1 
                      AND t1._id IN (".$queryIds.")");
            DB::delete("DELETE t1 FROM `address_book_groups` t1
                          WHERE t1._id = ".$currentGroupId." 
                          AND (t1.name LIKE  '%addressbook_import_added_manually%' OR t1.name LIKE  '%added_from_a_file%')
                          AND (SELECT COUNT(*) FROM address_book_group_contact WHERE t1._id = address_book_group_contact.address_book_group_id ) = 0");
        } else {
            DB::delete("DELETE t2, t1 FROM  address_book_contacts t1
                      LEFT JOIN address_book_group_contact t2 ON t1._id = t2.address_book_contact_id
                      WHERE t1._id IN (".$queryIds.")");
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_removed'
            ],
            'groupIsRemoved' => $groupIsRemoved
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing multiple contacts
     * DELETE /address-book/remove-contacts-searched
     *
     * @param Request $request
     * @return JSON .
     */
    public function postRemoveContactsSearched(Request $request)
    {
        $user = Auth::user();
        $keyWord = $request->get('key_word');
        $currentGroupId = $request->get('group_id', null);
        $groupIsRemoved = false;

        $contactsQuery = "SELECT t1._id FROM  address_book_contacts t1
                      LEFT JOIN address_book_group_contact t2 ON t1._id = t2.address_book_contact_id
                      WHERE t1.user_id = ".$user->_id;

        if($keyWord) {
            $contactsQuery .= " AND (t1.name LIKE '%".$keyWord."%' OR t1.phone_number LIKE '%".$keyWord."%') ";
        }

        if($currentGroupId && $currentGroupId != 'all') {
            $contactsQuery .= " AND t2.address_book_group_id = ".$currentGroupId;
        }

        $contactsIds = DB::select($contactsQuery);

        $queryIds = ' ';
        foreach ($contactsIds as $contactId) {
            $queryIds .= $contactId->_id . ',';
        }
        $queryIds = rtrim($queryIds, ',');

        $deleteContactsQuery = "DELETE t1 FROM address_book_contacts t1 
                                    LEFT JOIN address_book_group_contact t2 ON t1._id = t2.address_book_contact_id
                                    WHERE t1._id IN (".$queryIds.")";

        $deletePivotQuery = "DELETE FROM address_book_group_contact WHERE address_book_group_contact.address_book_contact_id IN (".$queryIds.")";

        if($currentGroupId && $currentGroupId != 'all') {
            $deletePivotQuery .= ' AND address_book_group_contact.address_book_group_id = '.$currentGroupId;
            $deleteContactsQuery .= ' AND (SELECT COUNT(*) FROM address_book_groups WHERE address_book_groups._id = t2.address_book_contact_id ) = 1 ';
        }

        $contactDeletedRows =  DB::delete($deleteContactsQuery);
        if(!$contactDeletedRows) {
            DB::delete($deletePivotQuery);
        }

        if($currentGroupId && $currentGroupId != 'all') {
            DB::delete("DELETE t1 FROM `address_book_groups` t1
                          WHERE t1._id = ".$currentGroupId." 
                          AND (t1.name LIKE  '%addressbook_import_added_manually%' OR t1.name LIKE  '%added_from_a_file%')
                          AND (SELECT COUNT(*) FROM address_book_group_contact WHERE t1._id = address_book_group_contact.address_book_group_id ) = 0");
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_removed'
            ],
            'groupIsRemoved' => $groupIsRemoved
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting all contacts for export.
     * GET /address-book/export-contacts
     *
     * @return JSON
     */
    public function getExportContacts(Request $request)
    {
        $token = $request->get('token');
        $user = \App\Services\DownloadsService::user($token);

        $groupOrContact = $request->get('group_or_contact');
        $selectedIds = $request->get('selected_ids');
        //dd($selectedIds);
        $selectedIds = array_keys(array_filter(json_decode($selectedIds, 1)));
        $allGroup = in_array('ALL', $selectedIds);
        $keyword = $request->get('keyword');

        if ($groupOrContact == 'contact') {
            $contacts = $user->addressBookContacts();
            if ($selectedIds) {
                $contacts = $contacts->whereIn('_id', $selectedIds);
            }
            if ($keyword) {
                $contacts = $contacts->where(function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('phone_number', 'LIKE', '%' . $keyword . '%');
                });
            }
            $contacts = $contacts->with(['groups', 'tariff'])->get();
        } elseif ($allGroup) {
            $contacts = $user->addressBookContacts();
            if ($keyword) {
                $contacts = $contacts->where(function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('phone_number', 'LIKE', '%' . $keyword . '%');
                });
            }
            $contacts = $contacts->with(['groups', 'tariff'])->get();
        } else {
            $groups = $user->addressBookGroups();
            if ($selectedIds) {
                $groups = $groups->whereIn('_id', $selectedIds);
            }
            if ($keyword) {
                $groups = $groups->where('name', 'LIKE', '%' . $keyword . '%');
            }
            $groups = $groups->get();
            $contacts = new \Illuminate\Support\Collection();
            foreach ($groups as $group) {
                $contacts = $contacts->merge($group->contacts()->with('groups')->get());
            }
        }

        $locale = $request->get('locale', 'en');
        \App::setLocale($locale);

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne([trans('csv.phonenumber'), trans('csv.contact_name'), trans('csv.groups')]);
        foreach ($contacts as $contact) {
            $groupNames = [];
            foreach ($contact['groups'] as $group) {
                $groupNames[] = $group['name'];
            }
            $groupName = implode(',', $groupNames);
            if ($contact['should_put_three_asterisks']) {
                $contactNumber = AddressBookService::addThreeAsterisks($contact['phone_number']);
            } else {
                $contactNumber = $contact['phone_number'];
            }
            $contactName = $contact['name'];
            $csv->insertOne([$contactNumber, $contactName, $groupName]);
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="contacts_data.csv"');
        $csv->output('contacts_data.csv');
        exit;
    }

    /**
     * Send request for importing contacts data.
     * POST /address-book/import-contacts
     *
     * @param Request $request
     * @return JSON
     */
    public function postImportContacts(Request $request, CampaignDbService $campaignDbRepo)
    {
        set_time_limit(600);
        $user = Auth::user();
        $contactsData = $request->get('contacts_data');

        $jobId = $request->get('job_id');
        if ($jobId) {
            $user->jobs()->where('_id', $jobId)->delete();
        }
        $importedCount = 0;

        $groupObject = $user->addressBookGroups()->create(
            [
                'type' => 'CREATED_BY_USER_MANUALLY',
                'name' => $request->get('group_name', null)
            ]
        );

        foreach ($contactsData as $contact) {
            $contact = (object)$contact;
            if ($contact->status != 'success') {
                continue;
            }
            $contactData = [
                'name' => $contact->name,
                'phone_number' => $contact->number,
                'type' => $campaignDbRepo->checkIsPhoneNumberMobile($contact->number),
                'tariff_id' => $contact->tariff['_id'],
                'user_id' => $user->_id
            ];
            $importedCount++;
            try {
                $newContact = AddressBookContact::create($contactData);
            } catch (\Exception $e) {
                continue;
            }
            if (!$newContact) {
                continue;
            }

            $newContact->groups()->attach($groupObject->_id);
        }
        if ($importedCount == 0) {
            $groupObject->delete();
        }

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'PHONEBOOK',
            'description' => 'User imported ' . $importedCount . ' contacts'
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'imported'
            ]
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting one group.
     * GET /address-book/show-group/{id}
     *
     * @return JSON
     */
    public function getShowGroup($id)
    {
        $user = Auth::user();
        $group = $user->addressBookGroups()->find($id);
        if (!$group) {
            $response = $this->createBasicResponse(-1, 'grou__ does__not_exists');
            return response()->json(['resource' => $response]);
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'group_data_1'
            ],
            'group' => $group
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting all groups of the user .
     * GET /address-book/all-groups
     *
     * @param Request $request
     * @return JSON
     */
    public function getAllGroups(Request $request)
    {
        $user = Auth::user();
        $groups = $user->addressBookGroups()->whereNotNull("name")->select('_id as id', 'name as label')->get();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contacts_list'
            ],
            "groups" => $groups
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting all groups of the user .
     * GET /address-book/index-groups
     *
     * @param Request $request
     * @return JSON
     */
    public function getIndexGroups(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 0);
        $perPage = $request->get('per_page', 14);
        $searchKey = $request->get('name');
        $searchInGroups = $request->get('search_in_groups',false);
        $orderField = $request->get('order_field', 'updated_at');
        $order = $request->get('order', 'DESC');

        $addressBookGroups = AddressBookGroup::whereNotNull("address_book_groups.name")->where('address_book_groups.user_id', $user->_id);

        if ($searchKey && !$searchInGroups) {
            $addressBookGroups = $addressBookGroups->where('address_book_groups.name', 'LIKE', '%' . $searchKey . '%');
        }

        if ($orderField == 'contacts_count') {

            if($searchInGroups) {
                $addressBookGroups = $addressBookGroups
                    ->join('address_book_group_contact', 'address_book_groups._id', '=', 'address_book_group_contact.address_book_group_id')
                    ->join('address_book_contacts', 'address_book_group_contact.address_book_contact_id', '=', 'address_book_contacts._id')
                    ->select('address_book_groups._id', 'address_book_groups.user_id', 'address_book_groups.name', 'address_book_groups.in_progress',
                        'address_book_groups.type', 'address_book_groups.created_at', 'address_book_groups.updated_at',
                        DB::raw('address_book_group_contact.address_book_group_id as address_book_group_contact_address_book_group_id'),
                        DB::raw('count(address_book_group_contact.address_book_group_id) as contacts_total_count'),
                        DB::raw('address_book_contacts.name as contact_name'),
                        DB::raw('address_book_contacts.phone_number as contact_phone_number'))
                    ->where(function ($contact) use($searchKey,$searchInGroups) {
                        $contact->where('address_book_contacts.name', 'LIKE', '%' . $searchKey . '%')
                            ->orWhere('address_book_contacts.phone_number', 'LIKE', '%' . $searchKey . '%');

                    })->groupBy('address_book_groups._id')
                    ->orderBy('contacts_total_count', $order);
                $addressBookGroupsForCount = clone $addressBookGroups;
                $count = count($addressBookGroupsForCount->get());
            } else {
                $addressBookGroups = $addressBookGroups
                    ->join('address_book_group_contact', 'address_book_groups._id', '=', 'address_book_group_contact.address_book_group_id')
                    ->select('address_book_groups._id', 'address_book_groups.user_id', 'address_book_groups.name', 'address_book_groups.in_progress',
                        'address_book_groups.type', 'address_book_groups.created_at', 'address_book_groups.updated_at',
                        DB::raw('address_book_group_contact.address_book_group_id as address_book_group_contact_address_book_group_id'),
                        DB::raw('count(address_book_group_contact.address_book_group_id) as contacts_total_count'))
                    ->groupBy('address_book_groups._id')
                    ->orderBy('contacts_total_count', $order);
                $addressBookGroupsForCount = clone $addressBookGroups;
                $count = count($addressBookGroupsForCount->get());
            }
        }
        else {
            if ($searchInGroups) {
                $addressBookGroups = $addressBookGroups
                    ->join('address_book_group_contact', 'address_book_groups._id', '=', 'address_book_group_contact.address_book_group_id')
                    ->select('address_book_groups._id', 'address_book_groups.user_id', 'address_book_groups.name', 'address_book_groups.in_progress',
                        'address_book_groups.type', 'address_book_groups.created_at', 'address_book_groups.updated_at' ,
                        DB::raw('address_book_contacts.name as contact_name'),
                        DB::raw('address_book_contacts._id as contact_id'),
                        DB::raw('address_book_contacts.phone_number as contact_phone_number'))
                    ->join('address_book_contacts', 'address_book_group_contact.address_book_contact_id', '=', 'address_book_contacts._id')
                    ->where(function ($contact) use($searchKey,$searchInGroups) {
                        if($searchInGroups) {
                            $contact->where('address_book_contacts.name', 'LIKE', '%' . $searchKey . '%')
                                ->orWhere('address_book_contacts.phone_number', 'LIKE', '%' . $searchKey . '%');
                        } else {
                            $contact->whereNotNull('address_book_contacts._id');
                        }
                    })
                    ->groupBy('address_book_groups._id')
                    ->orderBy('address_book_groups.'.$orderField, $order)->orderBy('_id',$order);
                $addressBookGroupsForCount = clone $addressBookGroups;
                $count = count($addressBookGroupsForCount->get());
            } else {
                $addressBookGroups = $addressBookGroups->select(
                    ['_id', 'user_id', 'name', 'in_progress',
                        'type', 'created_at', 'updated_at'])->orderBy($orderField, $order)->orderBy('_id',$order);
                $count = $addressBookGroups->count();
            }
        }


        $perPage = (int)$perPage;
        $page = (int)$page;
        $addressBookGroups = $addressBookGroups->with('contactCount')->skip($page * $perPage)->take($perPage)->get();
        $allContactsCount = $user->addressBookContacts()->count();
        //todo when we need all contacts just uncomment this part
//        $allContacts = collect([
//            'contact_count' => [
//                [
//                    'count' => $allContactsCount
//                ]
//            ],
//            'in_progress' => 0,
//            '_id' => 'all'
//        ]);
//        $addressBookGroups->prepend($allContacts);
        $allGroupsCount = $user->addressBookGroups()->count();
        $lastAddedContactDate = $user->addressBookContacts()->select('_id')
            ->orderBy('_id', 'DESC')
            ->first();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contacts_list'
            ],
            'page' => $page + 1,
            'count' => $count,
            'groups' => $addressBookGroups,
            'allContactsCount' => $allContactsCount,
            'allGroupsCount' => $allGroupsCount,
            'lastAddedContactDate' => $lastAddedContactDate,
        ];


        return response()->json(['resource' => $response]);

    }

    /**
     * Send request for creating a new group.
     * POST /address-book/create-group
     *
     * @param Request $request
     * @return JSON
     */
    public function postCreateGroup(Request $request)
    {
        $user = Auth::user();
        $name = $request->get('name');
        $notes = $request->get('notes');

        $group = AddressBookGroup::create([
            'user_id' => $user->_id,
            'name' => $name,
        ]);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'group__created'
            ],
            'group' => $group
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for merging groups.
     * POST /address-book/merge-groups
     *
     * @param Request $request
     * @return JSON
     */
    public function postMergeGroups(Request $request)
    {
        $user = Auth::user();
        $name = $request->get('name');
        $ids = array_keys(array_filter($request->get('ids')));
        $groups = $user->addressBookGroups()->whereIn('_id', $ids)->with('contacts')->get();
        $contactIds = [];
        foreach ($groups as $group) {
            foreach ($group->contacts as $contact) {
                $contactIds[] = $contact->_id;
            }
            $group->contacts()->detach();
            $group->delete();
        }
        $contactIds = array_unique($contactIds);
        $newGroup = AddressBookGroup::create(['name' => $name, 'user_id' => $user->_id]);
        $newGroup->contacts()->attach($contactIds);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'groups_unified_1'
            ],
            'group' => $newGroup
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * create token for upload contacts from mobile app.
     * POST /address-book/mobile-contacts
     *
     * @param Request $request
     * @return JSON
     */
    public function getMobileContacts()
    {
        $token = str_random(30);
        $tokenExpirationDate = Carbon::now()->addMinutes(10);

        Auth::user()->update([
            'mobile_contacts_upload_token' => $token,
            'mobile_contacts_upload_token_expiration' => $tokenExpirationDate
        ]);

        $response = [
            'error' => [
                'no' => 0,
                'text' => ''
            ],
            'token' => $token
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for updating existing group
     * PUT /address-book/update-group/{id}
     *
     * @param integer $id
     * @param Request $request
     * @return JSON
     */
    public function putUpdateGroup($id, Request $request)
    {
        $user = Auth::user();
        $name = $request->get('name');

        $group = $user->addressBookGroups()->find($id);
        if (!$group) {
            $response = $this->createBasicResponse(-1, 'grou__ does__not_exists	');
            return response()->json(['resource' => $response]);
        }
        $group->update(['name' => $name]);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'group_has_been_updated'
            ],
            'group' => $group
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing group.
     * DELETE /address-book/remove-group/{id}
     *
     * @param integer $id
     * @return JSON
     */
    public function deleteRemoveGroup($id)
    {

        $user = Auth::user();

        $addressBookGroup = AddressBookGroup::find($id);
        if (!$addressBookGroup) {
            $response = $this->createBasicResponse(-1, 'grou__ does__not_exists');
            return response()->json(['resource' => $response]);
        }
        $add = $addressBookGroup->contacts()->whereHas('groups', function ($query) {
        }, '<', 2)->delete();
        // $add = AddressBookContact::whereHas('groups', function($query) use ($addressBookGroup){
        //     $query->where('_id', $addressBookGroup->_id);
        // }, '<', 2)->delete();
        $user->addressBookGroups()->destroy($id);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_removed'
            ],
            'group_id' => $id
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing group.
     * DELETE /address-book/remove-groups
     *
     * @param Request $request
     * @return JSON
     */
    public function deleteRemoveGroups(Request $request)
    {
        $groupIdsString = $request->get('group_ids');
        $groupIds = array_keys(array_filter(json_decode($groupIdsString, 1)));
        $user = Auth::user();

        if (in_array('all', $groupIds)) {

            $deletedRowsCount = \DB::delete(
                "DELETE t1 FROM  address_book_groups t1 
                      LEFT JOIN campaign_groups t4 ON t1._id = t4.address_book_group_id
                      LEFT JOIN campaigns t5 ON t4.campaign_id = t5._id
                      WHERE (t5.status = 'dialing_completed' OR 
                            (SELECT COUNT(*) FROM campaign_groups WHERE campaign_groups.address_book_group_id = t1._id ) = 0 )
                              AND t1.user_id = ".$user->_id
            );

            if ($deletedRowsCount == 0) {
                $response = [
                    'error' => [
                        'no' => -1,
                        'text' => 'contacts_used_and_cant_be_removed'
                    ]
                ];

                return response()->json(['resource' => $response]);
            } else {
                DB::delete("DELETE t1 FROM address_book_contacts t1
                          WHERE t1.user_id = ".$user->_id." AND (SELECT COUNT(*) FROM address_book_group_contact WHERE address_book_group_contact.address_book_contact_id = t1._id ) = 0");
            }
        } else {
            if($groupIds) {
                $queryIds = ' ';
                foreach ($groupIds as $groupId) {
                    $queryIds .= $groupId . ',';
                }
                $queryIds = rtrim($queryIds, ',');

                $contactsIds = DB::select("SELECT t1._id FROM  address_book_contacts t1
                      LEFT JOIN address_book_group_contact t2 ON t1._id = t2.address_book_contact_id
                      WHERE t1.user_id = ".$user->_id." AND t2.address_book_group_id IN (".$queryIds.")");

                $contactsQueryIds = ' ';
                foreach ($contactsIds as $contactId) {
                    $contactsQueryIds .= $contactId->_id . ',';
                }
                $contactsQueryIds = rtrim($contactsQueryIds, ',');


                $deletedRowsCount = \DB::delete(
                    "DELETE t1 FROM  address_book_groups t1 
                      LEFT JOIN campaign_groups t4 ON t1._id = t4.address_book_group_id
                      LEFT JOIN campaigns t5 ON t4.campaign_id = t5._id
                      WHERE (t5.status = 'dialing_completed' OR 
                            (SELECT COUNT(*) FROM campaign_groups WHERE campaign_groups.address_book_group_id = t1._id ) = 0 )
                              AND t1._id IN (".$queryIds.")"
                );

                if($deletedRowsCount == 0) {
                    $response = [
                        'error' => [
                            'no' => -1,
                            'text' => 'contacts_used_and_cant_be_removed'
                        ]
                    ];

                    return response()->json(['resource' => $response]);
                }

                if(count($contactsIds)) {
                    DB::delete("DELETE FROM address_book_contacts WHERE address_book_contacts._id IN (".$contactsQueryIds.") 
                                AND (SELECT COUNT(*) FROM address_book_group_contact WHERE address_book_group_contact.address_book_contact_id IN (".$contactsQueryIds.") ) = 0 ");
                }
            }
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_removed'
            ]
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing group.
     * DELETE /address-book/remove-groups-searched
     *
     * @param Request $request
     * @return JSON
     */
    public function deleteRemoveGroupsSearched(Request $request)
    {
        $user = Auth::user();
        $searchKey = $request->get('name');

        $deleteGroupQuery = "DELETE t1 FROM  address_book_groups t1 
                      LEFT JOIN campaign_groups t4 ON t1._id = t4.address_book_group_id
                      LEFT JOIN campaigns t5 ON t4.campaign_id = t5._id
                      WHERE (t5.status = 'dialing_completed' OR 
                            (SELECT COUNT(*) FROM campaign_groups WHERE campaign_groups.address_book_group_id = t1._id ) = 0 )
                              AND t1.user_id = ".$user->_id;
        if($searchKey) {
            $deleteGroupQuery .= " AND t1.name LIKE '%".$searchKey."%'";
        }

        $deletedRowsCount = \DB::delete($deleteGroupQuery);

        if ($deletedRowsCount == 0) {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'contacts_used_and_cant_be_removed'
                ]
            ];
            return response()->json(['resource' => $response]);
        } else {
            DB::delete("DELETE t1 FROM address_book_contacts t1
                          WHERE t1.user_id = ".$user->_id." AND (SELECT COUNT(*) FROM address_book_group_contact WHERE address_book_group_contact.address_book_contact_id = t1._id ) = 0");
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'contact_has_been_removed'
            ]
        ];

        return response()->json(['resource' => $response]);
    }
}
