angular.module('callburnApp').controller('ComposeConfirmInteractions', 
	[ 		'$scope', 'CampaignComposeService', 'close', 'action',
	function($scope,   CampaignComposeService,   close,   action){
		
		$scope.CampaignComposeService = CampaignComposeService;


		$scope.dismissModal = function(){
			close('success');
		}

		$scope.proceedToSaveWithInteraction = function(){
			CampaignComposeService.proceedToSaving(action);
			close('waiting');
		}
	}])