angular.module('callburnApp').controller('StatisticsController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', '$stateParams', 'phonenumbers', 'repeats',
	function($scope,   $rootScope,   $state,    Restangular,   $stateParams,   phonenumbers,   repeats){

	$scope.repeatCampaigns = repeats ? repeats.resource.campaigns: null;
	$scope.goToNotification = $rootScope.goToNotification;
	$scope.isMultiple = $stateParams.is_multiple;
	$rootScope.currentActiveRoute = 'campaign';

	$rootScope.currentPage = 'dashboard';

	$scope.checkedPhonenumbers = {};
	$scope.isAllChecked = false;
	var totalCostOfCampaign = 0;

	$rootScope.showPreviousIcon = true;

	$rootScope.previousStep = function(){
		$state.go('campaign.overview');
	}

	var updatePhonenumbers = function(data){
		totalCostOfCampaign = data.resource.total_cost;
		$scope.phonenumbers = data.resource.phonenumbers;
		$scope.phonenumbersPage = data.resource.page;
		$scope.pagesCount = Math.ceil(data.resource.phonenumbers_count/10);
	}
	updatePhonenumbers(phonenumbers);

	$rootScope.footerData = {
		first:  '<span>You can export statistics into a file</span>' + 
				'<span>Just use “export” functionality</span>',
		second: '<span>For sending these messages</span>' + 
				'<span>you have spent a total of € ' + totalCostOfCampaign + '</span>',
		third:  '<span>Your delivery rate is</span>' + 
				'<span>' + $rootScope.dashboardData.deliver_rate + ' %</span>'
	}

	$scope.checkedUncheckPhonenumber = function(phonenumberId, event)
	{
		$scope.checkedPhonenumbers[phonenumberId] = $scope.checkedPhonenumbers[phonenumberId] ? !$scope.checkedPhonenumbers[phonenumberId] : true; 
	}

	$scope.checkUncheckAll = function(){
		$scope.isAllChecked = !$scope.isAllChecked;
		for(index in $scope.phonenumbers){
			$scope.checkedPhonenumbers[$scope.phonenumbers[index]._id] = $scope.isAllChecked;
		}
	}

	$scope.currentOrder = 'ASC';
	

	$scope.filterData = $stateParams;
	if($stateParams.action){
		$scope.filterData.action = $stateParams.action;
	}
	if($stateParams.campaign_batch){
		$scope.filterData.campaign_batch = $stateParams.campaign_batch;
	}

	$scope.phonecallStatuses = {
		'TRANSFER_REQUESTED' : 'Transfer',
		'REPLAY_REQUESTED' : 'Replay',
		'CALLBACK_REQUESTED' : 'Callback',
		'DONOTCALL_REQUESTED' : 'Blacklist'
	}

	$scope.getCost = function(tariff, duration){
		return (duration < 20) ? tariff.standard_price : tariff.custom_price * duration / 60;
	}

	$scope.filterChanged = function(){
		$rootScope.startLoader();
		Restangular.one('campaigns/show-campaign-numbers').get($scope.filterData).then(function(phonenumbers){
			$rootScope.stopLoader();
			updatePhonenumbers(phonenumbers);
		});
	}

	$scope.changeOrder = function(field){
		$scope.filterData.page = 0;
		if(field == $scope.filterData.order_field){
			$scope.currentOrder = ($scope.currentOrder == 'ASC') ? 'DESC' : 'ASC';
		}
		$scope.filterData.order_field = field;

		$scope.filterData.order = $scope.currentOrder;
		Restangular.one('campaigns/show-campaign-numbers').get($scope.filterData).then(function(phonenumbers){
			updatePhonenumbers(phonenumbers);
		});
	}

	$scope.changePage = function(page){
		if(page < 0 || page > $scope.pagesCount - 1){
			return;
		}
		$scope.filterData.page = page;
		$rootScope.startLoader();
		Restangular.one('campaigns/show-campaign-numbers').get($scope.filterData).then(function(phonenumbers){
			$rootScope.stopLoader();
			updatePhonenumbers(phonenumbers);
		});
	}

	$scope.activePhonenumber = {actions: []};
	$scope.openActionsModal = function(phonenumber){
		$scope.activePhonenumber = phonenumber;
		$scope.showAttemptsModal = true;
	}

	$scope.removeFromPhonebook = function(){
		if(isPhonenumbersEmpty()){
			alert('No phonenumber selected');
			return;
		}
		$rootScope.startLoader();
		Restangular.all('phonenumbers/remove-from-phonebook').post({phonenumber_ids: $scope.checkedPhonenumbers}).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				//alert('done');
			}
		})
	}

	$scope.sendAgain = function(){
		if(isPhonenumbersEmpty()){
			alert('No phonenumber selected');
			return;
		}
		var sendingPhonenumbers = [];
		for(index in $scope.checkedPhonenumbers){
			if($scope.checkedPhonenumbers[index]){
				for(phonenumbersIndex in $scope.phonenumbers){
					if($scope.phonenumbers[phonenumbersIndex]._id == index){
						var pushingObject = {
							number: $scope.phonenumbers[phonenumbersIndex].phone_no,
							status: 'success',
							tariff: $scope.phonenumbers[phonenumbersIndex].tariff
						}
						sendingPhonenumbers.push(pushingObject);
					}
				}
			}
		}
		sendingPhonenumbers = JSON.stringify(sendingPhonenumbers);
		$state.go('campaign.compose-from-phonenumbers', {phonenumbers: sendingPhonenumbers});
	}

	$scope.exportCampaign = function(){
		if(isPhonenumbersEmpty()){
			var postData = $scope.filterData;
		} else{
			var postData = {phonenumber_ids: $scope.checkedPhonenumbers, campaign_batch: $stateParams.campaign_batch};
		}
		window.location.href = '/phonenumbers/export-statistics?export_data=' + JSON.stringify(postData);
	}

	$scope.getAudioTooltipHtml = function(campaign, action){
		if(!campaign){
			return;
		}
		if(action == 'callback'){
			var voiceFileId = campaign.callback_digit_file_id;
		} else if(action == 'donotcall'){
			var voiceFileId = campaign.do_not_call_digit_file_id;
		} else{
			var voiceFileId = campaign.campaign_voice_file_id;
		}

		var str = '<audio src="' + apiUrl + '?key=' + $rootScope.currentUser.api_token + 
					'&file_id=' + voiceFileId + '" controls style="display:none;" id="campaignAudio' + 
					voiceFileId + '"></audio>';
		str = str + '<img src="/assets/callburn/images/play.png" class="compose_method3_icons" onclick="playAudio(' + voiceFileId + ')" />&nbsp;&nbsp;Play <br><br>';
		str = str + '<img src="/assets/callburn/images/stop1.png" class="compose_method3_icons" onclick="pauseAudio(' + voiceFileId + ')" />&nbsp;&nbsp;Pause ';
		return str;
	}

	var isPhonenumbersEmpty = function(){
		for(index in $scope.checkedPhonenumbers){
			if($scope.checkedPhonenumbers[index]){
				return false;
			}
		}
		return true;
	}

	window.playAudio = function(id){
		var audio = document.getElementById('campaignAudio' + id);
		audio.play();
	}

	window.pauseAudio = function(id){
		var audio = document.getElementById('campaignAudio' + id);
		audio.pause();
	}

	/*var queryString = 'batch=' + $stateParams.campaign_batch;
	var statisticsSocket = io.connect(window.socketUrl, { query: queryString });
	statisticsSocket.on('statistics_message', function(data){
		var parsedData = JSON.parse(data);
		console.log(parsedData);
		for(index in parsedData){
			var newPhonenumber = parsedData[index];
			for(phonenumberIndex in $scope.phonenumbers){
				if($scope.phonenumbers[phonenumberIndex]._id == newPhonenumber.phonenumber_id){
					$scope.phonenumbers[phonenumberIndex].call_status = newPhonenumber.call_status;
					$scope.phonenumbers[phonenumberIndex].duration = newPhonenumber.duration;
					$scope.phonenumbers[phonenumberIndex].dialled_datetime = newPhonenumber.dialled_datetime;
				}
			}
		}
		$scope.$apply();
	});*/

	$scope.$watch('phonenumbers', function(newVal){}, 1);

}]);