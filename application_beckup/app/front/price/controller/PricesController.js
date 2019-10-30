angular.module('frontCallburnApp').controller('PricesController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', 'callRoutes',
	function($scope,   $rootScope,   $state,    Restangular,   callRoutes){


	$scope.callRoutes = callRoutes.resource.routes;

	$scope.financialsStep = 1;
	var financialCalculator = $scope.financialCalculator = {step: 5};

	$scope.getMaxCostforOneMessage = function(){
		var length = financialCalculator.length;
		if(length < 20){
			length = 20;
		}
		var finalPrice = length / 60 * financialCalculator.callRoute.custom_price;
		return finalPrice.toFixed(2);
	}

	$scope.getMaxCostForAllMessages = function(){
		var length = financialCalculator.length;
		if(length < 20){
			length = 20;
		}
		var quantity = financialCalculator.quantity;
		var finalPrice = length * quantity * financialCalculator.callRoute.custom_price / 60;
		return finalPrice.toFixed(2);
	}


}])