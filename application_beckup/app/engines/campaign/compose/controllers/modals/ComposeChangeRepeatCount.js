angular.module('callburnApp').controller('ComposeChangeRepeatCount', 
	[ 		'$scope', 'CampaignComposeService', 'close',
	function($scope,   CampaignComposeService,   close){
		
		$scope.CampaignComposeService = CampaignComposeService;
		$scope.repeatData = {};
		$scope.repeatSource = 'custom';

		$scope.discardRepeat = function(){
			CampaignComposeService.campaignData.remaining_repeats = null;
			CampaignComposeService.campaignData.repeat_days_interval = null;
			close('success');
		}

		$scope.saveRepeat = function(){
			if( $scope.repeatSource == 'once' || 
				!CampaignComposeService.campaignData.remaining_repeats ||
				!CampaignComposeService.campaignData.repeat_days_interval){
				CampaignComposeService.campaignData.remaining_repeats = null;
				CampaignComposeService.campaignData.repeat_days_interval = null;
			}
			close('success');
		}
	}])