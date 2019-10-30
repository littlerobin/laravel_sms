angular.module('callburnApp').controller('DashboardController', 
	[ 		'$scope', '$rootScope', '$state', 'dashboardData',
	function($scope,   $rootScope,   $state ,  dashboardData){

	$scope.goToNotification = $rootScope.goToNotification;
	$rootScope.currentPage = 'dashboard';
	$rootScope.currentActiveRoute = 'dashboard';
	$scope.dashboardData = dashboardData.resource;
	$scope.options1 = [{a:1, b:2}];
	$rootScope.footerData = {
		first:  '<span ng-click="openChatWindow()">Want support</span>' + 
				'<span ng-click="openChatWindow()">Leave us a chat message</span>',
		second: '<span>With us you have spent</span>' + 
				'<span>$ ' + $rootScope.currentUser.billed_amount + '</span>',
		third:  '<a href="/#/docs" target="_blank"><span>Read our docs</span>' + 
				'<span>They can help you really much</span></a>'
	}
}]);