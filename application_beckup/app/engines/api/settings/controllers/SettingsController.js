angular.module('callburnApp').controller('SettingsController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', '$stateParams', 'apiKeys',
	function($scope,   $rootScope,   $state,    Restangular,   $stateParams,   apiKeys){


	$scope.goToNotification = $rootScope.goToNotification;
	$rootScope.currentPage = 'dashboard';
	$rootScope.currentActiveRoute = 'api';
	$scope.apiKeys = apiKeys.resource.api_keys;
	$scope.newKeydata = {};

	$scope.removeKey = function(id){
		Restangular.one('api-keys/remove-api-key', id).remove().then(function(data){
			updateKeys();
		});
	}

	$scope.addKey = function(){
		Restangular.all('api-keys/create-api-key').post($scope.newKeydata).then(function(data){
			updateKeys();
			$scope.newKeydata = {};
		})
	}

	var updateKeys = function(){
		Restangular.one('api-keys/api-keys').get().then(function(apiKeys){
			$scope.apiKeys = apiKeys.resource.api_keys;
		});
	}


}]);