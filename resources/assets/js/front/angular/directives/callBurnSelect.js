//

angular.module('frontCallburnApp').directive('callburnSelect', function ($document, $filter) {
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
                keepObject: "=keepObject",
                optionImage: "@optionImage",
                showAutocomplete: "=showAutocomplete",

            },
            link: function ($scope, elem, attr, ctrl) {

                var copyImageUrl = $scope.imageUrl;

                var ids = [
                    'selectBoxMain', 'data-show-right',
                    'data-show-right1', 'callburn-autocomplete',
                    'show-selecct-icon'
                ];
                $scope.filtering = {};
                $scope.showSelect = false;

                $document.on('click', function ($event) {
                    if (ids.indexOf($event.target.id) == -1) {
                        $scope.showSelect = false;
                        $scope.$apply();

                    }
                });
                $scope.valueSelected = function (showKey, key, img, index) {

                    $scope.showSelect = false;

                    $scope.languageSelected = [];
                    $scope.languageSelected[index] = 'language-selected';
                    $scope.selectedValue = $scope.keepObject ? showKey[$scope.showAttr] : showKey;
                    ctrl.$setViewValue(key);

                    if(img) {

                        $scope.imageUrl = img ;
                    }
                    else if ($scope.imageAttr && $scope.imageAttr[index]) {
                        $scope.imageUrl = $scope.imageAttr[index];
                    } else {
                        $scope.imageUrl = copyImageUrl;
                    }

                    //$filter('orderBy')($scope.options,  $scope.selectedValue);
                    //console.log($filter('orderBy')($scope.options,  'viewText'));
                }

            },
            // compile: function(element, attrs){
            //     if (!attrs.showAutocomplete) {
            //         attrs.showAutocomplete = false;
            //     }
            //
            // },
        }
    });