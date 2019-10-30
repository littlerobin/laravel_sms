angular.module('callburnApp').controller('InvoicesController', 
	[ 		'$scope', '$rootScope', '$state',  'Restangular', '$stateParams', 'invoices', 'orders',
	function($scope,   $rootScope,   $state,    Restangular,   $stateParams,   invoices,   orders){

	$scope.goToNotification = $rootScope.goToNotification;

	$rootScope.currentPage = 'dashboard';
	$rootScope.currentActiveRoute = 'account';
	$scope.filterData = {};

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

	var updateInvoices = function(invoices){
		$scope.invoices = invoices.resource.invoices;
		$scope.invoicesPage = invoices.resource.page;
		$scope.invoicesPagesCount = Math.ceil(invoices.resource.count/5);
	}

	var updateOrders = function(orders){
		$scope.orders = orders.resource.invoices;
		$scope.ordersPage = orders.resource.page;
		$scope.ordersPagesCount = Math.ceil(orders.resource.count/5);
	}

	updateInvoices( invoices );
	updateOrders( orders );

	$scope.changeInvoicesPage = function(page){
		if(page < 0 || page > $scope.invoicesPagesCount){
			return;
		}
		var postData = $scope.filterData;
		postData.invoices_page = page;
		Restangular.one('billings/invoices').get(postData).then(function(data){
			updateInvoices(data);
		});
	}

	$scope.changeOrdersPage = function(page){
		if(page < 0 || page > $scope.ordersPagesCount || page == $scope.ordersPage - 1){
			return;
		}
		var postData = $scope.filterData;
		postData.orders_page = page;
		Restangular.one('billings/orders').get(postData).then(function(data){
			updateOrders(data);
		});
	}


}]);