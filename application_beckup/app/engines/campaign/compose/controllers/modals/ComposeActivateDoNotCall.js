angular.module('callburnApp').controller('ComposeActivateDoNotCall', 
	[ 		'$scope', '$rootScope', '$sce', '$timeout', 'Restangular', 'close', 'notify','FileUploader', 'CampaignComposeService',
	function($scope,   $rootScope,   $sce,   $timeout,   Restangular,   close,   notify,  FileUploader,   CampaignComposeService){

		$scope.CampaignComposeService = CampaignComposeService;
		$scope.doNotCallStep = 1;
		$scope.dismissModal = function(result) {
			close(result);
		};
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
		$scope.openCampaignVoiceFileSelect = function(){
			$timeout(function() {
			    angular.element('#campaignVoiceFileInput1').trigger('click');
			}, 100);
		}
		$scope.doNotCallTtsData = {language: null};

		$scope.createAudioFromTextForDoNotCall = function(){
			$rootScope.startLoader();
			Restangular.all('campaigns/create-audio-from-text').post($scope.doNotCallTtsData).then(function(data){
				$rootScope.stopLoader();
				if(data.resource.error.no == 0){
					$scope.doNotCallTtsData = {language: null};
					var audioData = {
						source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
						file: data.resource.file
					}
					$scope.doNotCallAudioFiles.push(audioData);
					CampaignComposeService.audioTemplates.push(data.resource.file);
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
		    autoUpload : true
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


		if(CampaignComposeService.editingCampaign && CampaignComposeService.editingCampaign.do_not_call_file){
			$scope.doNotCallAudioFiles.push({file: CampaignComposeService.editingCampaign.do_not_call_file});
			$scope.newDoNotCallSelectedAudioFile = CampaignComposeService.editingCampaign.do_not_call_file;
		} else{
			$scope.newDoNotCallSelectedAudioFile = {};
		}

		$scope.showNewCreatedDoNotCallsDropDown = false;
		$scope.isDoNotCallNewCreatedFilePlaying = false;
		$scope.selectDoNotCallNewCreatedAudioFile = function(file){
			file = JSON.parse(file);
			$scope.newDoNotCallSelectedAudioFile = file;
			$scope.showNewCreatedDoNotCallsDropDown = false;
			CampaignComposeService.campaignData.do_not_call_voice_file_id = $scope.newDoNotCallSelectedAudioFile._id;
		}

		$scope.saveNewCreatedAsDoNotCall = function(){
			CampaignComposeService.campaignData.do_not_call_voice_file_id = $scope.newDoNotCallSelectedAudioFile._id;
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

		$scope.donotcallDigitActivated = function(){
			if(!CampaignComposeService.doNotCallDigit.checkboxChecked){
				return;
			}
			CampaignComposeService.doNotCallDigit.onOff = 'on';
			close('success');
		}
	}])