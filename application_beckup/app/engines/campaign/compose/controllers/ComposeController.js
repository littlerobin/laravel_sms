angular.module('callburnApp').controller('ComposeController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', '$stateParams', 'ttsLanguages',  'notify',
			'$sce', '$timeout', 'audioFiles', 'editingCampaign', 'reusingCampaign', 'CampaignComposeService',
	function($scope,   $rootScope,   $state,    Restangular,   $stateParams,   ttsLanguages,  notify,  
			 $sce,   $timeout,   audioFiles,   editingCampaign,   reusingCampaign,  CampaignComposeService){

		var copyOfCampaignService = {};
		angular.copy(CampaignComposeService, copyOfCampaignService);

		$scope.$on('$destroy', function(){
			angular.copy(copyOfCampaignService, CampaignComposeService);
		});


		$scope.CampaignComposeService = CampaignComposeService;
		$scope.CampaignComposeService.ttsLanguages = ttsLanguages.resource.languages;
		$scope.CampaignComposeService.audioTemplates = audioFiles.resource.files;

		if(reusingCampaign){
			CampaignComposeService.isEdit = true;
			reusingCampaign = reusingCampaign.resource.campaign;
			CampaignComposeService.campaignData = {
				caller_id: $rootScope.currentUser.numbers[0].phone_number,
				get_email_notifications: true
			};
			var reusingSource = $scope.reusingSource = $stateParams.reusing_source;
			if(reusingSource == 'message') {
				CampaignComposeService.finalStepData.voiceFile = reusingCampaign.voice_file;
				CampaignComposeService.campaignData.voice_file = reusingCampaign.voice_file;
				CampaignComposeService.campaignData.campaign_voice_file_id = reusingCampaign.voice_file._id;
				CampaignComposeService.composeStep = 2;
			}
			if(reusingSource == 'receipents') {
				CampaignComposeService.currentPhonenumbers = reusingCampaign.phonenumbers;
				CampaignComposeService.campaignData.current_phonenumbers_count = reusingCampaign.phonenumbers.length;
				CampaignComposeService.campaignData.removed_phonenumbers = [];
				CampaignComposeService.finalStepData.numbersCount = reusingCampaign.phonenumbers.length;
				CampaignComposeService.composeStep = 1;
			}
			if(reusingSource == 'both'){
				CampaignComposeService.finalStepData.voiceFile = reusingCampaign.voice_file;
				CampaignComposeService.campaignData.voice_file = reusingCampaign.voice_file;
				CampaignComposeService.campaignData.campaign_voice_file_id = reusingCampaign.voice_file._id;
				CampaignComposeService.currentPhonenumbers = reusingCampaign.phonenumbers;
				CampaignComposeService.campaignData.current_phonenumbers_count = reusingCampaign.phonenumbers.length;
				CampaignComposeService.campaignData.removed_phonenumbers = [];
				CampaignComposeService.finalStepData.numbersCount = reusingCampaign.phonenumbers.length;
				CampaignComposeService.composeStep = 3;
			}
		}
		else if(editingCampaign){
			CampaignComposeService.isEdit = true;
			CampaignComposeService.composeStep = 3;
			editingCampaign = editingCampaign.resource.campaign;
			CampaignComposeService.currentPhonenumbers = editingCampaign.phonenumbers;
			CampaignComposeService.campaignData = {
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
			CampaignComposeService.finalStepData = {
				voiceFile: editingCampaign.voice_file,
				numbersCount: editingCampaign.total_phonenumbers_loaded,
			}
		} else{
			CampaignComposeService.campaignData = {
				caller_id: $rootScope.currentUser.numbers[0].phone_number,
				get_email_notifications: true
			};

			if($stateParams.audioFile){
				CampaignComposeService.composeStep = 2;
				var audioData = {
					source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + $stateParams.audioFile._id),
					file: $stateParams.audioFile
				}
				CampaignComposeService.finalStepData.voiceFile = $stateParams.audioFile;
				CampaignComposeService.campaignData.campaign_voice_file_id = $stateParams.audioFile;
			}
		}

	}]).directive('selectNumber', function(){
		return{
			restrict: 'E',
			templateUrl: '/app/templates/select-number.html',
			scope: {
		      interactionName: '=interaction',
		      origscope: '=origscope',
		      camelInteraction: '=camelinteraction'
		    },
		    controller: ['$scope', 'CampaignComposeService', function($scope, CampaignComposeService) {
				$scope.CampaignComposeService = CampaignComposeService;
				$scope.actionNumbersMatrix = [
					['1', '2', '3'],
					['4', '5', '6'],
					['7', '8', '9'],
					['',  '0', '' ]
				];

				/*$scope.getStatus = function(num){
					var usedNumbers = [];
					var validInteractions = ['transfer_digit', 'replay_digit', 'callback_digit', 'do_not_call_digit'];
					if(CampaignComposeService.campaignData[$scope.interactionName] == num){
						return 'active';
					}
					for(index in validInteractions){
						if(validInteractions[index] != $scope.interactionName){
							usedNumbers.push(CampaignComposeService.campaignData[validInteractions[index]]);
						}
					}
					var isInList = usedNumbers.indexOf(num);
					return isInList == -1 ? 'free' : 'selected';
				}*/
		    }],
		}
		
	});