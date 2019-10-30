angular.module('callburnApp').controller('FinancialsController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', '$stateParams', 'callRoutes', 'taxData',
	function($scope,   $rootScope,   $state,    Restangular,   $stateParams,   callRoutes,   taxData){

	$scope.goToNotification = $rootScope.goToNotification;
	if($stateParams.oldData){
		$scope.paymentData = {};
		$scope.paymentData.amount = $stateParams.oldData.amount;
		$scope.paymentData.vat_id = $stateParams.oldData.vat_id;
		$scope.paymentData.vat_amount = $stateParams.oldData.vat_amount;
		$scope.paymentData.discount_code = $stateParams.oldData.coupon_code;
	} else{
		$scope.paymentData = {amount: 0, vat_amount: 0, vat_id: $rootScope.currentUser.vat};
	}
	
	$rootScope.currentActiveRoute = 'account';

	$rootScope.currentPage = 'dashboard';
	$scope.taxData = taxData.resource.taxData;

	$scope.discountData = {};
	var notChangedStandardRate = taxData.resource.taxData.standard_rate;
	var lastCheckedVatId = null;
	var lastCheckedCoupon = null;

	if($rootScope.currentUser.is_autobilling_active){
		var firstText = 'Autorecharge on Low Balance is enabled';
		var secondText = 'You will be always on!';
	} else{
		var firstText = 'Autorecharge on Low Balance is disabled';
		var secondText = 'Enable it to stay always on!';
	}
	$rootScope.footerData = {
		first:  '<span ng-click="openChatWindow()">Want support</span>' + 
				'<span ng-click="openChatWindow()">Leave us a chat message</span>',
		second: '<span>' + firstText + '</span>' + 
				'<span>' + secondText + '</span>',
		third:  '<a href="/#/docs" target="_blank"><span>Read our docs</span>' + 
				'<span>They can help you really much</span></a>'
	}

	var updateVat = function(newVal){
		$scope.paymentData.vat_amount = newVal ? newVal * $scope.taxData.standard_rate / 100 : 0 ;
		$scope.paymentData.total_amount = Number( $scope.paymentData.vat_amount ) + Number( $scope.paymentData.amount ) ;
	}

	$scope.$watch('paymentData.amount', function(newVal){
		updateVat(newVal);
	});

	$scope.callRoutes = callRoutes.resource.routes;

	$scope.financialsStep = 1;
	var financialCalculator = $scope.financialCalculator = {step: 5};

	$scope.getMaxCostforOneMessage = function(){
		var length = financialCalculator.length;
		if(length < 20){
			length = 20;
		}
		var finalPrice =  length / 60 * financialCalculator.callRoute.custom_price;
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

	$scope.applyCalculatedCost = function(){
		if(financialCalculator.callRoute){
			$scope.paymentData.amount = $scope.getMaxCostForAllMessages();
		}
	}

	
	$scope.checkVat = function(){
		$rootScope.startLoader();
		Restangular.all('billings/check-vat-id').post($scope.paymentData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				$scope.taxData.standard_rate = 0;
				lastCheckedVatId = $scope.paymentData.vat_id;
				updateVat($scope.paymentData.amount);
			}
		})
	}

	$scope.$watch('paymentData.vat_id', function(newVal){
		if(newVal !== lastCheckedVatId){
			$scope.taxData.standard_rate = notChangedStandardRate;
		} else{
			$scope.taxData.standard_rate = 0;
		}
		updateVat($scope.paymentData.amount);
	})

	$scope.$watch('paymentData.discount_code', function(newVal){
		if(lastCheckedCoupon && newVal !== lastCheckedCoupon.code){
			$scope.discountData = {};
		} else{
			$scope.discountData = lastCheckedCoupon;
		}
	})

	$scope.checkCouponCode = function(){
		$rootScope.startLoader();
		Restangular.all('billings/check-coupon-code').post($scope.paymentData).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				lastCheckedCoupon = data.resource.coupon;
				$scope.discountData =  data.resource.coupon;
			}
		})
	}

	$scope.createPreorder = function(){
		$rootScope.startLoader();
		Restangular.all('billings/create-preorder').post($scope.paymentData).then(function(data){
			$rootScope.stopLoader();
			$state.go('account.checkout', {invoice_id: data.resource.invoice._id})
		})
	}

	var reloadUsersData = function(){
		Restangular.one('users/show-user').get().then(function(data){
			if(data.resource.user_data){
				$rootScope.currentUser = data.resource.user_data;
			}
		})
	}

	$scope.autoRecharge = {autobilling_amount: 5};
	$scope.enableAutoRecharge = function(){
		$rootScope.startLoader();
		Restangular.all('billings/activate-automatic-billing').post($scope.autoRecharge).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				window.location = data.resource.redirect_url;
			}
		})
	}

	$scope.disableAutoRecharge = function(){
		$rootScope.startLoader();
		Restangular.all('billings/cancel-automatic-billing').post().then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				reloadUsersData()
			}
		})
	}

	$scope.notifyWhenBalanceIsLow = $rootScope.currentUser.notify_when_balance_is_low;
	$scope.enableDisableLowBalanceNotifications = function(){
		var isEnabled = true;
		if(!$scope.notifyWhenBalanceIsLow){
			isEnabled = false;
		}
		$rootScope.startLoader();
		Restangular.all('users/enable-disable-low-balance-alerts').post({ 
			send_low_balance_notifications: isEnabled, 
			notify_when_balance_is_low: $scope.notifyWhenBalanceIsLow})
		.then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				reloadUsersData();
			}
		})
	}

	$scope.autorechargeAmountChanged = function(autorechargeWith){
		$rootScope.startLoader();
		Restangular.all('billings/set-auto-recharge-amount').post({autorecharge_with: autorechargeWith}).then(function(data){
			$rootScope.stopLoader();
			if(data.resource.error.no == 0){
				reloadUsersData();
			}
		});
	}


}]);