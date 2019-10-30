angular.module('callburnApp').service('CampaignComposeService',
	function($sce, Restangular, $state, ModalService, $rootScope,CallBournModal,notify){

	var composeData = {};
	composeData.editingCampaign = null;
	composeData.reusingCampaign = null;
	composeData.campaignData = {phonenumbers: []};
	composeData.finalStepData = {};
	composeData.ttsLanguages = [];
	composeData.removed_phonenumbers = [];
	composeData.audioTemplates = [];
	composeData.interactionsForModal = [];
	composeData.composeStep = 1;


	composeData.replayDigit    = {showNumbersSelect: false, onOff: 'off', checkboxChecked: false};
	composeData.transferDigit  = {showNumbersSelect: false, onOff: 'off', checkboxChecked: false};
	composeData.callbackDigit  = {showNumbersSelect: false, onOff: 'off', checkboxChecked: false};
	composeData.doNotCallDigit = {showNumbersSelect: false, onOff: 'off', checkboxChecked: false};

	composeData.getAudioSource = function(audio){
		return $sce.trustAsResourceUrl('/uploads/audio/' + audio.map_filename);
	};

	composeData.doValitation = function(action){

		//DO VALIDATION
		var isValid = true;
		var errorMessage = '';
		var campaignData = composeData.campaignData;
		if(composeData.callbackDigit.onOff == 'on'){
			if(!campaignData.callback_digit || !campaignData.callback_voice_file_id){
				isValid = false;
				errorMessage = 'callback_voice_file_required_with_callback_digit';
			}
		}
		if(composeData.doNotCallDigit.onOff == 'on'){
			if(!campaignData.do_not_call_digit || !campaignData.do_not_call_voice_file_id){
				isValid = false;
				errorMessage = 'donotcall_voice_file_required_with_donotcall_digit';
			}
		}
		if(composeData.transferDigit.onOff == 'on'){
			if(!campaignData.transfer_digit || !campaignData.transfer_options){
				isValid = false;
				errorMessage = 'transfer_options_required_with_transfer_digit';
			}
		}
		if(composeData.replayDigit.onOff == 'on'){
			if(!campaignData.replay_digit){
				isValid = false;
				errorMessage = 'replay_digit_is_activated_but_not_selected';
			}
		}
		if(!isValid){
          	notify({message: errorMessage, classes: ['notification-alert-danger']});
          	return;
		}
		//END DO VALIDATION
		//CHECK IF ANY INTERACTION EXISTS


		var isAnyActive = false;
		if(composeData.replayDigit.onOff == 'on'){
			isAnyActive = true;
			var replayDigitObject = {action: 'Replay Voice Message', keypress: composeData.campaignData.replay_digit};
			composeData.interactionsForModal.push(replayDigitObject);
		}
		if(composeData.transferDigit.onOff == 'on'){
			isAnyActive = true;
			var transferDigitObject = {action: 'Transfer Voice Message', keypress: composeData.campaignData.transfer_digit};
			composeData.interactionsForModal.push(transferDigitObject);
		}
		if(composeData.callbackDigit.onOff == 'on'){
			isAnyActive = true;
			var callbackDigitObject = {action: 'Callback', keypress: composeData.campaignData.callback_digit};
			composeData.interactionsForModal.push(callbackDigitObject);
		}
		if(composeData.doNotCallDigit.onOff == 'on'){
			isAnyActive = true;
			var doNotCallDigitObject = {action: 'Blacklist', keypress: composeData.campaignData.do_not_call_digit};
			composeData.interactionsForModal.push(doNotCallDigitObject);
		}

		if(!isAnyActive){
			composeData.proceedToSaving(action)
		} else{
			$rootScope.showBlurEffect = true;
			CallBournModal.open({
				scope: {},
				templateUrl: "/app/modals/camping-batch/confirm-interactions.html"
			},function (scope) {

			});
		}
		//END CHECK IF ANY INTERACTION EXISTS
	}

	composeData.proceedToSaving = function(action){
		var campaignData = composeData.campaignData;
		campaignData.is_replay_active = composeData.replayDigit.onOff == 'on' ? true : false;
		campaignData.is_transfer_active = composeData.transferDigit.onOff == 'on' ? true : false;
		campaignData.is_callback_active = composeData.callbackDigit.onOff == 'on' ? true : false;
		campaignData.is_donotcall_active = composeData.doNotCallDigit.onOff == 'on' ? true : false;
		if(!campaignData.caller_id){
			alert('Caller id is required.');
			return;
		}
		switch(action){
			case 'saved':
				campaignData.schedulations = null;
				break;
			case 'scheduled':
				for(index in campaignData.schedulations){
					campaignData.schedulations[index].date = moment(campaignData.schedulations[index].date).format('YYYY-MM-DD HH:mm:ss');
				}
				break;
			case 'start':
				campaignData.schedulations = null;
				break;
			case 'preview':
				var dataCopy = {};
				for(index in campaignData){
					dataCopy[index] = campaignData[index];
				}
				dataCopy.phonenumbers = [campaignData.caller_id];

				Restangular.all('campaigns/create-campaign').post(dataCopy).then(function(data){
					if(data.resource.error.no == 0){
						alert('Call made');

					}
				});
				return;
		}
		if(composeData.editingCampaign){
			var url = 'campaigns/update-campaign';
		} else{
			var url = 'campaigns/create-campaign';
		}
		$rootScope.startLoader();
		Restangular.all(url).post(campaignData).then(function(data){
			$rootScope.showBlurEffect = true;
			$rootScope.stopLoader();
			CallBournModal.open({
			 scope: {},
			 templateUrl: "/app/modals/camping-batch/voice-message-sent.html",
			 }, function (scope) {
			 /**
			 * close modal and  redirect to $state camping .overviw
			 */

			 scope.goToOverview = function () {
			 CallBournModal.close();
			 $state.go('campaign.overview');
			 };
			 /**
			 * close modal
			 */

			 scope.dismissModal = function () {
			 CallBournModal.close();
			 }

			 });
		});
	}
	return composeData;
})