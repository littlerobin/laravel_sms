<?php

namespace App\Services;

use App\Models\Campaign;

class CampaignService
{

    /**
     * Object of Campaign Class for working with storage.
     *
     * @var Campaign
     */
    private $campaign;

    /**
     * Create new instance of CampaignService class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->campaign = new Campaign();
    }

    /**
     * Store new campaign Data into storage.
     *
     * @param array $campData
     * @return Campaign
     */
    public function createCampaign($campData)
    {
        $campaign = $this->campaign->create($campData);
        //$this->createNewTableForCampaign($campaign->_id);
        return $campaign;
    }

    /**
     * Get campaign by primary key.
     * @param integer $id
     * @return Campaign
     */
    public function getCampaignByPK($id)
    {
        return $this->campaign->where('_id', $id)
            ->with(['voiceFile', 'callbackFile', 'doNotCallFile', 'user'])
            ->first();
    }

    /**
     * Update campaign data by id.
     *
     * @param integer $id
     * @param array $campData
     * @return bool
     */
    public function updateCampaign($id, $campData)
    {
        return $this->getCampaignByPK($id)->update($campData);
    }

    /**
     * Update campaign caller-id by id.
     *
     * @param integer $id
     * @param string $callerId
     * @return bool
     */
    public function updateCampaignCallerId($id, $callerId)
    {
        $campData = ['caller_id' => $callerId];
        return $this->getCampaignByPK($id)->update($campData);
    }

    /**
     * Get total recipients if the source of the campaign
     * is groups and its still first run
     *
     * @param Campaign $campaign
     * @return integer
     */
    public function getTotalRecipientsIfGroups($campaign)
    {
        //If its not first run of the campaign, it means that the recipients already added to the
        //phonenumebrs database, so we will let angular part to count this
        //If there is no groups and should_use_all_contacts is false, it means that recipients
        // were added directly when creating, so we will let angular handle this.
        if (!$campaign->is_first_run || ($campaign->groups->count() == 0 && !$campaign->should_use_all_contacts)) {
            return null;
        }
        //If the should_use_all_contacts flag was set to true
        //we will show all count of users contacts
        if ($campaign->should_use_all_contacts) {
            return $campaign->user->addressBookContacts()->count();
        }
        //If code arrives here means that the source was groups,
        //So we need to show the count of recipients in groups .
        $groupsIds = $campaign->groups->lists('_id')->all();

        $query = app('db')->table('address_book_contacts')
            ->leftJoin(
                'address_book_group_contact',
                'address_book_group_contact.address_book_contact_id',
                '=',
                'address_book_contacts._id'
            )
            ->select(app('db')->raw('COUNT(DISTINCT address_book_group_contact.address_book_contact_id) as count'))
            ->where('address_book_contacts.user_id', $campaign->user_id)
            ->whereIn('address_book_group_contact.address_book_group_id', $groupsIds)
            ->first();

        return is_null($query) ? 0 : $query->count;
    }

