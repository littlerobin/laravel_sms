angular.module('frontCallburnApp').controller('HomePageController', 
	[ 		 '$scope', '$rootScope', '$state',  'Restangular', '$stateParams',
	 function($scope,   $rootScope,   $state,    Restangular,   $stateParams){


	$scope.sliderStep = 1;
	$scope.messageSliderStep = 1;

	// setInterval(function(){
	// 	if($scope.sliderStep == 8){
	// 		$scope.sliderStep = 1;
	// 	} else{
	// 		$scope.sliderStep++;
	// 	}
	// 	$scope.$apply();
	// }, 5000);

	// setInterval(function(){
	// 	if($scope.messageSliderStep == 7){
	// 		$scope.messageSliderStep = 1;
	// 	} else{
	// 		$scope.messageSliderStep++;
	// 	}
	// 	$scope.$apply();
	// }, 5000);

	$scope.nextSlide = function(){
		if($scope.sliderStep == 10){
			$scope.sliderStep = 1;
		} else{
			$scope.sliderStep++;
		}
	}

	$scope.prevSlide = function(){
		if($scope.sliderStep == 1){
			$scope.sliderStep = 10;
		} else{
			$scope.sliderStep--;
		}
	}

	$scope.goToSlide = function(step){
		$scope.sliderStep = step;
	}

	$scope.goToMessageSlide = function(step){
		$scope.messageSliderStep = step;
	}


	$scope.showRegModal = $stateParams.token ? true : false;
	$scope.finishRegistrationData = {email_confirmation_token: $stateParams.token};
	$scope.regVerificationStep = 1;
	$scope.phonenumberValidationError = false;
	$scope.regVerificationStepError = false;

	$scope.sendVerificationCallReg = function(){
		var postData = {
			phonenumber: $scope.finishRegistrationData.phonenumber,
			action: 'registration'
		}
		Restangular.all('users/send-verification-code').post(postData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.phonenumberValidationError = false;
				$scope.regVerificationStep = 2;
				$scope.finishRegistrationData.phonenumber = data.resource.phonenumber;
			} else{
				$scope.phonenumberValidationError = true;
			}
		});
	}

	$scope.validateVoiceCodeReg = function(){
		Restangular.all('users/check-voice-code-validation').post($scope.finishRegistrationData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.regVerificationStep = 3;
				$scope.regVerificationStepError = false;
			} else{
				$scope.regVerificationStepError = true;
			}
		});
	}

	$scope.submitSaving = function(){
		Restangular.all('users/activate-account').post($scope.finishRegistrationData).then(function(data){
			if(data.resource.error.no == 0){
				window.location.assign(appUrl + data.resource.api_key + "/#/dashboard/dashboard");
			} else{
				$scope.regVerificationStepError = data.resource.error.text;
			}
		});
	}

}]);