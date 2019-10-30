angular.module('callburnApp').controller('AddAudioTemplateController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', 'ttsLanguages', 'FileUploader', 'notify', '$timeout',
	function($scope,   $rootScope,   $state,    Restangular,   ttsLanguages,   FileUploader,   notify,   $timeout){

	$scope.goToNotification = $rootScope.goToNotification;
	$scope.ttsLanguages = ttsLanguages.resource.languages;
	$scope.ttsData = {language: ''};

	$scope.createAudioFromTextForVoice = function(){
		console.log('uf');

		$rootScope.startLoader();
		$scope.ttsData.language = $scope.ttsData.language.languageId;
		console.log($scope.ttsData);
		Restangular.all('campaigns/create-audio-from-text').post($scope.ttsData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$state.go('campaign.templates');
			} else{
				$scope.voiceTtsError = data.resource.error.text;
			}
		})
	}


	$scope.openCampaignVoiceFileSelect = function(){
		$timeout(function() {
		    angular.element('#campaignVoiceFileInput1').trigger('click');
		}, 100);
	}

	var campaignVoiceMessageUpload = $scope.campaignVoiceMessageUpload = new FileUploader({
		formData: [{is_template: 1}],
	    url: 'campaigns/upload-audio-file',
	    alias : 'file',
	    autoUpload : true
	});

	campaignVoiceMessageUpload.onAfterAddingFile = function(item){
		$scope.uploadingAudioName = item.file.name;
	}

	campaignVoiceMessageUpload.onBeforeUploadItem  = function(){
		$rootScope.startLoader();
	}

	campaignVoiceMessageUpload.onSuccessItem = function(item, response, status, headers){
		$rootScope.stopLoader();
		if(response.resource.error.no == 0){
			$state.go('campaign.templates');
		} else{
          	notify({message: response.resource.error.text, classes: ['notification-alert-danger']})
		}
	};

	campaignVoiceMessageUpload.onErrorItem = function(item, response, status, headers){
		$rootScope.stopLoader();
        notify({message: 'Something went wrong', classes: ['notification-alert-danger']})
	}
}])