    /**
     * Validate campaign data for create new campaign.
     * @param array $request
     * @return stdClass
     */
    public static function createCampaignValidation($request)
    {

        $campaignDbRepo = new \App\Services\CampaignDbService;

        $data = new \stdClass;

        $user = \Auth::user();
        $name = $request->get('campaign_name');
        $callerId = $request->get('caller_id');
        $voiceFileId = $request->get('campaign_voice_file_id');
        $transferDigit = $request->get('is_transfer_active') ? $request->get('transfer_digit') : null;
        $transferOptions = $request->get('is_transfer_active') ? $request->get('transfer_options') : null;
        $replayDigit = $request->get('is_replay_active') ? $request->get('replay_digit') : null;
        $callbackDigit = $request->get('is_callback_active') ? $request->get('callback_digit') : null;
        $callbackVoiceFileId = $request->get('is_callback_active') ? $request->get('callback_voice_file_id') : null;
        $doNotCallDigit = $request->get('is_donotcall_active') ? $request->get('do_not_call_digit') : null;
        $doNotCallVoiceFileId = $request->get('is_donotcall_active') ? $request->get('do_not_call_voice_file_id') : null;
        $liveAnswersOnly = $request->get('live_answer_only');
        $playbackCount = $request->get('playback_count');
        $totalRetries = 5;
        $liveTransferLimit = $request->get('live_transfer_limit');
        $timezone = $request->get('timezone', $user->timezone);
        $campaignStatus = $request->get('status', 'start');
        $campaignType = $request->get('type','VOICE_MESSAGE');
        $campaignSmsText = $request->get('sms_text');
        $campaignSenderName = $request->get('sender_name');
        $getEmailNotifications = $request->get('get_email_notifications', false);
        $same_sms_text = $request->get('same_sms_text');
        $shouldShuffle = $request->get('should_shuffle', false);
        $manualPhonenumbers = array_unique($request->get('phonenumbers', []));
        $finalPhonenumbers = $campaignDbRepo->getValidPhonenumbers($manualPhonenumbers, $user);
        $groupIds = array_keys(array_filter($request->get('selected_groups', [])));
        $shouldUseAllContacts = $request->get('all_contacts', 0);
        $shouldUseAllContacts ? $shouldUseAllContacts : 0;
        $campaignStatus = $request->get('status');
        $saveAsDraft = $campaignStatus == 'saved' ? true : false;

        if (!$shouldUseAllContacts && $finalPhonenumbers->count() == 0 && count($groupIds) == 0 && !$saveAsDraft) {
            $data->error = -6;
            $data->text = 'there__is__no_valid_phonenumber_2';
            return $data;
        }

        $maxCost = $request->get('max_cost');
        $maxGiftCost = $request->get('max_gift_cost');
        $remainingRepeats = $request->get('remaining_repeats');
        $repeatDaysInterval = $request->get('repeat_days_interval');
        $schedulations = $request->get('schedulations');

        $schedulationOriginalData = $schedulations ? json_encode($schedulations) : null;

        if ($remainingRepeats > 0) {
            $groupingType = 'REPEAT';
        } else {
            $groupingType = 'NONE';
        }

        $finalSchedulation = MessageValidationService::getFinalSchedulation($schedulations, $finalPhonenumbers, $timezone);
        if ($campaignStatus == 'scheduled' && count($finalSchedulation) == 0) {
            $data->error = -7;
            $data->text = 'no_valid_schedulations';
            return $data;
        }

        $validatorData = $request->all();
        $Errors = MessageValidationService::Validator($validatorData, $saveAsDraft, 'create',$campaignType);

        if ($Errors) {
            $data->error = $Errors->errorNumber;
            $data->text = $Errors->errorMessage;

        } else {
            $data->error = 0;
            $data->text = 'VALID_1';

        }

        if ($data->error != 0) {
            return $data;

        }

        $callerId = ltrim($callerId, '+');
        $usersCallerId = $user->numbers()->where('phone_number', $callerId)->with('tariff')->first();
        if (!$usersCallerId) {
            $data->error = -11;
            $data->text = 'caller_id_is_not_registered_for_the_user';
            return $data;

        }

        if ($campaignStatus != 'scheduled' && !MessageValidationService::haveEnoughBalanceForCall($campaignStatus, $maxCost, $maxGiftCost, $user)) {
            $data->error = -12;
            $data->text = 'balance_is_not_enough_for_creating_message';
            return $data;
        }

        $randomBatchString = str_random(20);
        if ($remainingRepeats > 0) {
            $repeatBatchGrouping = $randomBatchString;
        } else {
            $repeatBatchGrouping = $request->get('repeat_batch_grouping', $randomBatchString);
        }

        $campaignData = [
            'campaign_name' => $name,
            'caller_id' => $callerId,
            'type' => $campaignType,
            'sms_text' => $campaignSmsText,
            'sender_name' => $campaignSenderName,
            'campaign_voice_file_id' => $voiceFileId,
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
            'status' => $campaignStatus,
            'get_email_notifications' => $getEmailNotifications,
            'repeat_batch_grouping' => $repeatBatchGrouping,
            'retained_balance' => $maxCost,
            'retained_gift_balance' => $maxGiftCost,
            'remaining_repeats' => $remainingRepeats,
            'repeat_days_interval' => $repeatDaysInterval,
            'grouping_type' => $groupingType,
            'playback_count' => $playbackCount,
            'schedulation_original_data' => $schedulationOriginalData,
            'should_use_all_contacts' => $shouldUseAllContacts,
            'should_shuffle' => $shouldShuffle,
            'same_sms_text' => $same_sms_text
        ];

        $data->error = 0;
        $data->campaign = $campaignData;
        $data->randomBatchString = $randomBatchString;
        $data->finalPhonenumbers = $finalPhonenumbers;
        $data->finalSchedulation = $finalSchedulation;
        $data->usersCallerId = $usersCallerId;
        $data->shouldUseAllContacts = $shouldUseAllContacts;
        $data->groupIds = $groupIds;

        return $data;

    }

