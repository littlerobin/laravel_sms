module.exports =
    ['$document', function ($document) {
        return {
            restrict: 'EA',
            require: 'ngModel',
            templateUrl: '/app/directives/templates/callburn-select.html',
            scope: {
                options: '=',
                showAttr: '@showAttr',
                keepAttr: '@keepAttr',
                imageUrl: '@imageUrl',
                imageAttr: '=imageAttr',
                selectText: '@selectText',
                keepObject: "=keepObject"
            },
            link: function ($scope, elem, attr, ctrl) {
                var copyImageUrl = $scope.imageUrl;
                $scope.showSelect = false;
                $scope.selectedValue = false;
                $document.on('click', function ($event) {
                    if ($event.target.id != 'selectBoxMain') {
                        $scope.showSelect = false;
                        $scope.$apply();
                    }
                });
                $scope.valueSelected = function (showKey, key, index) {
                    $scope.showSelect = false;
                    $scope.selectedValue = $scope.keepObject ? showKey[$scope.showAttr] : showKey;
                    ctrl.$setViewValue(key);
                    if ($scope.imageAttr && $scope.imageAttr[index]) {
                        $scope.imageUrl = $scope.imageAttr[index];
                    } else {
                        $scope.imageUrl = copyImageUrl;
                    }

                }
            }
        }
    }];