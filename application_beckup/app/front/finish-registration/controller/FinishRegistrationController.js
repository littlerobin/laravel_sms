angular.module('frontCallburnApp').controller('FinishRegistrationController', 
	[ 		 '$scope', 'Restangular', '$stateParams',
	 function($scope,  Restangular,   $stateParams){


	$scope.finishRegistrationData = {email_confirmation_token: $stateParams.token};
	$scope.regVerificationStep = 1;
	$scope.regVerificationStepError = false;

	$scope.sendVerificationCallReg = function(){
		var postData = {
			phonenumber: $scope.finishRegistrationData.phonenumber,
			action: 'registration'
		}
		Restangular.all('users/send-verification-code').post(postData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.regVerificationStepError = false;
				$scope.regVerificationStep = 2;
				$scope.finishRegistrationData.phonenumber = data.resource.phonenumber;
			} else{
				$scope.regVerificationStepError = data.resource.error.text;
			}
		});
	}

	$scope.validateVoiceCodeReg = function(){
		Restangular.all('users/check-voice-code-validation').post($scope.finishRegistrationData).then(function(data){
			if(data.resource.error.no == 0){
				$scope.regVerificationStep = 3;
				$scope.regVerificationStepError = false;
			} else{
				$scope.regVerificationStepError = data.resource.error.text;
			}
		});
	}

	$scope.submitSaving = function(){
		Restangular.all('users/activate-account').post($scope.finishRegistrationData).then(function(data){
			if(data.resource.error.no == 0){
				alert('done');
			} else{
				$scope.regVerificationStepError = data.resource.error.text;
			}
		});
	}

}]);