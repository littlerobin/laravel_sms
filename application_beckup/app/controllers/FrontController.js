angular.module('frontCallburnApp').controller('FrontController',
	[ 		'$scope', '$rootScope', '$state', 'Restangular', '$http',
	function($scope,   $rootScope,   $state,   Restangular,   $http){


	// setTimeout(function(){
	// 	Intercom("boot", {
	//         app_id: "w07q90xq",
	//         widget: {
	//           activator: "#IntercomDefaultWidget"
	//         }
	//     });
	// }, 3000)
		//select default language
	var db = openDatabase('callbourn', '1.0', 'dbCall', 2 * 1024 * 1024);
		db.transaction(function (tx) {
			tx.executeSql('CREATE TABLE IF NOT EXISTS LANG (code varchar,name varchar)');
			tx.executeSql('SELECT * FROM LANG WHERE `rowid` = ?', [1], function (tx, results) {
				if(results.rows.length==0){
					tx.executeSql('INSERT INTO LANG (code, name) VALUES ("en", "ENG")',[],function (tx,results) {
						tx.executeSql('SELECT * FROM LANG WHERE `rowid` = ?', [1], function (tx, results) {
							$rootScope.currentLanguage=results.rows[0].code;
							$rootScope.currentLanguageName=results.rows[0].name;
						})
					});

				}else{
					$rootScope.currentLanguage=results.rows[0].code;
					$rootScope.currentLanguageName=results.rows[0].name;
				}
			}, null);

		});

	$scope.showPhoneMenu = false;
	$scope.phoneMenu = function(){
		angular.element('.menu_icon_line_container').toggleClass('menu_icon_line_container1')
		$scope.showPhoneMenu = !$scope.showPhoneMenu;		
	}



	$rootScope.currentLanguage = 'en';
	$rootScope.composeAction = 'compose';

	$rootScope.changeComposeAction = function(action){
		$rootScope.composeAction = action;
	}

	$scope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams){
		$rootScope.showLoading = true;
	})

	$scope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){ 
		$rootScope.showLoading = false;
	})

	$scope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams, error){
		$rootScope.showLoading = false;
	})

	$scope.showRegistrationModal = false;
	$scope.showLoginModal = false;
	$scope.showRecoveryModal = false;
	$scope.loginErrorMessage = false;
	$scope.showPrivacyModal = false;
	$scope.showConditionsModal = false;


	$scope.PrivacyModal = function(){
		$scope.showPrivacyModal = true;
	}

	$scope.ConditionsModal = function(){
		$scope.showConditionsModal = true;
	}

	$scope.loginErrorMessage = function(){
		$scope.showInvalidLogin = false;
	}

	$scope.login = function(){
		Restangular.all('users/login').post($scope.loginData).then(function(data){
			if(data.resource.error.no == 0){
				window.location.assign(appUrl + data.resource.api_key + "/#/dashboard/dashboard");
			} else{
				$scope.showInvalidLogin = true;
			}
		});
	}

	$scope.registrationData = {};
	$scope.verificationStep = 1;

	$scope.sendVerificationCall = function(){
		var postData = {
			phonenumber: $scope.registrationData.phonenumber,
			action: 'registration'
		}
		Restangular.all('users/send-verification-code').post(postData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.verificationStep = 2;
				$scope.registrationData.phonenumber = data.resource.phonenumber;
			} else{
				$scope.verificationErrorMessage = trans(data.resource.error.text);
			}
		});
	}

	$scope.validateVoiceCode = function(){
		Restangular.all('users/check-voice-code-validation').post($scope.registrationData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.verificationStep = 3;
			} else{
				$scope.verificationErrorMessage = trans(data.resource.error.text);
			}
		});
	}

	$scope.checkEmail = false;
	
	$scope.registration = function(){
		Restangular.all('users/registration').post($scope.registrationData).then(function(data){
			$scope.showRegistrationModal = false;
			$scope.showLoginModal = true;
			$scope.checkEmail = true;
		});	
	}

	$scope.loginFacebook = function(event){
		var url = '/social/facebook-login';
		if($scope.registrationData.phonenumber && $scope.registrationData.voice_code){
			url += '?phonenumber=' + $scope.registrationData.phonenumber + '&voice_code=' + $scope.registrationData.voice_code;
		}
		var width = 800;
		var height = 800;
		var left=(screen.width/2) - (width/2);
		var top=(screen.height/2) - (height/2);
		var facebookWindow = window.open(url , 'Connect facebook account', 'height='+height+',width='+width+',top='+top+',left='+left);
		var interval = setInterval(function(){
			try{
				if(facebookWindow.success == 'success'){
					clearInterval(interval);
					window.location.assign(appUrl  + facebookWindow.api_key + "/#/dashboard/dashboard");
				}
				if(facebookWindow.success == 'error'){
					$scope.errors = [['This account is not connected to facebook']];
				}
			} catch(err){}},1000)
						
	}

	$scope.loginGoogle = function(event){
		var url = 'social/google-login';
		if($scope.registrationData.phonenumber && $scope.registrationData.voice_code){
			url += '?phonenumber=' + $scope.registrationData.phonenumber + '&voice_code=' + $scope.registrationData.voice_code;
		}
		var width = 800;
		var height = 800;
		var left = (screen.width/2) - (width/2);
		var top = (screen.height/2) - (height/2);
		var googleWindow = window.open(url , 'Connect google account', 'height='+height+',width='+width+',top='+top+',left='+left);
		var interval = setInterval(function(){
			try{
				if(googleWindow.success == 'success'){
					clearInterval(interval);
					window.location.assign(appUrl + googleWindow.api_key + "/#/dashboard/dashboard");
				}
				if(googleWindow.success == 'error'){
					$scope.errors = [['This account is not connected to facebook']];
				}
			} catch(err){}},1000)
	}



	$rootScope.languages = {};
	Restangular.one('data/languages').get().then(function(data){
		$rootScope.flags=[];
		$rootScope.languages = data.resource.languages;
		$rootScope.languages.forEach(function (language) {
			$rootScope.flags.push("/assets/callburn/images/flags/"+language.code+".png");

		});
	})



	$scope.showLanguageSelect = false;
	$scope.hideLanguage = function(){
		$scope.showLanguageSelect = false;
	}

	$scope.showHideLanguageBar = function(){
		$scope.showLanguageSelect = !$scope.showLanguageSelect;
	}

	var translate = {};
	var langRequestInProcess = false;
	$rootScope.trans = function(str){
		var lang = $rootScope.currentLanguage ? $rootScope.currentLanguage : 'en';
		if(!translate[lang]){
			if(!langRequestInProcess){
				langRequestInProcess = true;
				$http.get('translations/front_translate_' + lang + '.json').success(function(data){
					translate[lang] = data;
					langRequestInProcess = false;
				});
			}
		} else{
			return translate[lang][str] ? translate[lang][str] : str;
		}
	}

	$scope.changeLanguage = function(lang){
		$rootScope.currentLanguage = lang;
		$rootScope.languages.forEach(function (item) {
			if(item.code == lang){
				db.transaction(function (tx) {
					tx.executeSql('UPDATE LANG SET code=?,name=? WHERE `rowid`=1',[lang,item.name]);
				});
			}
		});

	}

}]);