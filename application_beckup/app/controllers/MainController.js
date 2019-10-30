angular.module("callburnApp").controller('MainController',
	[ 		'$scope', '$rootScope', '$state', 'Restangular', '$stateParams','$sce', '$http',
	function($scope,   $rootScope,   $state,   Restangular,   $stateParams,  $sce,   $http){

	$rootScope.apiUrl = window.apiUrl;

	$rootScope.currentActiveRoute = null;
	$rootScope.currentLanguage = 'en';
	$rootScope.currentLanguageName = 'eng';
	$rootScope.currentTime = '';
	$rootScope.dashboardData = {};
	$rootScope.currentUser = {numbers: ['']};
	$rootScope.otherFooterData = {};
	$rootScope.languages = {};
	$rootScope.showBlurEffect = false;
	$rootScope.requestShowLoading = false;

	$scope.notifications = [];
	$scope.notSeenNotificationsCount = 0;

	$rootScope.isFooter1Active = false;
	$rootScope.isFooter2Active = false;
	$rootScope.isFooter3Active = false;

	var translate = {};

	angular.element('.displayNoneBody').removeClass('displayNoneBody');

	$scope.showHideSubmenuIconsGroups = false;

	$scope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams){
		$rootScope.showLoading = true;
		$rootScope.showPreviousIcon = false;
		$rootScope.showNextIcon = false;

		$rootScope.isFooter1Active = false;
		$rootScope.isFooter2Active = false;
		$rootScope.isFooter3Active = false;
	})

	if($stateParams.status){
		$scope.phonenumberMenuItem = {status: $stateParams.status};
		$scope.campaignMenuItem = {status: $stateParams.status};
	} else{
		$scope.phonenumberMenuItem = {status: 'addressbook.contacts'};
		$scope.campaignMenuItem = {status: 'campaign.overview'};
	}

	$scope.activePhonenumberMenu = function(status){
		$scope.phonenumberMenuItem.status = status ||  $scope.phonenumberMenuItem.status;
	}

	$scope.activeCampaignMenu = function(status){
		$scope.campaignMenuItem.status = status ||  $scope.campaignMenuItem.status;
	}

	$rootScope.startLoader = function(){
		$rootScope.showLoading = true;
		$rootScope.showBlurEffect = true;

	}

	$rootScope.stopLoader = function(){
		$rootScope.showLoading = false;
		$rootScope.showBlurEffect = false;
	}

	$scope.showNotifications = false;
	$scope.showHideNotifications = function(){
		$scope.showNotifications = !$scope.showNotifications;
	}

	$scope.topAccountShow = false;
	$scope.topAccountShowHide = function(){
		$scope.topAccountShow = !$scope.topAccountShow;
		$scope.quickActionShow =false;
	}

	$scope.quickActionShow = false;
	$scope.quickActionShowHide = function(){
		$scope.quickActionShow = !$scope.quickActionShow;
		$scope.topAccountShow=false;
	}

	$scope.closeWindow = function(){
		$scope.quickActionShow = false;
		$scope.topAccountShow = false;
	}

	$scope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){ 
		$rootScope.showLoading = false;
		$rootScope.showBlurEffect = false;
	});

	$scope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams, error){
		$rootScope.showLoading = false;
		$rootScope.showBlurEffect = false;
	})

	var updateTime = function(){
		Restangular.one('users/users-time').get().then(function(timeData){
			$rootScope.currentTime = timeData.resource.time;
		});
	}

	$rootScope.make_trusted = function(text){
		return $sce.trustAsHtml(text);
	}

	$rootScope.openChatWindow = function(){
		//console.log('working')
	}

	$rootScope.goToNotification = function(notification){
		if(notification.params){
			$state.go(notification.route, JSON.parse(notification.params));
		} else{
			$state.go(notification.route);
		}
	}

	Restangular.one('users/show-user').get().then(function(data){

		if(data.resource.user_data){
			$rootScope.currentUser = data.resource.user_data;
			updateTime()
			setInterval(function(){
				updateTime()
			}, 30000);

			var queryString = 'user_id=' + data.resource.user_data._id + '&api_key=' + data.resource.api_token;
			

			/******************** NOTIFICATIONS *************************/
			var userNotifications = data.resource.user_data.notifications;
			for(index in userNotifications){
				$scope.notifications.push(userNotifications[index]);
				if(!userNotifications[index].is_seen){
					$scope.notSeenNotificationsCount++;
				}
			}

			if(data.resource.user_data.numbers.length == 0){
				var callerIdNotification = {
					created_at: null,
					text: 'Caller id is missing',
					route: 'account.settings',
					params: false,
					is_seen: false,
					can_remove: false
				}
				$scope.notifications.unshift(callerIdNotification);
				$scope.notSeenNotificationsCount++;
			}
			if(data.resource.user_data.balance < 5){
				var lowBalanceNotification = {
					created_at: null,
					text: 'Your balance is low',
					route: 'account.financials',
					params: false,
					is_seen: false,
					can_remove: false
				}
				$scope.notifications.unshift(lowBalanceNotification);
				$scope.notSeenNotificationsCount++;
			}

			/******************** END NOTIFICATIONS **********************/


			$rootScope.currentUser.api_token = data.resource.api_token;
			/*Intercom("boot", {
		        app_id: "w07q90xq",
				name: data.resource.user_data.first_name,
				email: data.resource.user_data.email,
				user_id: data.resource.user_data._id,
		        widget: {
		          activator: "#IntercomDefaultWidget"
		        }
		    });*/
			nudgespot.identify(data.resource.user_data._id);
			if(data.resource.user_data.language){
				$rootScope.currentLanguage = data.resource.user_data.language.code;
				$rootScope.currentLanguageName=data.resource.user_data.language.name;
				$http.get('translations/back_translate_' + data.resource.user_data.language.code + '.json').success(function(data){
					translate[$rootScope.currentUser.language.code] = data;
				});
			}
			if(data.resource.user_data.numbers.length == 0){
				$rootScope.currentUser.numbers = [''];
			}

			/*var socket = io.connect(window.socketUrl, { query: queryString });
			socket.on('notification_message', function(data){
				var parsedData = JSON.parse(data);
				$scope.notifications.unshift(parsedData);
				$scope.notSeenNotificationsCount++;
			});*/
		}
	});

	/*Restangular.one('data/dashboard').get().then(function(data){
		$rootScope.dashboardData = data.resource;
	});

	Restangular.one('data/footer-data').get().then(function(data){
		$rootScope.otherFooterData = data.resource;
	});*/

	Restangular.one('data/languages').get().then(function(data){

		$rootScope.flags=[];
		$rootScope.languages = data.resource.languages;
		$rootScope.languages.forEach(function (language) {
			$rootScope.flags.push("/assets/callburn/images/flags/"+language.code+".png");
		});
	});


	$scope.showLanguageSelect = false;
	$scope.hideLanguage = function(){
		$scope.showLanguageSelect = false;
	}

	$scope.showHideLanguageBar = function(){
		$scope.showLanguageSelect = !$scope.showLanguageSelect;
	}

	var langRequestInProcess = false;
	$rootScope.trans = function(str){
		var lang = $rootScope.currentLanguage ? $rootScope.currentLanguage : 'en';
		if(!translate[lang]){
			if(!langRequestInProcess){
				langRequestInProcess = true;
				$http.get('translations/back_translate_' + lang + '.json').success(function(data){
					translate[lang] = data;
					langRequestInProcess = false;
				});
			}
		} else{
			return translate[lang][str] ? translate[lang][str] : str;
		}
	}

	$scope.changeLanguage = function(lang){
		$rootScope.languages.forEach(function (item) {
			if(item.code==lang){
				Restangular.all('users/update-main-data').post({language_id:item._id}).then(function (data) {
					$rootScope.currentLanguage = lang;
				});
			}
		});



	}


	$rootScope.checkIfLogged = function(response){
		if(response){
			if(response.error.no == -10){
				$state.go('login');
			} else{
				return true;
			}
		}
	}

	$rootScope.logOut = function(){
      Restangular.one('users/logout').get().then(function(data){
        window.location = frontUrl;
      }, function(response){
      });
    }

	$rootScope.days = [
		'00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11',
		'12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23',
		'24', '25', '26', '27', '28', '29', '30', '31'
	];
	$rootScope.hours = [
		'00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11',
		'12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23',
	];

	$rootScope.minutes = [
		'00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11',
		'12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23',
		'24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35',
		'36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47',
		'48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
	];

	$rootScope.footerDataLoaded = true;
	/*Restangular.all('data/footer-data').get().then(function(data){
		if(data.resource.error.no == 0){
			console.log(data.resource);
		}
	})*/

	$rootScope.getStatusName = function(status){
		switch(status){
			case '1':
				return 'Dialing in progress';
			case '2':
				return 'live (call in progress)';
			case '3':
				return 'no answer';
			case '4':
				return 'busy';
			case '5':
				return 'transfer';
			case '6':
				return 'do not call';
			case '7':
				return 'error due to channel unavailable';
			case '8':
				return 'misc.';
			case '9':
				return 'Machine';
			case '10':
				return 'success';
			case '11':
				return 'error due to congestion';
			case '12':
				return 'error marked on stucked calls';
			default:
				return 'Ready for be called';
		}
	}
}]);