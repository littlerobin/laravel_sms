module.exports = ['$rootScope', function ($rootScope) {
    return {
        restrict: 'E',
        transclude: true,
        templateUrl: "/app/directives/templates/callbourn-modal.html",
        controller: ['$scope', function ($scope) {
            $scope.hideOverlay = function () {
                $scope.isActive = false;
                $rootScope.showBlurEffect = false;
            }

            $scope.$watch('isActive', function (n, o) {
                if (n) {
                    angular.element('body').addClass('body-no-scroll');
                }

                else {
                    angular.element('body').removeClass('body-no-scroll');
                }
            })
        }]
    }
}];