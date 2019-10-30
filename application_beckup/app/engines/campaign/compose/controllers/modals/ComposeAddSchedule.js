angular.module('callburnApp').controller('ComposeAddSchedule', 
	[ 		'$scope', '$rootScope', 'Restangular', 'ttsLanguages',  'notify','FileUploader', 'CampaignComposeService',
	function($scope,   $rootScope,   Restangular,   ttsLanguages,    notify,  FileUploader,   CampaignComposeService){
		
		$scope.CampaignComposeService = CampaignComposeService;

	}])