    /**
     * Validate campaign data for update
     * @param array $request
     * @return stdClass
     */
    public static function updateCampaignValidation($request)
    {

        $campaignDbRepo = new \App\Services\CampaignDbService;

        $data = new \stdClass;
        $user = \Auth::user();
        $callerId = $request->get('caller_id');
        $name = $request->get('campaign_name');
        $voiceMessageId = $request->get('campaign_voice_file_id');
        $transferDigit = $request->get('is_transfer_active') ? $request->get('transfer_digit') : null;
        $transferOptions = $request->get('is_transfer_active') ? $request->get('transfer_options') : null;
        $replayDigit = $request->get('is_replay_active') ? $request->get('replay_digit') : null;
        $callbackDigit = $request->get('is_callback_active') ? $request->get('callback_digit') : null;
        $callbackVoiceFileId = $request->get('is_callback_active') ? $request->get('callback_voice_file_id') : null;
        $doNotCallDigit = $request->get('is_donotcall_active') ? $request->get('do_not_call_digit') : null;
        $doNotCallVoiceFileId = $request->get('is_donotcall_active') ? $request->get('do_not_call_voice_file_id') : null;
        $liveAnswersOnly = $request->get('live_answer_only');
        $playbackCount = $request->get('playback_count');
        $same_sms_text = $request->get('same_sms_text');
        $totalRetries = 5;
        $campaignType = $request->get('type','VOICE_MESSAGE');
        $campaignSmsText = $request->get('sms_text');
        $campaignSenderName = $request->get('sender_name');
        $liveTransferLimit = $request->get('live_transfer_limit');
        $timezone = $request->get('timezone'); //diff
        $campaignStatus = $request->get('status'); //diff
        $getEmailNotifications = $request->get('get_email_notifications'); //diff
        $manualPhonenumbers = array_unique($request->get('phonenumbers', []));
        $groupIds = array_keys(array_filter($request->get('selected_groups', [])));
        if($campaignStatus == 'scheduled') {
            $finalPhonenumbers = collect([true]);
        } else {
            $finalPhonenumbers = $campaignDbRepo->getValidPhonenumbers($manualPhonenumbers, $user);
        }
        //$shouldUseAllContacts = in_array('ALL', $groupIds);
        $shouldUseAllContacts = $request->get('all_contacts', 0);
        $shouldUseAllContacts ? $shouldUseAllContacts : 0;
        $campaignId = $request->get('campaign_id');
        $saveAsDraft = $campaignStatus == 'saved' ? true : false;

        if (!$shouldUseAllContacts && $finalPhonenumbers->count() == 0 && count($groupIds) == 0 && !$saveAsDraft) {
            $data->error = -6;
            $data->text = 'there_is_no_valid_phonenumber';
            return $data;
        }

        $maxCost = $request->get('max_cost');
        $maxGiftCost = $request->get('max_gift_cost');
        $remainingRepeats = $request->get('remaining_repeats');
        $repeatDaysInterval = $request->get('repeat_days_interval');
        $schedulations = $request->get('schedulations');
        // dd($schedulations);
        $schedulationOriginalData = $schedulations ? json_encode($schedulations) : null;

        $finalSchedulation = MessageValidationService::getFinalSchedulation($schedulations, $finalPhonenumbers, $timezone);
        // dd($finalSchedulation);
        if ($campaignStatus == 'scheduled' && count($finalSchedulation) == 0) {
            $data->error = -7;
            $data->text = 'no_valid_schedulations';
            return $data;
        }
        $campaign = $user->campaigns()->with(['schedulations', 'groups'])->where('_id', $campaignId)
            ->whereIn('status', ['saved', 'stop', 'scheduled', 'schedulation_idle'])
            ->first();
        if (!$campaign) {

            $data->error = -13;
            $data->text = 'Campaign__does__not_exist';

            return $data;
        }
        if ($user->_id != $campaign->user_id) {
            $data->error = -14;
            $data->text = 'this_campaign__does_not__belong_to_you';

            return $data;
        }

        $callerId = ltrim($callerId, '+');

        $ValidatorData = [
            'caller_id' => $callerId,
            'voice_message' => $voiceMessageId,
            'transfer_digit' => $transferDigit,
            'transfer_options' => $transferOptions,
            'callback_digit' => $callbackDigit,
            'playback_count' => $playbackCount,
            'callback_digit_file_id' => $callbackVoiceFileId,
            'do_not_call_digit_file_id' => $doNotCallDigit,
            'do_not_call_digit' => $doNotCallVoiceFileId,
        ];

        $Errors = MessageValidationService::Validator($ValidatorData, $saveAsDraft, 'update');

        if ($Errors) {
            $data->error = $Errors->errorNumber;
            $data->text = $Errors->errorMessage;

            return $data;
        }

        $usersCallerId = $user->numbers()->where('phone_number', $callerId)->first();
        if (!$usersCallerId) {
            $data->error = -11;
            $data->text = 'caller_id_is_not_registered_for_the_user';

            return $data;
        }

        if ($campaignStatus != 'scheduled' && !MessageValidationService::haveEnoughBalanceForCall($campaignStatus, $maxCost, $maxGiftCost, $user)) {
            $data->error = -12;
            $data->text = 'balance_is_not_enough_for_creating_message';

            return $data;
        }
        if ($remainingRepeats > 0) {
            $repeatsGroupingString = $campaign->repeat_batch_grouping ? $campaign->repeat_batch_grouping : str_random(20);
        } else {
            $repeatsGroupingString = str_random(20);
        }

        $campaignData = [
            'campaign_name' => $name,
            'caller_id' => $callerId,
            'campaign_voice_file_id' => $voiceMessageId,
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
            'user_id' => $user->_id,
            'type' => $campaignType,
            'sms_text' => $campaignSmsText,
            'sender_name' => $campaignSenderName,
            'timezone' => $timezone,
            'status' => $campaignStatus,
            'remaining_repeats' => $remainingRepeats,
            'repeat_days_interval' => $repeatDaysInterval,
            'repeat_batch_grouping' => $repeatsGroupingString,
            'playback_count' => $playbackCount,
            'schedulation_original_data' => $schedulationOriginalData,
            'should_use_all_contacts' => $shouldUseAllContacts,
            'same_sms_text' => $same_sms_text
        ];

        $data->error = 0;
        $data->getEmailNotifications = $getEmailNotifications;
        $data->campaignId = $campaignId;
        $data->campaign = $campaign;
        $data->campaignData = $campaignData;
        $data->remainingRepeats = $remainingRepeats;
        $data->finalPhonenumbers = $finalPhonenumbers;
        $data->usersCallerId = $usersCallerId;
        $data->finalSchedulation = $finalSchedulation;
        $data->groupIds = $groupIds;
        return $data;
    }

