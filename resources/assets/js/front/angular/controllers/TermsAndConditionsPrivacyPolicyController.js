angular.module('frontCallburnApp').controller('TermsAndConditionsPrivacyPolicyController',
	['$scope', '$rootScope', '$document','$location',
	function ($scope, $rootScope, $document, $location) {

		var param = $location.search();

		var tab = parseInt(param.tab);
		switch (tab) {
			case 1 :
				$scope.termsActiveNav = 'active show';
				$scope.privacyActiveNav = 'fade show';
				$scope.termsActive = 'show active';
				$scope.privacyActive = 'fade';
				break;
			case 2 :
				$scope.termsActiveNav = 'fade show';
				$scope.privacyActiveNav = 'active show';
				$scope.termsActive = 'fade';
				$scope.privacyActive = 'show active';
				break;
			default :
				$scope.termsActiveNav = 'active show';
				$scope.privacyActiveNav = 'fade show';
				$scope.termsActive = 'show active';
				$scope.privacyActive = 'fade';
		}

}]);