<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Campaign;
use App\Models\InvalidContact;
use App\Models\Phonenumber;
use App\Models\Schedulation;
use App\Models\UserSmsCost;
use App\Services\CampaignDbService;
use App\Services\CampaignService;
use App\Services\MessageLogsService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateCampaign extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $backgroundJob;
    private $messageLogsService;
    private $campaignDbRepo;
    private $campaignRepo;
    private $userRepo;
    private $user;
    private $emailNotification;
    private $voiceFileId;
    private $validatedCampaignData;
    private $serverTimezone;
    private $activityLogRepo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
//        \App\Models\BackgroundJob $backgroundJob,
        $emailNotification,
        $voiceFileId,
        $validatedCampaignData,
        $userId,
        $serverTimezone
    )
    {
//        $this->backgroundJob = $backgroundJob;
        $this->campaignDbRepo = new CampaignDbService();
        $this->campaignRepo = new CampaignService();
        $this->messageLogsService = new MessageLogsService();
        $this->userRepo = new UserService();
        $this->user = $this->userRepo->getUserByPK($userId);
        $this->voiceFileId = $voiceFileId;
        $this->emailNotification = $emailNotification;
        $this->validatedCampaignData = $validatedCampaignData;
        $this->serverTimezone = $serverTimezone;
        $this->activityLogRepo = new \App\Services\ActivityLogService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $userSmsTariffs = UserSmsCost::where('user_id', $this->user->_id)->get();
            $campaignData = $this->validatedCampaignData->campaignData;
            $smsText = $campaignData['sms_text'];
            $getEmailNotifications = $this->validatedCampaignData->getEmailNotifications;
            $campaignId = $this->validatedCampaignData->campaignId;
            $campaign = $this->validatedCampaignData->campaign;
            $oldCampaign = clone $campaign;
            $finalPhonenumbers = $this->validatedCampaignData->finalPhonenumbers;
            $usersCallerId = $this->validatedCampaignData->usersCallerId;
            $finalSchedulation = $this->validatedCampaignData->finalSchedulation;
            $groupIds = $this->validatedCampaignData->groupIds;
            $shouldUseAllContacts = $campaignData['should_use_all_contacts'];
            $serverTimezone = config('app.timezone');
            $customerTimezone = $this->user->timezone ? $this->user->timezone : 'UTC';
            $serverTimezine = $this->serverTimezone;

            if (!empty($finalSchedulation)) {
                $recipentsArray = array_column($finalSchedulation, 'max');
                $recipentsCount = array_sum($recipentsArray);
            } else {
                $recipentsCount = null;
            }

            if ($getEmailNotifications) {
                $campaignData['get_email_notifications'] = $this->emailNotification;
            }

            $schedulationsIds = Schedulation::where('campaign_id', $campaign->_id)->where('is_finished', 0)->lists('_id');
            Phonenumber::whereIn('schedulation_id', $schedulationsIds)->chunk(500, function ($phonenumbers) {
                $phonenumbersIds = [];
                foreach ($phonenumbers as $phonenumber) {
                    $phonenumbersIds[] = $phonenumber->_id;
                }
                DB::transaction(function () use ($phonenumbersIds) {
                    try {
                        Phonenumber::whereIn('_id', $phonenumbersIds)->update(['schedulation_id' => NULL]);
                        DB::commit();
                    } catch (\Exception $e) {
                        \Log::error($e);
                        DB::rollBack();
                    }
                }, 5);
            });

//            $campaign->schedulations()->where('is_finished', 0)->delete();
            DB::transaction(function () use ($campaign, $campaignId, $campaignData) {
                try {
                    $this->campaignRepo->updateCampaign($campaignId, $campaignData);
                    Schedulation::where('campaign_id', $campaign->_id)->where('is_finished', 0)->update(['campaign_id' => NULL]);
                    DB::commit();
                } catch (\Exception $e) {
                    \Log::error($e);
                    DB::rollBack();
                }
            }, 3);

            $schedulationsToAdd = [];
            foreach ($finalSchedulation as $schedulation) {
                if (!isset($schedulation['date']) || !isset($schedulation['max'])) {
                    continue;
                }
                $now = Carbon::now();
                if ($now->gte($schedulation['date'])) {
                    continue;
                }

                $schedData = [
                    'campaign_id' => $campaign->_id,
                    'scheduled_date' => $schedulation['date'],
                    'calls_limit' => $schedulation['max'],
                    'delivery_speed' => isset($schedulation['sending_time']) ? $schedulation['sending_time'] : null,
                    'recipients' => isset($schedulation['max']) ? $schedulation['max'] : 0,
                ];

                if (isset($schedulation['sending_time']) && $schedulation['sending_time']) {
                    $recipientsPerIteration = $schedulation['max'] > 1 ? $schedulation['max'] - 1 : 1;
                    $schedData['calling_interval_minutes'] = round($schedulation['sending_time'] * 60 / $recipientsPerIteration);
                } else {
                    $schedData['calling_interval_minutes'] = null;
                }
                $sched = Schedulation::create($schedData);
                $sched->scheduled_date = $schedulation['date'];
                $schedulationsToAdd[] = $sched;
            }

            if (count($schedulationsToAdd) > 0) {
                $schedulationDateFormat = max(array_map(function ($item) {
                    return $item->scheduled_date->format('Y-m-d H:i:s');
                }, $schedulationsToAdd));
                $campaign->first_scheduled_date = Carbon::createFromFormat('Y-m-d H:i:s', $schedulationDateFormat, $customerTimezone)->setTimezone($serverTimezine)->format('Y-m-d H:i:s');
            }

            if ($campaign->status == 'saved') {
                if (count($finalPhonenumbers) > 0 && !$shouldUseAllContacts && (!$groupIds || ($groupIds && !count($groupIds)))) {
                    $groupObject = $this->user->addressBookGroups()->create([
                        'name' => $campaignData['campaign_name'] ? $campaignData['campaign_name'] : null,
                    ]);
                    $campaignData['created_group_id'] = $groupObject->_id;
                }
                \App\Models\AddressBookGroup::where('_id', $campaign->created_group_id)->delete();
                $campaign->phonenumbers()->where('status', 'IN_PROGRESS')->whereHas('calls', function ($query) {
                }, '=', 0)->delete();
                $phonenumbersToadd = [];

                $fileId = $this->voiceFileId;
                $file = $this->user->files()->find($fileId);

                $length = $file ? $file->length : 0;
                $length = ($length < 20) ? 20 : $length;

                $userCanAll = true;
                $maxCost = 0;
                $maxGiftCost = 0;

                if(count($finalPhonenumbers) > 0 && !$shouldUseAllContacts && (!$groupIds || ($groupIds && !count($groupIds)))) {
                    $schedulationLimit = 0;
                    $schedulationInterval = [
                        'schedulation_id' => null,
                        'interval' => null
                    ];
                    $usedSchedulationsIds = [];

                    foreach ($finalPhonenumbers as $phonenumber) {
                        $cost = $phonenumber['tariff']['country']['customer_price'] * $length / 60;
                        $smsCost = $phonenumber['tariff']['country']['sms_customer_price'] * ceil(strlen($smsText) / 160);
                        $userCan = false;

                        if ($userSmsTariffs->count()) {
                            $currentCountryTariff = null;
                            foreach ($userSmsTariffs as $userSmsTariff) {
                                if ($userSmsTariff->country_id == $phonenumber['tariff']['country']['_id']) {
                                    $currentCountryTariff = $userSmsTariff;
                                }
                            }
                            if ($currentCountryTariff) {
                                $smsCost = $currentCountryTariff->cost * ceil(strlen($smsText) / 160);
                            }
                        }

                        if ($campaign->type == 'VOICE_MESSAGE') {
                            $userCan = $this->userRepo->canUseGift($phonenumber, $this->user, $maxGiftCost, $cost);
                        }

                        if ($userCan) {
                            $maxGiftCost += $cost;
                        } else {
                            if (!$this->user->bonus_criteria || $this->user->bonus_criteria > $phonenumber['tariff']['best_margin']) {
                                $userCanAll = false;
                            }
                            if ($campaign->type == 'VOICE_MESSAGE') {
                                $maxCost += $cost;
                            }
                            if ($campaign->type == 'SMS') {
                                $maxCost += $smsCost;
                            }
                            if ($campaign->type == 'VOICE_WITH_SMS') {
                                $maxCost += $smsCost + $cost;
                            }
                        }

                        $tariff = $phonenumber['tariff'];
                        $finalNumber = $phonenumber['phonenumber'];

                        $contactObject = $this->user->addressBookContacts()->where('phone_number', $finalNumber)
                            ->first();

                        if (!$contactObject) {
                            $contactData = [
                                'phone_number' => $finalNumber,
                                'tariff_id' => $tariff['_id'],
//                            'type' => $this->campaignDbRepo->checkIsPhoneNumberMobile($finalNumber),
                                'user_id' => $this->user->_id,
                            ];
                            try {
                                $contactObject = \App\Models\AddressBookContact::create($contactData);
                            } catch (\Exception $e) {
                                $contactObject = null;
                            }
                        }
                        if ($contactObject) {
                            $contactObject->groups()->attach($groupObject->_id);
                        }

                        $isUserNumberFromEu = isset($usersCallerId->tariff->country) ? $usersCallerId->tariff->country->is_eu_member : false;
                        $isNumberFromEu = $tariff['country']['is_eu_member'];
                        $isFromNotEuToEu = !$isUserNumberFromEu && $isNumberFromEu;
                        $isFree = $this->shouldPhonenumberBeFree($tariff, $usersCallerId, $campaign, $this->user);

                        $schedulationToSet = null;

                        foreach ($schedulationsToAdd as $addedSchedulation) {
                            if (!in_array($addedSchedulation->_id, $usedSchedulationsIds)) {
                                if ($schedulationLimit == $addedSchedulation->calls_limit) {
                                    array_push($usedSchedulationsIds, $addedSchedulation->_id);
                                    $schedulationLimit = 0;
                                } else {
                                    $schedulationToSet = $addedSchedulation;
                                }
                            }
                        }

                        if ($schedulationToSet) {
                            $toBeCalledAt = Carbon::createFromFormat('Y-m-d H:i:s', $schedulationToSet->scheduled_date, $serverTimezone)->setTimezone($serverTimezone);
                            if ($schedulationInterval['schedulation_id'] == $schedulationToSet->_id) {
                                if ($schedulationToSet->delivery_speed) {
                                    $schedulationInterval['interval'] = $schedulationInterval['interval']->addSeconds($schedulationToSet->calling_interval_minutes);
                                    $toBeCalledAt = $schedulationInterval['interval'];
                                }
                            } else {
                                if ($schedulationToSet->delivery_speed) {
                                    if(!$schedulationInterval['interval']) {
                                        $schedulationInterval['interval'] = Carbon::createFromFormat('Y-m-d H:i:s', $schedulationToSet->scheduled_date, $serverTimezone)->setTimezone($serverTimezone);
                                    }
                                    $schedulationInterval['interval'] = $schedulationInterval['interval']->addSeconds($schedulationToSet->calling_interval_minutes);
                                    $toBeCalledAt = $schedulationInterval['interval'];
                                } else {
                                    $schedulationInterval['schedulation_id'] = $schedulationToSet->_id;
                                    $schedulationInterval['interval'] = Carbon::createFromFormat('Y-m-d H:i:s', $schedulationToSet->scheduled_date, $serverTimezone)->setTimezone($serverTimezone);
                                }
                            }

                            $schedulationLimit++;
                            $schedulationData = [
                                "schedulation_id" => $schedulationToSet->_id,
                                "status" => "IN_PROGRESS",
                                "to_be_called_at" => $toBeCalledAt,
                            ];
                        }
                        else {
                            if (count($schedulationsToAdd)) {
                                $schedulationData = [
                                    "schedulation_id" => null,
                                    "status" => "IDLE",
                                    "to_be_called_at" => null,
                                ];
                            } else {
                                $schedulationData = [
                                    "schedulation_id" => null,
                                    "status" => "IN_PROGRESS",
                                    "to_be_called_at" => null,
                                ];
                            }
                        }

                        $phonenumbersToadd[] = [
                                'campaign_id' => $campaign->_id,
                                'action_type' => $campaign->type,
                                'phone_no' => $finalNumber,
                                'should_put_three_asterisks' => $contactObject->should_put_three_asterisks,
                                'created_at' => date('Y-m-d H:i:s'),
                                'retries' => 0,
                                'tariff_id' => $tariff['_id'],
                                'user_id' => $this->user->_id,
//                        'type' => $this->campaignDbRepo->checkIsPhoneNumberMobile($finalNumber),
                                'is_from_not_eu_to_eu' => $isFromNotEuToEu,
                                'is_free' => $isFree,
                            ] + $schedulationData;
                    }

                    if (count($finalPhonenumbers)){
                        \App\Models\Phonenumber::insert($phonenumbersToadd);
                        $campaign->is_first_run = 0;
                    }
                }

                if ($shouldUseAllContacts) {
                    $recipentsCounter = 0;
                    $contacts = $this->user->addressBookContacts();
                    $contacts = $contacts->selectRaw('count(*) as count, tariff_id, user_id, tariffs.prefix, tariffs.country_id, tariffs.best_margin, countries.customer_price,countries.sms_customer_price')
                        ->groupBy('tariff_id')
                        ->leftJoin('tariffs', 'address_book_contacts.tariff_id', '=', 'tariffs._id')
                        ->leftJoin('countries', 'tariffs.country_id', '=', 'countries._id')
                        ->get();
                    $minimumMargin = $this->user->bonus_criteria;
                    $availableGiftBalance = $this->user->bonus;
                    $maxCost = 0;
                    $maxGiftCost = 0;
                    $userCanAll = true;

                    foreach ($contacts as $contact) {

                        $smsCost = $contact->sms_customer_price * ceil(strlen($smsText) / 160);
                        if ($userSmsTariffs->count()) {
                            $currentCountryTariff = null;
                            foreach ($userSmsTariffs as $userSmsTariff) {
                                if ($userSmsTariff->country_id == $contact->_id) {
                                    $currentCountryTariff = $userSmsTariff;
                                }
                            }
                            if ($currentCountryTariff) {
                                $smsCost = $currentCountryTariff->cost * ceil(strlen($smsText) / 160);
                            }
                        }

                        if ($recipentsCount > $recipentsCounter || is_null($recipentsCount)) {

                            if (is_null($recipentsCount)) {
                                if ($campaign->type == 'VOICE_MESSAGE') {
                                    $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                }
                                if ($campaign->type == 'VOICE_WITH_SMS') {
                                    $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                    $tempCost += $smsCost;
                                }
                                if ($campaign->type == 'SMS') {
                                    $tempCost = $smsCost;
                                }
                            } else {
                                if ($contact->count + $recipentsCounter < $recipentsCount) {
                                    if ($campaign->type == 'VOICE_MESSAGE') {
                                        $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                    }
                                    if ($campaign->type == 'VOICE_WITH_SMS') {
                                        $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                        $tempCost += $smsCost;
                                    }
                                    if ($campaign->type == 'SMS') {
                                        $tempCost = $smsCost;
                                    }
                                } else {
                                    $count = $recipentsCount - $recipentsCounter;
                                    $tempCost = $contact->customer_price * $length * $count / 60;
                                    if ($campaign->type == 'VOICE_MESSAGE') {
                                        $tempCost = $contact->customer_price * $length * $count / 60;
                                    }
                                    if ($campaign->type == 'VOICE_WITH_SMS') {
                                        $tempCost = $contact->customer_price * $length * $count / 60;
                                        $tempCost += $smsCost;
                                    }
                                    if ($campaign->type == 'SMS') {
                                        $tempCost = $smsCost;
                                    }
                                }
                                $recipentsCounter += $contact->count;
                            }

                            if ($contact->best_margin > $minimumMargin && $availableGiftBalance > 0 && $campaign->type == 'VOICE_MESSAGE') {
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
                                if ($contact->best_margin > $minimumMargin) {
                                    $userCanAll = false;
                                }
                                $maxCost += $tempCost;
                            }
                        } else {
                            break;
                        }
                    }
                    if ($maxCost > 0 && !$userCanAll) {
                        $maxCost += $maxGiftCost;
                        $maxGiftCost = 0;
                    }
                }
                elseif ($groupIds) {
                    $recipentsCounter = 0;

                    $campaign->groups()->attach($groupIds);
                    $contacts = $this->user->addressBookContacts();
                    $contacts = $contacts->whereHas('groups', function ($query) use ($groupIds) {
                        $query->whereIn('address_book_groups._id', $groupIds);
                    }, '>', 0);
                    $contacts = $contacts->selectRaw('count(*) as count, tariff_id, user_id, tariffs.prefix, tariffs.country_id, tariffs.best_margin, countries.customer_price, countries.sms_customer_price')
                        ->groupBy('tariff_id')
                        ->leftJoin('tariffs', 'address_book_contacts.tariff_id', '=', 'tariffs._id')
                        ->leftJoin('countries', 'tariffs.country_id', '=', 'countries._id')
                        ->get();

                    $minimumMargin = $this->user->bonus_criteria;
                    $availableGiftBalance = $this->user->bonus;
                    $maxCost = 0;
                    $maxGiftCost = 0;
                    $userCanAll = true;
                    foreach ($contacts as $contact) {

                        $smsCost = $contact->sms_customer_price * ceil(strlen($smsText) / 160);
                        if ($userSmsTariffs->count()) {
                            $currentCountryTariff = null;
                            foreach ($userSmsTariffs as $userSmsTariff) {
                                if ($userSmsTariff->country_id == $contact->_id) {
                                    $currentCountryTariff = $userSmsTariff;
                                }
                            }
                            if ($currentCountryTariff) {
                                $smsCost = $currentCountryTariff->cost * ceil(strlen($smsText) / 160);
                            }
                        }

                        if ($recipentsCount > $recipentsCounter || is_null($recipentsCount)) {
                            if (is_null($recipentsCount)) {
                                if ($campaign->type == 'VOICE_MESSAGE') {
                                    $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                }
                                if ($campaign->type == 'VOICE_WITH_SMS') {
                                    $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                    $tempCost += $smsCost;
                                }
                                if ($campaign->type == 'SMS') {
                                    $tempCost = $smsCost;
                                }
                            } else {
                                if ($contact->count + $recipentsCounter < $recipentsCount) {
                                    if ($campaign->type == 'VOICE_MESSAGE') {
                                        $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                    }
                                    if ($campaign->type == 'VOICE_WITH_SMS') {
                                        $tempCost = $contact->customer_price * $length * $contact->count / 60;
                                        $tempCost += $smsCost;
                                    }
                                    if ($campaign->type == 'SMS') {
                                        $tempCost = $smsCost;
                                    }
                                } else {
                                    $count = $recipentsCount - $recipentsCounter;
                                    if ($campaign->type == 'VOICE_MESSAGE') {
                                        $tempCost = $contact->customer_price * $length * $count / 60;
                                    }
                                    if ($campaign->type == 'VOICE_WITH_SMS') {
                                        $tempCost = $contact->customer_price * $length * $count / 60;
                                        $tempCost += $smsCost;
                                    }
                                    if ($campaign->type == 'SMS') {
                                        $tempCost = $smsCost;
                                    }
                                }
                                $recipentsCounter += $contact->count;
                            }
                            if ($contact->best_margin > $minimumMargin && $availableGiftBalance > 0 && $campaign->type == 'VOICE_MESSAGE') {
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
                                if ($contact->best_margin > $minimumMargin) {
                                    $userCanAll = false;
                                }
                                $maxCost += $tempCost;
                            }
                        } else {
                            break;
                        }
                    }
                    if ($maxCost > 0 && !$userCanAll) {
                        $maxCost += $maxGiftCost;
                        $maxGiftCost = 0;
                    }
                }

                if ($maxCost > 0 && !$userCanAll) {
                    $maxCost += $maxGiftCost;
                    $maxGiftCost = 0;
                }

                $campaign->retained_balance = $maxCost;
                $campaign->retained_gift_balance = $maxGiftCost;
                $campaign->is_gift_being_used = ($maxGiftCost > 0) ? true : false;
            }

            if ($campaign->status == 'stop') {
                if (count($schedulationsToAdd)) {
                    $campaign->status = 'scheduled';
                } else {
                    $campaign->status = 'start';
                }
            }

            DB::transaction(function () use ($campaign) {
                try {
                    $campaign->save();
                    DB::commit();
                } catch (\Exception $e) {
                    \Log::error($e);
                    DB::rollBack();
                }
            }, 3);

            if($campaign->status != 'saved') {
                $this->createCallingInterval($campaign);
            }

            $type = 'INFO';
            $text = 'User with #' . $this->user->_id . ' id has updated campaign #' . $campaign->_id;
            $status = 'CAMPAIGN_EDITED';

            DB::transaction(function () use ($campaign, $type, $text, $status, $oldCampaign) {
                try {
                    $this->messageLogsService->createMessageLogForUpdate($type, $text, $status, $campaign, $oldCampaign);
                    DB::commit();
                } catch (\Exception $e) {
                    \Log::error($e);
                    DB::rollBack();
                }
            }, 3);

            $logData = [
                'campaign_id' => $campaign->_id,
                'user_id' => $this->user->_id,
                'device' => 'WEBSITE',
                'action' => 'MESSAGES',
                'description' => 'User updated campaign',
            ];

            DB::transaction(function () use ($logData) {
                try {
                    $this->activityLogRepo->createActivityLog($logData);
                    DB::commit();
                } catch (\Exception $e) {
                    \Log::error($e);
                    DB::rollBack();
                }
            }, 3);

        } catch (\Exception $e) {
            \Log::error('Error In UpdateCampaign');
            \Log::error($e);
        }

        DB::transaction(function () {
            try {
                DB::statement('DELETE FROM `schedulations` WHERE `campaign_id` IS NULL');
                DB::commit();
            } catch (\Exception $e) {
                \Log::error($e);
                DB::rollBack();
            }
        }, 3);
    }

    private function shouldPhonenumberBeFree($tariff, $usersCallerId, $campaign, $user)
    {
        $country = $usersCallerId->tariff->country;
        if ($tariff['country_id'] != $usersCallerId->tariff->country_id) {
            return false;
        }
        if (!$user->country) {
            return false;
        }

        if ($tariff['best_margin'] < $country->free_call_minimum_margin) {
            return false;
        }

        $freeMessagesPerDay = $country->web_free_messages_count_per_day;
        $freeMessageMaxDuration = $country->web_free_message_max_duration;
        if ($campaign->transfer_digit ||
            $campaign->callback_digit ||
            $campaign->replay_digit ||
            $campaign->do_not_call_digit ||
            $campaign->playback_count > 0 ||
            $campaign->voiceFile->length > $freeMessageMaxDuration
        ) {
            return false;
        }

        if (!$user->last_used_free_credit_at || !Carbon::createFromFormat('Y-m-d H:i:s', $user->last_used_free_credit_at)->isToday()) {
            $user->free_calls_made = 0;
        }
        if ($user->free_calls_made >= $freeMessagesPerDay) {
            return false;
        }
        $user->last_used_free_credit_at = Carbon::now();
        $user->free_calls_made++;
        $user->save();
        return true;
    }

    private function createCallingInterval(&$campaign)
    {
        $user = $this->user;
        $serverTimezone = $this->serverTimezone;
        $schedulationsToConsider = $campaign->schedulations()
            ->where('is_finished', 0)->get();
        $customerTimezone = $user->timezone;
        if (count($schedulationsToConsider) == 0) {
            if ($campaign->status == 'start') {
                $campaign->phonenumbers()
                    ->whereNull('schedulation_id')
                    ->where(['status' => 'IDLE'])
                    ->chunk(500, function ($phoneNumbers) {
                        $phoneNumbersIds = [];
                        foreach ($phoneNumbers as $phoneNumber) {
                            $phoneNumbersIds[] = $phoneNumber->_id;
                        }
                        Phonenumber::whereIn('_id', $phoneNumbersIds)
                            ->update(['status' => 'IN_PROGRESS']);
                    });
            }
            return;
        }
        foreach ($schedulationsToConsider as $schedulation) {
            $callsLimit = $schedulation->calls_limit;
            $deliverySpeed = $schedulation->delivery_speed;
            $scheduledDate = $schedulation->scheduled_date;
            $toBeCalledAtInServerTime = Carbon::createFromFormat('Y-m-d H:i:s', $scheduledDate, $customerTimezone)->setTimezone($serverTimezone);

            if (!$deliverySpeed) {
                while ($callsLimit > 0) {
                    $limit = 500;
                    if($callsLimit <= 500) {
                        $limit = $callsLimit;
                    }
                    DB::statement("UPDATE `phonenumbers` SET `to_be_called_at`= ?, `schedulation_id`= ?, `status`='IN_PROGRESS' 
                                    WHERE `campaign_id` = ? and `status` IN ('IN_PROGRESS', 'IDLE') and `schedulation_id` IS NULL LIMIT ?",
                        [$toBeCalledAtInServerTime,$schedulation->_id,$campaign->_id, $limit]);

                    $callsLimit -= 500;
                }
            }
            else {
                $values = ' ';
                $counter = 0;
                while ($callsLimit > 0) {
                    $limit = 500;
                    if($callsLimit <= 500) {
                        $limit = $callsLimit;
                    }

                    $phonenumbers = DB::select("SELECT * FROM `phonenumbers` WHERE `campaign_id` = ? and `status` IN ('IN_PROGRESS', 'IDLE') and `schedulation_id` IS NULL ORDER BY `_id` DESC  LIMIT ?", [$campaign->_id, $limit]);
                    foreach ($phonenumbers as $key => $phoneNumber) {
                        $values .= "( " . $phoneNumber->_id . " ,'" . $toBeCalledAtInServerTime . "'),";
                        $counter++;
                        $toBeCalledAtInServerTime = $toBeCalledAtInServerTime->addSeconds($schedulation->calling_interval_minutes);
                    }

                    if ($counter) {
                        $values = rtrim($values, ',');
                        $statement = "INSERT INTO phonenumbers (_id,to_be_called_at) 
                                    VALUES " . $values . "
                                        ON DUPLICATE KEY UPDATE to_be_called_at = VALUES(to_be_called_at) , status = 'IN_PROGRESS' , schedulation_id = " . $schedulation->_id . " ;";
                        \DB::statement($statement);
                        $values = ' ';
                    }

                    $callsLimit -= 500;
                }
            }
        }
        DB::transaction(function () use ($campaign) {
            try {
                $campaign->phonenumbers()->whereNull('schedulation_id')
                    ->update(['status' => 'IDLE']);
                DB::commit();
            } catch (\Exception $e) {
                \Log::error($e);
                DB::rollBack();
            }
        }, 3);
    }
}
