angular.module('callburnApp').controller('ComposeActivateCallback', 
	[ 		'$scope', '$rootScope','close', 'Restangular', '$sce', '$timeout', 'notify','FileUploader', 'CampaignComposeService',
	function($scope,   $rootScope,  close,   Restangular,   $sce,   $timeout,   notify,  FileUploader,   CampaignComposeService){


		//Share all data with controller
		$scope.CampaignComposeService = CampaignComposeService;
		$scope.callbackStep = 1;
		$scope.dismissModal = function(result) {
			close(result);
		};
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


		/*
		|---------------------------------------------------------
		| CALLBACK DIGIT - METHOD 1 - TTS
		|---------------------------------------------------------
		| Use tts , for creating audio file from text 
		| choosing language
		|
		*/
		$scope.callbackTtsData = {language: null};

		$scope.createAudioFromTextForCallback = function(){
			$rootScope.startLoader();
			Restangular.all('campaigns/create-audio-from-text').post($scope.callbackTtsData).then(function(data){
				$rootScope.stopLoader();
				if(data.resource.error.no == 0){
					$scope.callbackTtsData = {language: null};
					var audioData = {
						source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
						file: data.resource.file
					}
					$scope.callbackAudioFiles.push(audioData);
					CampaignComposeService.audioTemplates.push(data.resource.file);
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
		    autoUpload : true
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

		$scope.activateCallbackDigit = function(){
			if(!CampaignComposeService.callbackDigit.checkboxChecked){
				return;
			}
			CampaignComposeService.callbackDigit.onOff = 'on';
			close('success');
		}


	}])