    /**
     * Validate campaign data for update
     * @param array $request
     * @return array
     */
    public static function basicValidationUpdateCampaign($updateData)
    {
        //dd($updateData);
        $data = new \stdClass();
        $user = \Auth::user();
        $callerId = $updateData['caller_id'];
        $name = $updateData['campaign_name'];
        $voiceMessageId = $updateData['campaign_voice_file_id'];
        $transferDigit = $updateData['is_transfer_active'] ? $updateData['transfer_digit'] : null;
        $transferOptions = $updateData['is_transfer_active'] ? $updateData['transfer_options'] : null;
        $replayDigit = $updateData['is_replay_active'] ? $updateData['replay_digit'] : null;
        $callbackDigit = isset($updateData['is_callback_active']) && $updateData['is_callback_active'] ? $updateData['callback_digit'] : null;
        $callbackVoiceFileId = isset($updateData['is_callback_active']) && $updateData['is_callback_active'] ? $updateData['callback_voice_file_id'] : null;
        $doNotCallDigit = $updateData['is_donotcall_active'] ? $updateData['do_not_call_digit'] : null;
        $doNotCallVoiceFileId = $updateData['is_donotcall_active'] ? $updateData['do_not_call_voice_file_id'] : null;
//        $liveAnswersOnly = $updateData['live_answer_only'] ?? null;
//        $playbackCount = $updateData['playback_count'] ?? null;
        $totalRetries = 5;
//        $liveTransferLimit = $updateData['live_transfer_limit'] ?? null;
        $campaignId = $updateData['campaign_id'];
//        $remainingRepeats = $updateData['remaining_repeats'] ?? null;
//        $repeatDaysInterval = $updateData['repeat_days_interval'] ?? null;
        $liveAnswersOnly = $updateData['live_answer_only'] ? $updateData['live_answer_only'] : null;
        $playbackCount = $updateData['playback_count'] ? $updateData['playback_count'] : null;
        $liveTransferLimit = $updateData['live_transfer_limit'] ? $updateData['live_transfer_limit'] : null;
        $remainingRepeats = $updateData['remaining_repeats'] ? $updateData['remaining_repeats'] : null;
        $repeatDaysInterval = $updateData['repeat_days_interval'] ? $updateData['repeat_days_interval'] : null;
        $saveAsDraft = $updateData['status'] == 'saved' ? true : false;

        $campaign = $user->campaigns()->where('_id', $campaignId)
            ->whereIn('status', ['saved', 'stop', 'scheduled', 'schedulation_idle'])
            ->first();
        if ($user->_id != $campaign->user_id) {
            $data->error = -14;
            $data->text = 'this_campaign__does_not__belong_to_you';
            return $data;
        }

        $callerId = ltrim($callerId, '+');

        $ValidatorData = [
            'caller_id' => $callerId,
            'voice_message' => $voiceMessageId,
            'transfer_digit' => $transferDigit,
            'transfer_options' => $transferOptions,
            'callback_digit' => $callbackDigit,
            'playback_count' => $playbackCount,
            'callback_digit_file_id' => $callbackVoiceFileId,
            'do_not_call_digit_file_id' => $doNotCallDigit,
            'do_not_call_digit' => $doNotCallVoiceFileId,
        ];

        $Errors = MessageValidationService::Validator($ValidatorData, $saveAsDraft, 'update');

        if ($Errors) {
            $data->error = $Errors->errorNumber;
            $data->text = $Errors->errorMessage;
            return $data;
        }
        //dd($callerId);
        $usersCallerId = $user->numbers()->where('phone_number', $callerId)->first();
        if (!$usersCallerId) {
            $data->error = -11;
            $data->text = 'caller_id_is_not_registered_for_the_user';
            return $data;
        }

        if ($remainingRepeats > 0) {
            $repeatsGroupingString = $campaign->repeat_batch_grouping ? $campaign->repeat_batch_grouping : str_random(20);
        } else {
            $repeatsGroupingString = str_random(20);
        }

        $campaignData = [
            'campaign_name' => $name,
            'caller_id' => $callerId,
            'campaign_voice_file_id' => $voiceMessageId,
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
            'user_id' => $user->_id,
            'remaining_repeats' => $remainingRepeats,
            'repeat_days_interval' => $repeatDaysInterval,
            'repeat_batch_grouping' => $repeatsGroupingString,
            'playback_count' => $playbackCount,
        ];

        return $campaignData;
    }
}
