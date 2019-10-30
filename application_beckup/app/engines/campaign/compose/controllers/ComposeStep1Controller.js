angular.module('callburnApp').controller('ComposeStep1Controller', 
	[ 		'$scope', '$rootScope', 'Restangular', 'notify','FileUploader', '$sce', '$timeout', 'CampaignComposeService',
	function($scope,   $rootScope,   Restangular,   notify,  FileUploader,   $sce ,  $timeout,   CampaignComposeService){

		$scope.CampaignComposeService = CampaignComposeService;
		CampaignComposeService.ttsLanguages.forEach(function (item) {
			item.selectView = item.languageName + '-' + item.ttsEngine;
 		});
		$scope.images=[ "/assets/callburn/images/manually-or-file-icon.png"];
		$scope.ttsData = {language: ''};
		$scope.getTTSUrl = function(){
			return $sce.trustAsResourceUrl('/assets/callburn/tts/' + $scope.ttsData.language + '.wav')
		}
	
		$scope.playTTSDemo = function(){
			document.getElementById('ttsDemoAudio').play();
		}

		/*
		 * Send request to server to create audio file.
		 */
		var createAudioFromTextForVoice = function(){
			$rootScope.startLoader();
			Restangular.all('campaigns/create-audio-from-text').post($scope.ttsData).then(function(data){
				$rootScope.stopLoader();
				if(data.resource.error.no == 0){
					$scope.ttsData = {language: ''};
					CampaignComposeService.finalStepData.voiceFile = data.resource.file;
					CampaignComposeService.campaignData.campaign_voice_file_id = data.resource.file._id;
					CampaignComposeService.composeStep = 2;
				} else{
					notify({message: data.resource.error.text, classes: ['notification-alert-danger']});
				}
			})
		}

		/*
		 * Make uploader instance to upload audio files.
		 */
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
				CampaignComposeService.finalStepData.voiceFile = response.resource.file;
				CampaignComposeService.campaignData.campaign_voice_file_id = response.resource.file._id;
				CampaignComposeService.composeStep = 2;
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

		$scope.openCampaignVoiceFileSelect = function(){
			$timeout(function() {
			    angular.element('#campaignVoiceFileInput1').trigger('click');
			}, 100);
		}

	}])
