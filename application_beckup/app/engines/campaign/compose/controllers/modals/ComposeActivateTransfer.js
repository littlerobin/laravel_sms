angular.module('callburnApp').controller('ComposeActivateTransfer', 
	[ 		'$scope', '$rootScope', 'Restangular', 'close', 'notify','FileUploader', 'CampaignComposeService',
	function($scope,   $rootScope,   Restangular,   close ,  notify,  FileUploader,   CampaignComposeService){

		$scope.CampaignComposeService = CampaignComposeService;
		$scope.transferStep = 1;

		$scope.dismissModal = function(result) {
			close(result);
		};
		/*
		|---------------------------------------------------------
		| LIVE TRANSFER
		|---------------------------------------------------------
		| Select phone numbers for live transfer interactions
		*/
		if(CampaignComposeService.editingCampaign && CampaignComposeService.editingCampaign.transfer_option){
			$scope.liveTransferNumbers = CampaignComposeService.editingCampaign.transfer_option.split();
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

		$scope.activateTransferDigit = function(){
			if(!CampaignComposeService.transferDigit.checkboxChecked){
				return;
			}
			CampaignComposeService.transferDigit.onOff = 'on';
			CampaignComposeService.campaignData.transfer_options = $scope.liveTransferNumbers.join();
			close('success');
		}
	}])