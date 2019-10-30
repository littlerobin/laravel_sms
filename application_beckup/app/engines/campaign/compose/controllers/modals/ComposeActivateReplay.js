angular.module('callburnApp').controller('ComposeActivateReplay', 
	[ 		'$scope', '$rootScope', 'Restangular', 'close',  'notify','FileUploader', 'CampaignComposeService',
	function($scope,   $rootScope,   Restangular,   close,    notify,  FileUploader,   CampaignComposeService){


		$scope.CampaignComposeService = CampaignComposeService;
		$scope.dismissModal = function(result) {
			close(result);
		};

		$scope.replyDigitActivated = function(){
			if(!CampaignComposeService.replayDigit.checkboxChecked){
				return;
			}
			CampaignComposeService.replayDigit.onOff = 'on';
			close('success');
		}

	}])