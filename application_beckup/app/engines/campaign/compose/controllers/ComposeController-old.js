angular.module('callburnApp').controller('ComposeController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', '$stateParams', 'ttsLanguages',  'notify',
			'FileUploader', '$sce', '$timeout', 'audioFiles', 'editingCampaign', 'reusingCampaign',
	function($scope,   $rootScope,   $state,    Restangular,   $stateParams,   ttsLanguages,  notify,  
			 FileUploader,   $sce,   $timeout,   audioFiles,   editingCampaign,   reusingCampaign){

	$scope.goToNotification = $rootScope.goToNotification;
	$scope.todaysDate = moment();
	$rootScope.currentActiveRoute = 'campaign';

	$scope.audioFiles = [];
	$scope.selectedAudio = null;

	
	$rootScope.footerData = {
		first:  '<span>Step 1</span>' + 
				'<span>Compose your message</span>',
		second: '<span>Step 2</span>' + 
				'<span>Choose your receipents</span>',
		third:  '<span>Step 3</span>' + 
				'<span>Review, add functionalities and send</span>'
	}


	$scope.composeStep = 1;
	if(reusingCampaign){
		$scope.isEdit = true;
		reusingCampaign = reusingCampaign.resource.campaign;
		$scope.finalStepData = {};
		$scope.campaignData = {
			caller_id: $rootScope.currentUser.numbers[0].phone_number,
			get_email_notifications: true
		};
		var reusingSource = $scope.reusingSource = $stateParams.reusing_source;
		if(reusingSource == 'message') {
			$scope.finalStepData.voiceFile = reusingCampaign.voice_file;
			$scope.campaignData.voice_file = reusingCampaign.voice_file;
			$scope.campaignData.campaign_voice_file_id = reusingCampaign.voice_file._id;
			$scope.composeStep = 2;
		}
		if(reusingSource == 'receipents') {
			$scope.currentPhonenumbers = reusingCampaign.phonenumbers;
			$scope.campaignData.current_phonenumbers_count = reusingCampaign.phonenumbers.length;
			$scope.campaignData.removed_phonenumbers = [];
			$scope.finalStepData.numbersCount = reusingCampaign.phonenumbers.length;
			$scope.composeStep = 1;
		}
		if(reusingSource == 'both'){
			$scope.finalStepData.voiceFile = reusingCampaign.voice_file;
			$scope.campaignData.voice_file = reusingCampaign.voice_file;
			$scope.campaignData.campaign_voice_file_id = reusingCampaign.voice_file._id;
			$scope.currentPhonenumbers = reusingCampaign.phonenumbers;
			$scope.campaignData.current_phonenumbers_count = reusingCampaign.phonenumbers.length;
			$scope.campaignData.removed_phonenumbers = [];
			$scope.finalStepData.numbersCount = reusingCampaign.phonenumbers.length;
			$scope.composeStep = 3;
		}
	}
	else if(editingCampaign){
		$scope.isEdit = true;
		$scope.composeStep = 3;
		editingCampaign = editingCampaign.resource.campaign;
		$scope.currentPhonenumbers = editingCampaign.phonenumbers;
		$scope.campaignData = {
			campaign_id: editingCampaign._id,
			callback_digit: editingCampaign.callback_digit,
			callback_voice_file_id: editingCampaign.callback_digit_file_id,
			callback_file: editingCampaign.callback_file,
			caller_id: editingCampaign.caller_id,
			campaign_name: editingCampaign.campaign_name,
			campaign_voice_file_id: editingCampaign.campaign_voice_file_id,
			do_not_call_digit: editingCampaign.do_not_call_digit,
			do_not_call_voice_file_id: editingCampaign.do_not_call_digit_file_id,
			get_email_notifications: editingCampaign.get_email_notifications,
			replay_digit: editingCampaign.replay_digit,
			timezone: editingCampaign.timezone,
			transfer_digit: editingCampaign.transfer_digit,
			transfer_options: editingCampaign.transfer_option,
			user_id: editingCampaign.user_id,
			voice_file: editingCampaign.voice_file,
			schedulations: editingCampaign.schedulations,
			removed_phonenumbers: [],
			current_phonenumbers_count: editingCampaign.phonenumbers.length,
			sending_time: editingCampaign.delivery_speed
		};
		$scope.finalStepData = {
			voiceFile: editingCampaign.voice_file,
			numbersCount: editingCampaign.total_phonenumbers_loaded,
		}
	} else{
		$scope.campaignData = {
			caller_id: $rootScope.currentUser.numbers[0].phone_number,
			get_email_notifications: true
		};
		$scope.finalStepData = {};

		if($stateParams.audioFile){
			$scope.composeStep = 2;
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + $stateParams.audioFile._id),
				file: $stateParams.audioFile
			}
			$scope.audioFiles.push(audioData);
			$scope.selectedAudio = $stateParams.audioFile._id;
			$scope.finalStepData.voiceFile = $stateParams.audioFile;
			$scope.campaignData.voice_file = $stateParams.audioFile;
		}
	}

	
	$scope.audioTemplates = audioFiles.resource.files;
	$scope.recordedAudio = new Audio();
	$scope.uploadingAudioName = '';
	$scope.ttsLanguages = ttsLanguages.resource.languages;


	$rootScope.previousStep = function(){
		$scope.composeStep = $scope.composeStep - 1;
	}
	$rootScope.nextStep = function(){
		switch($scope.composeStep){
			case 1:
				$scope.goToStep2();
				break;
			case 2:
				$scope.goToStep3();
				break;
		}
	}
	
	$scope.$watch('composeStep', function(newVal, oldVal){
		if(newVal == 1){
			$rootScope.isFooter1Active = true;
			$rootScope.isFooter2Active = false;
			$rootScope.isFooter3Active = false;
			$rootScope.showPreviousIcon = false;
			$rootScope.showNextIcon = true;
		}
		if(newVal == 2){
			$rootScope.isFooter1Active = false;
			$rootScope.isFooter2Active = true;
			$rootScope.isFooter3Active = false;

			$rootScope.showPreviousIcon = true;
			$rootScope.showNextIcon = true;
		}
		if(newVal == 3){
			$rootScope.isFooter1Active = false;
			$rootScope.isFooter2Active = false;
			$rootScope.isFooter3Active = true;
			
			$rootScope.showPreviousIcon = true;
			$rootScope.showNextIcon = false;
		}
	});

	$scope.composeModalShow = 180;


	$scope.ttsData = {language: ''};

	$scope.getTTSUrl = function(){
		return $sce.trustAsResourceUrl('/assets/callburn/tts/' + $scope.ttsData.language + '.wav')
	}

	$scope.playTTSDemo = function(){
		document.getElementById('ttsDemoAudio').play();
	}
	
	if(editingCampaign || (reusingCampaign && (reusingSource == 'both' || reusingSource == 'message' ) )){
		var tempCampaign = editingCampaign ? editingCampaign : reusingCampaign;
		$scope.selectedAudio = tempCampaign.voice_file._id;
		var isEditingTemplate = false;
		for(index in $scope.audioTemplates){
			if($scope.audioTemplates[index]._id == tempCampaign.voice_file._id){
				isEditingTemplate = true;
				break;
			}
		}
		if(!isEditingTemplate){
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + tempCampaign.voice_file._id),
				file: tempCampaign.voice_file
			}
			$scope.audioFiles.push(audioData);
		}
	}


	$scope.isFilePlaying = [];
	$scope.isRecordedFilePlaying = false;
	$scope.isRecording = false;



	$scope.contactsModalShow = false;

	// $scope.closeContactsModal = function(){
	// 	$scope.contactsModalShow = false;
	// }

	/*
	|---------------------------------------------------------
	| COMPOSE STEP 1 - Audio file
	|---------------------------------------------------------
	| Step 1 is for selecting audio message for campaign
	| User can use TTS service for creating audio from text OR
	| upload an audio file using both upload or dropzone OR
	| record audio directly from his browser OR
	| choose from already existign template files
	*/

	$scope.audioTemplatesPage = 0;
	$scope.audioTemplatesPagesCount = Math.ceil( $scope.audioTemplates.length/5 );

	$scope.audioTemplatesListingSkip = 0;

	$scope.changeAudioTemplatesPage = function(pageNumber){
		if(pageNumber < 0 || pageNumber > $scope.audioTemplatesPagesCount - 1){
			return;
		}
		$scope.audioTemplatesPage = pageNumber;
		$scope.audioTemplatesListingSkip = $scope.audioTemplatesPage * 5;
	}

	$scope.showTtsNotificationModal = false;
	$scope.ttsAction = 'voice_file';
	$scope.showTtsNotification = function(action){
		$scope.ttsAction = action;
		$scope.showTtsNotificationModal = true;
	}

	$scope.acceptTtsNote = function(){
		switch($scope.ttsAction){
			case 'voice_file':
				createAudioFromTextForVoice();
				break;
			case 'callback':
				createAudioFromTextForCallback();
				break;
			case 'donotcall':
				createAudioFromTextForDoNotCall();
				break;
		}
		$scope.showTtsNotificationModal = false;
	}


	$scope.showFilesInput = [];

	$scope.removeTemplateAudioFile = function(id){
		$rootScope.startLoader();
		Restangular.all('audio-files/remove-audio-template').post({id: id}).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				Restangular.one('audio-files/audio-templates').get().then(function(audioFiles){
					$scope.audioTemplates = audioFiles.resource.files;
				})
			}
		})
	}

	$scope.changeFileName = function(id, name, isTemplate){
		if(isTemplate){
			var source = $scope.audioTemplates;
		} else{
			var source = $scope.audioFiles;
		}
		$rootScope.startLoader();
		Restangular.one('audio-files/update-file-name', id).put({name: name}).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				for(index in source){
					var fileSource = source[index].file ? source[index].file : source[index];
					if(fileSource._id == id){
						fileSource.orig_filename = name;
						$scope.showFilesInput[id] = false;
						break;
					}
				}
			}
		})
	}

	$scope.changeNumbersSource = function(source){
		$scope.numbersSource = source;
	}

	$scope.playFile = function(id){
		var audio = document.getElementById('audioFile' + id);
		audio.play();
		$scope.isFilePlaying[id] = true;
		audio.addEventListener('ended', function() {
	        	$scope.isFilePlaying[id] = false;
	        	$scope.$apply();
	    }, false);
	}

	$scope.pauseFile = function(id){
		document.getElementById('audioFile' + id).pause();
		$scope.isFilePlaying[id] = false;
	}

	$scope.openCampaignVoiceFileSelect = function(){
		$timeout(function() {
		    angular.element('#campaignVoiceFileInput1').trigger('click');
		}, 100);
	}

	$scope.getAudioSource = function(id){
		return $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + id);
	}

	var createAudioFromTextForVoice = function(){
		$rootScope.startLoader();
		Restangular.all('campaigns/create-audio-from-text').post($scope.ttsData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.ttsData = {language: ''};
				/*var audioData = {
					source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
					file: data.resource.file
				}
				$scope.audioFiles.push(audioData);*/
				$scope.audioTemplates.push(data.resource.file)
				$scope.selectedAudio = data.resource.file._id;
				$scope.goToStep2();
			} else{
				$scope.voiceTtsError = data.resource.error.text;
			}
		})
	}

	

	var campaignVoiceMessageUpload = $scope.campaignVoiceMessageUpload = new FileUploader({
	    url: 'campaigns/upload-audio-file',
	    alias : 'file',
	    autoUpload : true
	});

	campaignVoiceMessageUpload.onAfterAddingFile = function(item){
		$scope.uploadingAudioName = item.file.name;
	}

	campaignVoiceMessageUpload.onSuccessItem = function(item, response, status, headers){
		$rootScope.stopLoader();
		if(response.resource.error.no == 0){
			$scope.uploadingAudioName = false;
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + response.resource.file._id),
				file: response.resource.file
			}
			$scope.audioFiles.push(audioData);
			$scope.selectedAudio = response.resource.file._id;
			$scope.goToStep2();
		} else{
          	notify({message: response.resource.error.text, classes: ['notification-alert-danger']})
		}
	};

	campaignVoiceMessageUpload.onErrorItem = function(item, response, status, headers){
		$scope.errors = response;
		$rootScope.stopLoader();
	}

	campaignVoiceMessageUpload.onBeforeUploadItem  = function(){
		$rootScope.startLoader();
	}

	$scope.startVoiceFileUpload = function(){
		campaignVoiceMessageUpload.uploadAll();
	}

	$scope.startBrowserAudioRecording = function(){
		$scope.isRecording = true;
		Fr.voice.record(true, function(){});
	}

	$scope.campaignVoiceMessageRecordFinish = function(){
		Fr.voice.export(function(url){
			Fr.voice.stop();
			$scope.isRecording = false;
			var base64 = url.split(',');
      		$scope.audioRecordedBase64  = base64[base64.length - 1];
      		$scope.recordedAudio = new Audio("data:audio/wav;base64," + $scope.audioRecordedBase64);
      		$scope.$apply();
		}, 'base64');
	}

	$scope.playPauseRecordedAudio = function(){
		if($scope.isRecordedFilePlaying){
			$scope.recordedAudio.pause();
		}else{
			$scope.recordedAudio.play();
			$scope.recordedAudio.addEventListener('ended', function() {
	        	$scope.isRecordedFilePlaying = false;
	        	$scope.$apply();
	        }, false);
		}
		$scope.isRecordedFilePlaying = !$scope.isRecordedFilePlaying;
	}

	$scope.saveRecordedFile = function(){
		Restangular.all('campaigns/create-file-from-base64').post({base64_data: $scope.audioRecordedBase64}).then(function(data){
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
				file: data.resource.file
			}
			$scope.audioFiles.push(audioData);
		})
	}

	$scope.selectAudio = function(id){
		$scope.selectedAudio = id;
	}

	$scope.saveAsTemplate = function(){
		if(!$scope.selectedAudio){
			return;
		}
		$rootScope.startLoader();
		Restangular.all('audio-files/make-audio-template').post({id: $scope.selectedAudio}).then(function(data){
			var thisId = $scope.selectedAudio;
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				Restangular.one('audio-files/audio-templates').get().then(function(audioFiles){
					$scope.audioTemplates = audioFiles.resource.files;
					$scope.removeAudioFile(thisId);
				})
			}
		})
	}

	$scope.removeAudioFile = function(id){
		var selectedAudioId = id ? id : $scope.selectedAudio;
		if(!selectedAudioId){
			return;
		}
		for(index in $scope.audioFiles){
			if($scope.audioFiles[index].file._id == selectedAudioId){
				$scope.audioFiles.splice(index, 1);
				break;
			}
		}
	}

	$scope.goToStep2 = function(){
		if($scope.selectedAudio){
			$scope.campaignData.campaign_voice_file_id = $scope.selectedAudio;
			for(index in $scope.audioFiles){
				if($scope.audioFiles[index].file._id == $scope.selectedAudio){
					$scope.finalStepData.voiceFile = $scope.audioFiles[index].file;
					break;
				}
			}
			for(index in $scope.audioTemplates){
				if($scope.audioTemplates[index]._id == $scope.selectedAudio){
					$scope.finalStepData.voiceFile = $scope.audioTemplates[index];
					break;
				}
			}
			$scope.composeStep = 2;
		} else{
          	notify({message: 'File not selected', classes: ['notification-alert-danger']})
		}
	}


	/*
	|---------------------------------------------------------
	| COMPOSE STEP 2 - Phonenumbers
	|---------------------------------------------------------
	| Choose phonenumbers for campaign .
	| User can add manually , upload a file
	| choose from contacts or select groups .
	|
	*/

	$scope.numbersSource = 'contacts';

	/*
	|---------------------------------------------------------
	| COMPOSE STEP 2 - EDIT PHONENUMBERS
	|---------------------------------------------------------
	| Remove/readd phonenumbers on edit screen
	*/
	$scope.editPhonenumbersRemove = function(id){
		$scope.campaignData.removed_phonenumbers.push(id);
	}

	$scope.editPhonenumbersReadd = function(id){
		var index = $scope.campaignData.removed_phonenumbers.indexOf(id);
		$scope.campaignData.removed_phonenumbers.splice(index, 1);
	}


	/*
	|---------------------------------------------------------
	| MANUALLY
	|---------------------------------------------------------
	| Here will go all logic for compose step 2 , when user
	| wants to manually add, or upload file as a source of numbers.
	| 
	*/

	$scope.addedNumbers = '';
	$scope.listingSkip = 0;
	$scope.manuallyAddedPage = 0;
	$scope.manuallyAddedPagesCount = 1;
	$scope.manuallyAddedNumbers = [];

	if($stateParams.phonenumbers){
		$scope.numbersSource = 'manually';
		var paramPhonenumbers = JSON.parse($stateParams.phonenumbers);
		for(index in paramPhonenumbers){
			$scope.manuallyAddedNumbers.push(paramPhonenumbers[index]);
		}
		$scope.manuallyAddedPagesCount = Math.ceil(paramPhonenumbers.length/7);
	}

	$scope.changeManuallyAddedPage = function(pageNumber){
		if(pageNumber < 0 || pageNumber > $scope.manuallyAddedPagesCount - 1){
			return;
		}
		$scope.manuallyAddedPage = pageNumber;
		$scope.listingSkip = $scope.manuallyAddedPage * 7;
	}

	$scope.startUpload = function(){
		numbersFileUpload.uploadAll();
	}

	var numbersFileUpload = $scope.numbersFileUpload = new FileUploader({
	    url: 'phonenumbers/upload-phonenumbers',
	    alias : 'file',
	    autoUpload : true,
	    formData: [{is_campaign_create: true}]
	});

	numbersFileUpload.onAfterAddingFile = function(item){
		$scope.uploadingImageName = item.file.name;
	}

	numbersFileUpload.onErrorItem = function(item, response, status, headers){
		$rootScope.stopLoader();
	}

	numbersFileUpload.onBeforeUploadItem  = function(){
		$rootScope.startLoader();
	}

	numbersFileUpload.onSuccessItem = function(item, data, status, headers){
		$rootScope.stopLoader();
		if(data.resource.error.no == 0){
			$scope.uploadingImageName = false;
			$scope.numbersResponseData = data.resource;
			var responsePhonenumbers = data.resource.phonenumbers;
			for(index in responsePhonenumbers){
				var isDuplicate = false;
				for(ind in $scope.manuallyAddedNumbers){
					if(responsePhonenumbers[index].number == $scope.manuallyAddedNumbers[ind].number){
						isDuplicate = true;
						break;
					}
				}
				if(!isDuplicate){
					$scope.manuallyAddedNumbers.push(responsePhonenumbers[index]);
				}
			}
			$scope.manuallyAddedPagesCount = Math.ceil($scope.manuallyAddedNumbers.length/7);
		} else{
			$scope.uploadingImageName = false;
	      	notify({message: data.resource.error.text, classes: ['notification-alert-danger']})
	    }
	};

	$scope.openFileSelect= function(){
		$timeout(function() {
		    angular.element('#hiddenNumbersFileInput').trigger('click');
		}, 100);
	}

	$scope.addNumbers = function(){
		var postData = {phonenumbers: $scope.addedNumbers};
		postData.is_campaign_create = true;
		$rootScope.startLoader();
		Restangular.all('phonenumbers/add-phonenumbers').post(postData).then(function(data){
			$scope.addedNumbers = '';
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.numbersResponseData = data.resource;
				var responsePhonenumbers = data.resource.phonenumbers;
				for(index in responsePhonenumbers){
					var isDuplicate = false;
					for(ind in $scope.manuallyAddedNumbers){
						if(responsePhonenumbers[index].number == $scope.manuallyAddedNumbers[ind].number){
							isDuplicate = true;
							break;
						}
					}
					if(!isDuplicate){
						$scope.manuallyAddedNumbers.push(responsePhonenumbers[index]);
					}
				}
				$scope.manuallyAddedPagesCount = Math.ceil($scope.manuallyAddedNumbers.length/7);
			} else{

			}
		})
	}

	$scope.removeManuallyAddedContact = function(index){
		$scope.manuallyAddedNumbers.splice(index, 1);
		$scope.manuallyAddedPagesCount = Math.ceil( $scope.manuallyAddedNumbers.length /7);
	}

	/*
	|---------------------------------------------------------
	| CONTACTS
	|---------------------------------------------------------
	| Here will go all logic for compose step 2 , when user
	| wants to use contacts as the source of the phonenumbers
	| 
	*/


	Restangular.one('address-book/index-contacts').get().then(function(data){
		$scope.contacts = data.resource.contacts;
		$scope.contactsPage = data.resource.page;
		$scope.contactsPagesCount = Math.ceil(data.resource.count/10);
	});

	var routeContactIds = $stateParams.contact_ids;
	if(routeContactIds){
		$scope.numbersSource = 'contacts';
		$scope.checkedContacts = JSON.parse(routeContactIds);
	} else{
		$scope.checkedContacts = {};
	}
	$scope.isAllContactsChecked = false;

	$scope.checkedUncheckContact = function(contactId, event)
	{
		$scope.checkedContacts[contactId] = $scope.checkedContacts[contactId] ? !$scope.checkedContacts[contactId] : true; 
	}

	$scope.checkUncheckAllContacts = function(){
		$scope.isAllContactsChecked = !$scope.isAllContactsChecked;
		for(index in $scope.contacts){
			$scope.checkedContacts[$scope.contacts[index]._id] = $scope.isAllContactsChecked;
		}
	}

	$scope.changeContactsPage = function(page){
		if(page < 0 || page > $scope.pagesCount - 1){
			return;
		}
		$rootScope.startLoader();
		Restangular.one('address-book/index-contacts').get({page: page}).then(function(data){
			$rootScope.stopLoader();
			$scope.contacts = data.resource.contacts;
			$scope.contactsPage = data.resource.page;
			$scope.contactsPagesCount = Math.ceil(data.resource.count/10);
		});
	}

	/*
	|---------------------------------------------------------
	| GROUPS
	|---------------------------------------------------------
	| 
	| Here will go all logic for compose step 2 , when user
	| wants to use groups(s) as the source of the phonenumbers
	| 
	*/

	Restangular.one('address-book/index-groups').get().then(function(data){
		$scope.groups = data.resource.groups;
		$scope.groupsPage = data.resource.page;
		$scope.groupsPagesCount = Math.ceil(data.resource.count/10);
	});

	var routeGroupIds = $stateParams.group_ids;
	
	if(routeGroupIds){
		$scope.numbersSource = 'groups';
		$scope.checkedGroups = JSON.parse(routeGroupIds);
	} else{
		$scope.checkedGroups = {};
	}
	$scope.isAllGroupsChecked = false;

	$scope.checkedUncheckGroup = function(groupId)
	{
		$scope.checkedGroups[groupId] = $scope.checkedGroups[groupId] ? !$scope.checkedGroups[groupId] : true; 
	}

	$scope.checkUncheckAllGroups = function(){
		$scope.isAllGroupsChecked = !$scope.isAllGroupsChecked;
		for(index in $scope.groups){
			$scope.checkedGroups[$scope.groups[index]._id] = $scope.isAllGroupsChecked;
		}
	}

	$scope.changeGroupsPage = function(page){
		if(page < 0 || page > $scope.groupsPagesCount - 1){
			return;
		}
		Restangular.one('address-book/index-groups').get({page: page}).then(function(data){
			$scope.groups = data.resource.groups;
			$scope.groupsPage = data.resource.page;
			$scope.groupsPagesCount = Math.ceil(data.resource.count/10);
		});
	}

	
	$scope.goToStep3 = function(){
		var postData = [];
		switch($scope.numbersSource){
			case 'manually':
				for(index in $scope.manuallyAddedNumbers){
					if($scope.manuallyAddedNumbers[index].tariff){
						postData.push($scope.manuallyAddedNumbers[index].number)
					}
				}
				break;
			case 'contacts':
				postData = $scope.checkedContacts;
				break;
			case 'groups':
				postData = $scope.checkedGroups;
				$scope.campaignData.selected_groups = $scope.checkedGroups;
				break;
			default:
				break;
		}
		$rootScope.startLoader();
		Restangular.all('phonenumbers/add-numbers-and-calculate-cost-' + $scope.numbersSource ).post({ file_id: $scope.campaignData.campaign_voice_file_id,  data: postData}).then(function(data){
			if(data.resource.error.no == 0){
				$scope.finalStepData.maxCost = data.resource.max_cost;
				$scope.finalStepData.sendingTime = data.resource.sending_time;
				$scope.finalStepData.numbersCount = data.resource.phonenumbers.length;
				$scope.campaignData.phonenumbers = data.resource.phonenumbers;
				$scope.campaignData.max_cost = data.resource.max_cost;
				if(editingCampaign || (reusingCampaign && (reusingSource == 'both' || reusingSource == 'receipents' ) )){
					var editingPhonenumbersData = [];
					for(index in $scope.currentPhonenumbers){
						if($scope.campaignData.removed_phonenumbers.indexOf($scope.currentPhonenumbers[index]._id) == -1){
							editingPhonenumbersData.push($scope.currentPhonenumbers[index].phone_no);
						}
					}
					Restangular.all('phonenumbers/add-numbers-and-calculate-cost-manually').post({ file_id: $scope.campaignData.campaign_voice_file_id,  data: editingPhonenumbersData}).then(function(data1){
						$rootScope.stopLoader();
						if(data1.resource.error.no == 0){
							if(reusingCampaign && (reusingSource == 'both' || reusingSource == 'receipents')){
								for(ind in data1.resource.phonenumbers){
									$scope.campaignData.phonenumbers.push(data1.resource.phonenumbers[ind]);
								}
							}
							$scope.finalStepData.maxCost += data1.resource.max_cost;
							$scope.campaignData.max_cost += data1.resource.max_cost;
							$scope.finalStepData.numbersCount += data1.resource.phonenumbers.length;
							if(data1.resource.phonenumbers.length == 0 && data.resource.phonenumbers.length){
          						notify({message: 'There is no valid phonenumber selected. This should be changed to beautiful error', classes: ['notification-alert-danger']})
							} else{
								$scope.composeStep = 3;
							}
						}
					})
				} else{
					$rootScope.stopLoader();
					if(data.resource.phonenumbers.length == 0){
          				notify({message: 'There is no valid phonenumber selected. This should be changed to beautiful error', classes: ['notification-alert-danger']})
					} else{
						$scope.composeStep = 3;
					}
				}
				
			}
		})
	}


	/*
	|---------------------------------------------------------
	| COMPOSE STEP 3 - FINAL STEP
	|---------------------------------------------------------
	| Final step for creating campaign.
	| Here use can choose callback, donotcall, replay and transfer digits,
	| change caller id, see max cost etc.
	| 
	*/

	$scope.replayDigit    = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
	$scope.transferDigit  = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
	$scope.callbackDigit  = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
	$scope.doNotCallDigit = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};

	if(editingCampaign){
		if(editingCampaign.replay_digit){
			$scope.replayDigit.onOff = 'on';
			$scope.replayDigit.checkboxChecked = true;
		}
		if(editingCampaign.transfer_digit){
			$scope.transferDigit.onOff = 'on';
			$scope.transferDigit.checkboxChecked = true;
		}
		if(editingCampaign.callback_digit_file_id){
			$scope.callbackDigit.onOff = 'on';
			$scope.callbackDigit.checkboxChecked = true;
		}
		if(editingCampaign.do_not_call_digit_file_id){
			$scope.doNotCallDigit.onOff = 'on';
			$scope.doNotCallDigit.checkboxChecked = true;
		}
	}


	$scope.currentAction = false;
	var checkIfActiveInteraction = function(callback, action){
		$scope.interactionsForModal = [];
		var isAnyActive = false;
		if($scope.replayDigit.onOff == 'on'){
			isAnyActive = true;
			var replayDigitObject = {action: 'Replay Voice Message', keypress: $scope.campaignData.replay_digit};
			$scope.interactionsForModal.push(replayDigitObject);
		}
		if($scope.transferDigit.onOff == 'on'){
			isAnyActive = true;
			var transferDigitObject = {action: 'Transfer Voice Message', keypress: $scope.campaignData.transfer_digit};
			$scope.interactionsForModal.push(transferDigitObject);
		}
		if($scope.callbackDigit.onOff == 'on'){
			isAnyActive = true;
			var callbackDigitObject = {action: 'Callback', keypress: $scope.campaignData.callback_digit};
			$scope.interactionsForModal.push(callbackDigitObject);
		}
		if($scope.doNotCallDigit.onOff == 'on'){
			isAnyActive = true;
			var doNotCallDigitObject = {action: 'Blacklist', keypress: $scope.campaignData.do_not_call_digit};
			$scope.interactionsForModal.push(doNotCallDigitObject);
		}
		if(!isAnyActive){
			return callback();
		} else{
			$scope.showSaveCampaignModalWithInteractions = true;
			$scope.currentAction = action;
		}
	}
	
	$scope.proceedToSaveWithInteraction = function(action){
		$scope.showSaveCampaignModalWithInteractions = false;
		switch(action){
			case 'save':
				return proceedSaveCampaign();
			case 'schedule':
				return scheduleCampaignProceed();
			case 'draft':
				return saveAsDraftProceed();
			case 'preview':
				return sendPreviewToYourPhoneProceed();
		}
	}

	/*
	|---------------------------------------------------------
	| ENABLE CALLBACK DIGIT IN COMPOSE STEP 3
	|---------------------------------------------------------
	| Here will go all logic for selecting callback digit
	| and audio file for callback action . For audio user can
	| use the same ways as for audio voice file .
	|
	*/

	$scope.callbackAudioFiles = [];
	$scope.isCallbackFilePlaying = [];
	$scope.callbackAudioFileId = null;

	$scope.callbackDigitActivated = function(){
		if(!$scope.callbackDigit.checkboxChecked){
			return;
		}
		$scope.callbackDigit.modalStep = -1;
		$scope.callbackDigit.onOff = 'on';
	}

	$scope.donotcallDigitActivated = function(){
		if(!$scope.doNotCallDigit.checkboxChecked){
			return;
		}
		$scope.doNotCallDigit.modalStep = -1;
		$scope.doNotCallDigit.onOff = 'on';
	}

	$scope.replyDigitActivated = function(){
		if(!$scope.replayDigit.checkboxChecked){
			return;
		}
		$scope.replayDigit.modalStep = -1;
		$scope.replayDigit.onOff = 'on';
	}

	$scope.transferDigitActivated = function(){
		if(!$scope.transferDigit.checkboxChecked){
			return;
		}
		$scope.transferDigit.modalStep = -1;
		$scope.transferDigit.onOff = 'on';
		$scope.campaignData.transfer_options = $scope.liveTransferNumbers.join();
	}


	/*
	|---------------------------------------------------------
	| CALLBACK DIGIT - METHOD 1 - TTS
	|---------------------------------------------------------
	| Use tts , for creating audio file from text 
	| choosing language
	|
	*/
	$scope.callbackTtsData = {language: null};

	var createAudioFromTextForCallback = function(){
		$rootScope.startLoader();
		Restangular.all('campaigns/create-audio-from-text').post($scope.callbackTtsData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.callbackTtsData = {language: null};
				/*var audioData = {
					source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
					file: data.resource.file
				}
				$scope.callbackAudioFiles.push(audioData);*/
				$scope.audioTemplates.push(data.resource.file);
			} else{
				$scope.voiceTtsError = data.resource.error.text;
			}
		})
	}

	/*
	|---------------------------------------------------------
	| CALLBACK DIGIT - METHOD 2 - UPLOAD
	|---------------------------------------------------------
	| Upload a file , can use dropdown too.
	|
	*/
	$scope.uploadingCallbackAudioName = '';
	$scope.openCallbackFileSelect = function(){
		$timeout(function() {
		    angular.element('#campaignCallbackFileInput').trigger('click');
		}, 100);
	}

	var callbackDigitFileUpload = $scope.callbackDigitFileUpload = new FileUploader({
	    url: 'campaigns/upload-audio-file',
	    alias : 'file',
	    autoUpload : false
	});

	callbackDigitFileUpload.onAfterAddingFile = function(item){
		$scope.uploadingCallbackAudioName = item.file.name;
	}

	callbackDigitFileUpload.onSuccessItem = function(item, response, status, headers){
		if(response.resource.error.no == 0){
			$scope.uploadingCallbackAudioName = false;
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + response.resource.file._id),
				file: response.resource.file
			}
			$scope.callbackAudioFiles.push(audioData);
		} else{
          	notify({message: response.resource.error.text, classes: ['notification-alert-danger']})
        }
	};

	callbackDigitFileUpload.onErrorItem = function(item, response, status, headers){
		$scope.errors = response;
	}

	$scope.startCallbackUpload = function(){
		callbackDigitFileUpload.uploadAll();
	}

	/*
	|---------------------------------------------------------
	| CALLBACK DIGIT - METHOD 3 - RECORD
	|---------------------------------------------------------
	| Record audio directly from browser .
	| FR audio record module used
	*/
	$scope.isCallbackRecordedFilePlaying = false;
	$scope.isCallbackRecording = false;
	$scope.recordedCallbackAudio = new Audio();
	$scope.startBrowserAudioRecordingCallback = function(){
		$scope.isCallbackRecording = true;
		Fr.voice.record(true, function(){});
	}

	$scope.callbackMessageRecordFinish = function(){
		Fr.voice.export(function(url){
			Fr.voice.stop();
			$scope.isCallbackRecording = false;
			var base64 = url.split(',');
      		$scope.audioRecordedBase64Callback  = base64[base64.length - 1];
      		$scope.recordedCallbackAudio = new Audio("data:audio/wav;base64," + $scope.audioRecordedBase64Callback);
      		$scope.$apply();
		}, 'base64');
	}

	$scope.playPauseCallbackRecordedAudio = function(){
		if($scope.isCallbackRecordedFilePlaying){
			$scope.recordedCallbackAudio.pause();
		}else{
			$scope.recordedCallbackAudio.play();
			$scope.recordedCallbackAudio.addEventListener('ended', function() {
	        	$scope.isCallbackRecordedFilePlaying = false;
	        	$scope.$apply();
	        }, false);
		}
		$scope.isCallbackRecordedFilePlaying = !$scope.isCallbackRecordedFilePlaying;
	}

	$scope.saveCallbackRecordedFile = function(){
		Restangular.all('campaigns/create-file-from-base64').post({base64_data: $scope.audioRecordedBase64Callback}).then(function(data){
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
				file: data.resource.file
			}
			$scope.callbackAudioFiles.push(audioData);
		})
	}
	
	/*
	|---------------------------------------------------------
	| CALLBACK DIGIT - METHOD 4 - TEMPLATE
	|---------------------------------------------------------
	| Use already created audio template file.
	| 
	*/
	$scope.selectedCallbackAudioTemplateFile = {};
	$scope.showCallbackTemplateSelectDropDown = false;
	$scope.isCallbackTemplateFilePlaying = false;

	$scope.callbackAudioTemplateSelected = function(file){
		file = JSON.parse(file);
		$scope.selectedCallbackAudioTemplateFile = file;
		$scope.showCallbackTemplateSelectDropDown = false;
	}

	$scope.isCallbackAlreadyAdded = function(){
		for(index in $scope.callbackAudioFiles){
			if($scope.callbackAudioFiles[index].file._id == $scope.selectedCallbackAudioTemplateFile._id){
				return true;
			}
		}
		return false;
	}

	$scope.saveTemplateAsCallback = function(){
		$scope.callbackAudioFiles.push({file: $scope.selectedCallbackAudioTemplateFile});
	}

	$scope.playPauseCallbackTemplateAudio = function(action){
		if(!$scope.selectedCallbackAudioTemplateFile._id){
			return;
		}
		var audio = document.getElementById('callbackAudioFileTemplate');
		if(action == 'play'){
			audio.play();
			$scope.isCallbackTemplateFilePlaying = true;
			audio.addEventListener('ended', function() {
		        	$scope.isCallbackTemplateFilePlaying = false;
		        	$scope.$apply();
		    }, false);
		} else{
			audio.pause();
			$scope.isCallbackTemplateFilePlaying = false;
		}
	}

	/*
	|---------------------------------------------------------
	| Caller id 
	|---------------------------------------------------------
	| Verify caller id modal.
	*/
	$scope.showSelectCallerIdModel = false;
	if(editingCampaign){
		$scope.selectedCallerId = editingCampaign.caller_id;
	} else{
		$scope.selectedCallerId = $rootScope.currentUser.numbers[0].phone_number;
	}

	$scope.callerIdSelected = function(phonenumber){
		$scope.selectedCallerId = phonenumber;
	}

	$scope.changeCallerId = function(){
		/*for(index in $rootScope.currentUser.numbers){
			if($rootScope.currentUser.numbers[index]._id == $scope.selectedCallerId){
				$scope.campaignData.caller_id = $rootScope.currentUser.numbers[index].phone_number;
				break;
			}
		}*/
		$scope.campaignData.caller_id = $scope.selectedCallerId;
		$scope.showSelectCallerIdModel = false;
	}

	/*
	|---------------------------------------------------------
	| CALLBACK DIGIT - finalizing and closing modal
	|---------------------------------------------------------
	| Select audio file for callback digit , from the list
	| created by the 4 methods above 
	*/
	if(editingCampaign && editingCampaign.callback_file){
		$scope.callbackAudioFiles.push({file: editingCampaign.callback_file});
		$scope.newCallbackSelectedAudioFile = editingCampaign.callback_file;
	} else{
		$scope.newCallbackSelectedAudioFile = {};
	}
	$scope.showNewCreatedCallbacksDropDown = false;
	$scope.isCallbackNewCreatedFilePlaying = false;
	$scope.selectCallbackNewCreatedAudioFile = function(file){
		file = JSON.parse(file);
		$scope.newCallbackSelectedAudioFile = file;
		$scope.showNewCreatedCallbacksDropDown = false;
		$scope.campaignData.callback_voice_file_id = $scope.newCallbackSelectedAudioFile._id;
	}

	$scope.playPauseCallbackNewCreatedAudio = function(action){
		if(!$scope.newCallbackSelectedAudioFile._id){
			return;
		}
		var templateId = $scope.newCallbackSelectedAudioFile._id;
		var audio = document.getElementById('callbackCreatedAudioFile');
		if(action == 'play'){
			audio.play();
			$scope.isCallbackNewCreatedFilePlaying = true;
			audio.addEventListener('ended', function() {
		        	$scope.isCallbackNewCreatedFilePlaying = false;
		        	$scope.$apply();
		    }, false);
		} else{
			audio.pause();
			$scope.isCallbackNewCreatedFilePlaying = false;
		}
	}


	/*
	|---------------------------------------------------------
	| ENABLE DONOTCALL DIGIT IN COMPOSE STEP 3
	|---------------------------------------------------------
	| Here will go all logic for selecting doNotCall digit
	| and audio file for doNotCall action . For audio user can
	| use the same ways as for audio voice file .
	|
	*/

	$scope.doNotCallAudioFiles = [];
	$scope.isDoNotCallFilePlaying = [];
	$scope.doNotCallAudioFileId = null;


	/*
	|---------------------------------------------------------
	| DONOTCALL DIGIT - METHOD 1 - TTS
	|---------------------------------------------------------
	| Use tts , for creating audio file from text 
	| choosing language
	|
	*/
	$scope.doNotCallTtsData = {language: null};

	var createAudioFromTextForDoNotCall = function(){
		$rootScope.startLoader();
		Restangular.all('campaigns/create-audio-from-text').post($scope.doNotCallTtsData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.doNotCallTtsData = {language: null};
				/*var audioData = {
					source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
					file: data.resource.file
				}
				$scope.doNotCallAudioFiles.push(audioData);*/
				$scope.audioTemplates.push(data.resource.file);
			} else{
				$scope.voiceTtsError = data.resource.error.text;
			}
		})
	}

	/*
	|---------------------------------------------------------
	| DONOTCALL DIGIT - METHOD 2 - UPLOAD
	|---------------------------------------------------------
	| Upload a file , can use dropdown too.
	|
	*/
	$scope.uploadingDoNotCallAudioName = '';
	$scope.openDoNotCallFileSelect = function(){
		$timeout(function() {
		    angular.element('#campaignDoNotCallFileInput').trigger('click');
		}, 100);
	}

	var doNotCallDigitFileUpload = $scope.doNotCallDigitFileUpload = new FileUploader({
	    url: 'campaigns/upload-audio-file',
	    alias : 'file',
	    autoUpload : false
	});

	doNotCallDigitFileUpload.onAfterAddingFile = function(item){
		$scope.uploadingDoNotCallAudioName = item.file.name;
	}

	doNotCallDigitFileUpload.onSuccessItem = function(item, response, status, headers){
		if(response.resource.error.no == 0){
			$scope.uploadingDoNotCallAudioName = false;
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + response.resource.file._id),
				file: response.resource.file
			}
			$scope.doNotCallAudioFiles.push(audioData);
		} else{
          	notify({message: response.resource.error.text, classes: ['notification-alert-danger']})
        }
	};

	doNotCallDigitFileUpload.onErrorItem = function(item, response, status, headers){
		$scope.errors = response;
	}

	$scope.startDoNotCallUpload = function(){
		doNotCallDigitFileUpload.uploadAll();
	}

	/*
	|---------------------------------------------------------
	| DONOTCALL DIGIT - METHOD 3 - RECORD
	|---------------------------------------------------------
	| Record audio directly from browser .
	| FR audio record module used
	*/
	$scope.isDoNotCallRecordedFilePlaying = false;
	$scope.isDoNotCallRecording = false;
	$scope.recordedDoNotCallAudio = new Audio();
	$scope.startBrowserAudioRecordingDoNotCall = function(){
		$scope.isDoNotCallRecording = true;
		Fr.voice.record(true, function(){});
	}

	$scope.doNotCallMessageRecordFinish = function(){
		Fr.voice.export(function(url){
			Fr.voice.stop();
			$scope.isDoNotCallRecording = false;
			var base64 = url.split(',');
      		$scope.audioRecordedBase64DoNotCall  = base64[base64.length - 1];
      		$scope.recordedDoNotCallAudio = new Audio("data:audio/wav;base64," + $scope.audioRecordedBase64DoNotCall);
      		$scope.$apply();
		}, 'base64');
	}

	$scope.playPauseDoNotCallRecordedAudio = function(){
		if($scope.isDoNotCallRecordedFilePlaying){
			$scope.recordedDoNotCallAudio.pause();
		}else{
			$scope.recordedDoNotCallAudio.play();
			$scope.recordedDoNotCallAudio.addEventListener('ended', function() {
	        	$scope.isDoNotCallRecordedFilePlaying = false;
	        	$scope.$apply();
	        }, false);
		}
		$scope.isDoNotCallRecordedFilePlaying = !$scope.isDoNotCallRecordedFilePlaying;
	}

	$scope.saveDoNotCallRecordedFile = function(){
		Restangular.all('campaigns/create-file-from-base64').post({base64_data: $scope.audioRecordedBase64DoNotCall}).then(function(data){
			var audioData = {
				source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
				file: data.resource.file
			}
			$scope.doNotCallAudioFiles.push(audioData);
		})
	}
	
	/*
	|---------------------------------------------------------
	| DONOTCALL DIGIT - METHOD 4 - TEMPLATE
	|---------------------------------------------------------
	| Use already created audio template file.
	| 
	*/
	$scope.selectedDoNotCallAudioTemplateFile = {};
	$scope.showDoNotCallTemplateSelectDropDown = false;
	$scope.isDoNotCallTemplateFilePlaying = false;

	$scope.doNotCallAudioTemplateSelected = function(file){
		file = JSON.parse(file);
		$scope.selectedDoNotCallAudioTemplateFile = file;
		$scope.showDoNotCallTemplateSelectDropDown = false;
	}

	$scope.isDoNotCallAlreadyAdded = function(){
		for(index in $scope.doNotCallAudioFiles){
			if($scope.doNotCallAudioFiles[index].file._id == $scope.selectedDoNotCallAudioTemplateFile._id){
				return true;
			}
		}
		return false;
	}

	$scope.saveTemplateAsDoNotCall = function(){
		$scope.doNotCallAudioFiles.push({file: $scope.selectedDoNotCallAudioTemplateFile})
	}

	$scope.playPauseDoNotCallTemplateAudio = function(action){
		if(!$scope.selectedDoNotCallAudioTemplateFile._id){
			return;
		}
		var audio = document.getElementById('doNotCallAudioFile');
		if(action == 'play'){
			audio.play();
			$scope.isDoNotCallTemplateFilePlaying = true;
			audio.addEventListener('ended', function() {
		        	$scope.isDoNotCallTemplateFilePlaying = false;
		        	$scope.$apply();
		    }, false);
		} else{
			audio.pause();
			$scope.isDoNotCallTemplateFilePlaying = false;
		}
	}

	/*
	|---------------------------------------------------------
	| DONOTCALL DIGIT - finalizing and closing modal
	|---------------------------------------------------------
	| Select audio file for doNotCall digit , from the list
	| created by the 4 methods above
	*/


	if(editingCampaign && editingCampaign.do_not_call_file){
		$scope.doNotCallAudioFiles.push({file: editingCampaign.do_not_call_file});
		$scope.newDoNotCallSelectedAudioFile = editingCampaign.do_not_call_file;
	} else{
		$scope.newDoNotCallSelectedAudioFile = {};
	}

	$scope.showNewCreatedDoNotCallsDropDown = false;
	$scope.isDoNotCallNewCreatedFilePlaying = false;
	$scope.selectDoNotCallNewCreatedAudioFile = function(file){
		file = JSON.parse(file);
		$scope.newDoNotCallSelectedAudioFile = file;
		$scope.showNewCreatedDoNotCallsDropDown = false;
		$scope.campaignData.do_not_call_voice_file_id = $scope.newDoNotCallSelectedAudioFile._id;
	}

	$scope.saveNewCreatedAsDoNotCall = function(){
		$scope.campaignData.do_not_call_voice_file_id = $scope.newDoNotCallSelectedAudioFile._id;
	}

	$scope.playPauseDoNotCallNewCreatedAudio = function(action){
		if(!$scope.newDoNotCallSelectedAudioFile._id){
			return;
		}
		var audio = document.getElementById('doNotCallCreatedAudioFile');
		if(action == 'play'){
			audio.play();
			$scope.isDoNotCallNewCreatedFilePlaying = true;
			audio.addEventListener('ended', function() {
		        	$scope.isDoNotCallNewCreatedFilePlaying = false;
		        	$scope.$apply();
		    }, false);
		} else{
			audio.pause();
			$scope.isDoNotCallNewCreatedFilePlaying = false;
		}
	}

	/*
	|---------------------------------------------------------
	| LIVE TRANSFER
	|---------------------------------------------------------
	| Select phone numbers for live transfer interactions
	*/
	if(editingCampaign && editingCampaign.transfer_option){
		$scope.liveTransferNumbers = editingCampaign.transfer_option.split();
	} else{
		$scope.liveTransferNumbers = [];
	}

	var currentTariffId = null;
	$scope.addRemoveTransfer = function(number, tariffId){
		var indexOfNumber = $scope.liveTransferNumbers.indexOf(number);
		if(indexOfNumber == -1){
			if(currentTariffId && currentTariffId != tariffId){
				return;
			}
			currentTariffId = tariffId;
			$scope.liveTransferNumbers.push(number)
		} else{
			$scope.liveTransferNumbers.splice(indexOfNumber, 1);
			if($scope.liveTransferNumbers.length == 0){
				currentTariffId = null;
			}
		}
	}


	/*
	|---------------------------------------------------------
	| SCHEDULE CAMPAIGN
	|---------------------------------------------------------
	| Here user can schedule campaign, and split delivery
	| 
	*/
	$scope.currentMaximumAvailable = $scope.finalStepData.numbersCount;
	var rangeSliderTemplate = {min:1, max:1};

	if(editingCampaign && editingCampaign.schedulations.length > 0){
		$scope.schedulations = [];
		for(index in editingCampaign.schedulations){
			$scope.schedulations.push({
				date: editingCampaign.schedulations[index].scheduled_date, min: 1,
				max: editingCampaign.schedulations[index].calls_limit,
				sending_time: editingCampaign.schedulations[index].delivery_speed
 			});
		}
	} else{
		$scope.schedulations = [{date: '', min:1, max:1}];
	}

	$scope.canAddSchedule = function(){
		var schedCount = $scope.schedulations.length + 1;
		var phonenumbersCount = $scope.finalStepData.numbersCount;
		if(phonenumbersCount/schedCount >= 1){
			return true;
		}
		return false;
	}

	$scope.$watch('schedulations', function(newVal, oldVal){
		for(index in newVal){
			if(newVal[index].date < new Date()){
				newVal[index].date = '';
			}
		}
	}, true)

	$scope.addSchedule = function(){
		$scope.schedulations.push({date: '', min:1, max:1});
	}

	$scope.removeSchedule = function(ind){
		$scope.schedulations.splice(ind, 1);
	}

	$scope.getMaximum = function(ind){
		if(!$scope.finalStepData.numbersCount){
			return $scope.schedulations[ind].max;
		}
		var currentSum = 0;
		for(index in $scope.schedulations){
			currentSum += Number( $scope.schedulations[index].max );
		}
		$scope.currentMaximumAvailable = $scope.finalStepData.numbersCount - Number( currentSum );
		var currentCount = $scope.schedulations[ind].max;
		var finalRes = Number( currentCount  + $scope.currentMaximumAvailable ) ;
		return finalRes > 0 ? finalRes : 1;
	}

	$scope.canScheduleCampaign = function(){
		var maxSums = 0;
		for(index in $scope.schedulations){
			if(!$scope.schedulations[index].date){
				return false;
			}
			maxSums += $scope.schedulations[index].max;
		}
		return maxSums == $scope.finalStepData.numbersCount;
	}
	

	$scope.composeStep1ErrorMessage = false;

	var addInteractionDetailsToData = function(postData){
		postData.is_replay_active = $scope.replayDigit.onOff == 'on' ? true : false;
		postData.is_transfer_active = $scope.transferDigit.onOff == 'on' ? true : false;
		postData.is_callback_active = $scope.callbackDigit.onOff == 'on' ? true : false;
		postData.is_donotcall_active = $scope.doNotCallDigit.onOff == 'on' ? true : false;
		return postData;
	}

	$scope.showSentMessageSuccessModal = false;
	var proceedSaveCampaign = function(){
		var postData = $scope.campaignData;
		postData.schedulations = null;
		var requestUrl = 'campaigns/create-campaign';
		if(editingCampaign){
			requestUrl = 'campaigns/update-campaign';
			postData.campaign_id = editingCampaign._id;
		}

		postData.status = 'start';
		postData.is_replay_active = $scope.replayDigit.onOff == 'on' ? true : false;
		postData.is_transfer_active = $scope.transferDigit.onOff == 'on' ? true : false;
		postData.is_callback_active = $scope.callbackDigit.onOff == 'on' ? true : false;
		postData.is_donotcall_active = $scope.doNotCallDigit.onOff == 'on' ? true : false;

		$rootScope.startLoader();
		Restangular.all(requestUrl).post(postData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.showSentMessageSuccessModal = true;
				$scope.showSaveCampaignModalWithInteractions = false;
			}
			else{
				$scope.composeStep1ErrorMessage = data.resource.error.text;
			}
		})
	}

	$scope.saveCampaign = function(){
		doValidation(proceedSaveCampaign, 'save');
	}

	/*var saveCampaignProceed = function(){
		var isAnyActive = checkIfActiveInteraction();
		if(!isAnyActive){
			$scope.proceedSaveCampaign();
		} else{
			$scope.showSaveCampaignModalWithInteractions = true;
		}
	}*/

	$scope.showSchedulationSuccessModal = false;

	$scope.scheduleCampaign = function(){
		doValidation(scheduleCampaignProceed, 'schedule');
	}

	var scheduleCampaignProceed = function(){
		var postData = $scope.campaignData;
		postData.schedulations = $scope.schedulations;

		for(index in postData.schedulations){
			postData.schedulations[index].date = moment(postData.schedulations[index].date).format('YYYY-MM-DD HH:mm:ss');
		}

		var requestUrl = 'campaigns/create-campaign';
		if(editingCampaign){
			requestUrl = 'campaigns/update-campaign';
			postData.campaign_id = editingCampaign._id;
		}

		postData.is_replay_active = $scope.replayDigit.onOff == 'on' ? true : false;
		postData.is_transfer_active = $scope.transferDigit.onOff == 'on' ? true : false;
		postData.is_callback_active = $scope.callbackDigit.onOff == 'on' ? true : false;
		postData.is_donotcall_active = $scope.doNotCallDigit.onOff == 'on' ? true : false;
		postData.status = 'scheduled';
		$rootScope.startLoader();
		Restangular.all(requestUrl).post(postData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.showSchedulationSuccessModal = true;
			}
			else{
				$scope.composeStep1ErrorMessage = data.resource.error.text;
			}
		})
	}

	$scope.saveAsDraft = function(){
		doValidation(saveAsDraftProceed, 'draft');
	}

	var saveAsDraftProceed = function(){
		var postData = $scope.campaignData;
		postData.schedulations = null;
		postData.status = 'saved';
		var requestUrl = 'campaigns/create-campaign';
		if(editingCampaign){
			requestUrl = 'campaigns/update-campaign';
			postData.campaign_id = editingCampaign._id;
		}

		postData.is_replay_active = $scope.replayDigit.onOff == 'on' ? true : false;
		postData.is_transfer_active = $scope.transferDigit.onOff == 'on' ? true : false;
		postData.is_callback_active = $scope.callbackDigit.onOff == 'on' ? true : false;
		postData.is_donotcall_active = $scope.doNotCallDigit.onOff == 'on' ? true : false;

		$rootScope.startLoader();
		Restangular.all(requestUrl).post(postData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$state.go('campaign.overview');
			}
			else{
				$scope.composeStep1ErrorMessage = data.resource.error.text;
			}
		})
	}

	$scope.showSendPreviewCallModal = false;
	$scope.sendPreviewCallSuccessText = false;
	$scope.sendPreviewCallErrorText = false;

	$scope.sendPreviewToYourPhone = function(){
		doValidation(sendPreviewToYourPhoneProceed, 'preview');
	}

	var sendPreviewToYourPhoneProceed = function(){
		var postData = {};
		for(index in $scope.campaignData){
			postData[index] = $scope.campaignData[index];
		}
		postData.phonenumbers = [$scope.campaignData.caller_id];
		postData.status = 'start';

		postData.is_replay_active = $scope.replayDigit.onOff == 'on' ? true : false;
		postData.is_transfer_active = $scope.transferDigit.onOff == 'on' ? true : false;
		postData.is_callback_active = $scope.callbackDigit.onOff == 'on' ? true : false;
		postData.is_donotcall_active = $scope.doNotCallDigit.onOff == 'on' ? true : false;

		/*if(editingCampaign){
			postData.unique_string_for_batch_grouping = editingCampaign.unique_string_for_batch_grouping;
		}*/
		$scope.showSendPreviewCallModal = false;
		$rootScope.startLoader();
		Restangular.all('campaigns/create-campaign').post(postData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.sendPreviewCallSuccessText = 'Message sent!';
			} else{
				$scope.sendPreviewCallErrorText = data.resource.error.text;
			}
		})
	}

	var doValidation = function(cb){
		var campaignData = $scope.campaignData;
		var isValid = true;
		var errorMessage = '';
		if($scope.callbackDigit.onOff == 'on'){
			if(!campaignData.callback_digit || !campaignData.callback_voice_file_id){
				isValid = false;
				errorMessage = 'callback_voice_file_required_with_callback_digit';
			}
		}
		if($scope.doNotCallDigit.onOff == 'on'){
			if(!campaignData.do_not_call_digit || !campaignData.do_not_call_voice_file_id){
				isValid = false;
				errorMessage = 'donotcall_voice_file_required_with_donotcall_digit';
			}
		}
		if($scope.transferDigit.onOff == 'on'){
			if(!campaignData.transfer_digit || !campaignData.transfer_options){
				isValid = false;
				errorMessage = 'transfer_options_required_with_transfer_digit';
			}
		}
		if($scope.replayDigit.onOff == 'on'){
			if(!campaignData.replay_digit){
				isValid = false;
				errorMessage = 'replay_digit_is_activated_but_not_selected';
			}
		}
		if(!isValid){
          	notify({message: errorMessage, classes: ['notification-alert-danger']})
		} else{
			checkIfActiveInteraction(cb, arguments[1]);
		}
	}

	/*************** MODAL EFFECTS ****************/
	var arrayOfModals = [
		'showSendPreviewCallModal', 'showSelectCallerIdModel',
		'showSchedulationModal', 'showSchedulationSuccessModal',
		'showSentMessageSuccessModal', 'showTtsNotificationModal',
		'showSaveCampaignModalWithInteractions', 'showEstimatedSendingTimeModal',
		'contactsModalShow'
	];
	var arrayOfInteractionModals = [
		'callbackDigit.modalStep',
		'doNotCallDigit.modalStep',
		'replayDigit.modalStep',
		'transferDigit.modalStep'
	];
	$scope.$watchGroup(arrayOfModals, function(newValues, oldValues, scope) {
		var needBlur = false;
		for(index in newValues){
			if(newValues[index]){
				needBlur = true;
				break;
			}
		}
		$rootScope.showBlurEffect = needBlur;
	});
	$scope.$watchGroup(arrayOfInteractionModals, function(newValues, oldValues, scope) {
		var needBlur = false;
		for(index in newValues){
			if(newValues[index] == 1 || newValues[index] == 2){
				needBlur = true;
				break;
			}
		}
		$rootScope.showBlurEffect = needBlur;
	});

}]).directive('selectNumber', function(){
	return{
		restrict: 'E',
		templateUrl: '/app/templates/select-number.html',
		scope: {
	      interactionName: '=interaction',
	      origscope: '=origscope',
	      camelInteraction: '=camelinteraction'
	    },
	    controller: ['$scope', function($scope) {
			
			$scope.actionNumbersMatrix = [
				['1', '2', '3'],
				['4', '5', '6'],
				['7', '8', '9'],
				['',  '0', '' ]
			];

			/*$scope.getStatus = function(num){
				var usedNumbers = [];
				var validInteractions = ['transfer_digit', 'replay_digit', 'callback_digit', 'do_not_call_digit'];
				if($scope.origscope.campaignData[$scope.interactionName] == num){
					return 'active';
				}
				for(index in validInteractions){
					if(validInteractions[index] != $scope.interactionName){
						usedNumbers.push($scope.origscope.campaignData[validInteractions[index]]);
					}
				}
				var isInList = usedNumbers.indexOf(num);
				return isInList == -1 ? 'free' : 'selected';
			}*/
	    }],
	}
	
});