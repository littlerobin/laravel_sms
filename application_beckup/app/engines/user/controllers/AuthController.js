angular.module('callburnApp').controller('AuthController', 
	[ 		'$scope', '$rootScope', '$state', 'Restangular', 'routes',
	function($scope,   $rootScope,   $state,   Restangular,   routes){

	if(routes){
		$scope.callRoutes = routes.resource.routes;
	}
	
	$scope.verificationCode = {};
	$scope.verificationCall = {};
	$scope.verificationData = {};
	$scope.registrationStep = 0;
	$rootScope.currentPage = 'log-reg';

	$scope.loginData = {};
	$scope.loginErrorMessage = false;

	$scope.registrationData = {};
	$scope.registrationErrorMessage = false;

	$scope.login = function(){
		Restangular.all('users/login').post($scope.loginData).then(function(data){
			if(data.resource.error.no == 0){
				$rootScope.currentUser = data.resource.user_data;
				$rootScope.currentUser.api_token = data.resource.api_key;
				$state.go('dashboard.dashboard');
			} else{
				$scope.loginErrorMessage = trans(data.resource.error.text);
			}
		});
	}

	$scope.sendVerificationCall = function(){
		$scope.verificationCall.action = 'registration';
		$scope.verificationCall.phonenumber = $scope.verificationData.phonenumberCode + $scope.verificationData.phonenumber;
		$scope.registrationData.phonenumber = $scope.verificationData.phonenumberCode + $scope.verificationData.phonenumber;
		Restangular.all('users/send-verification-code').post($scope.verificationCall).then(function(data){
			if(data.resource.error.no == 0){
				$scope.registrationStep = 1;
				$scope.registrationErrorMessage = false;
			} else{
				$scope.registrationErrorMessage = trans(data.resource.error.text);
			}
		});
	}

	$scope.makeRegistration = function(){
		$scope.registrationData.voice_code = $scope.verificationCode.firstLetter + $scope.verificationCode.secondLetter + $scope.verificationCode.thirdLetter + $scope.verificationCode.fourthLetter;
		Restangular.all('users/registration').post($scope.registrationData).then(function(data){
			if(data.resource.error.no == 0){
				$state.go('login');
				$scope.registrationErrorMessage = false;
			} else{
				$scope.registrationErrorMessage = trans(data.resource.error.text);
			}
		})
	}

}]);