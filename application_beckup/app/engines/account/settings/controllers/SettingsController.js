angular.module('callburnApp').controller('SettingsController',
    ['$scope', '$rootScope', '$state', 'Restangular', '$stateParams', 'timezones', 'countries', 'SettingsService',
        function ($scope, $rootScope, $state, Restangular, $stateParams, timezones, countries, SettingsService) {


            $scope.goToNotification = $rootScope.goToNotification;
            $rootScope.currentPage = 'dashboard';
            $rootScope.currentActiveRoute = 'account';
            $scope.showCallerIdInput = [];
            $scope.editAccountMainSuccessMessage = false;
            $scope.editAccountMainErrorMessage = false;
            console.log($rootScope.languages);

            $rootScope.footerData = {
                first: '<span ng-click="openChatWindow()">Want support</span>' +
                '<span ng-click="openChatWindow()">Leave us a chat message</span>',
                second: '<span>You have got a total of</span>' +
                '<span>' + $rootScope.currentUser.numbers.length + ' registered Caller ID</span>',
                third: '<a href="/#/docs" target="_blank"><span>Read our docs</span>' +
                '<span>They can help you really much</span></a>'
            }

            /*
             |---------------------------------------------------------
             | Edit users email.
             |---------------------------------------------------------
             | Open/close email update modal .
             | Submit for changing email
             |
             */
            $scope.showChangeEmailModal = false;
            $scope.updateEmailErrorMessage = false;
            $scope.editEmailData = {};

            $scope.editEmail = function () {
                $rootScope.startLoader();
                SettingsService.updateEmail($scope.editEmailData).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $scope.showChangeEmailModal = false;
                        $scope.editAccountMainSuccessMessage = 'Email has been successfully changed';
                        $scope.editEmailData = {};
                    } else {
                        $scope.updateEmailErrorMessage = trans(data.resource.error.text);
                    }
                });
            }

            /*
             |---------------------------------------------------------
             | Edit users password.
             |---------------------------------------------------------
             | Open/close password update modal .
             | Submit for changing password
             |
             */
            $scope.updatePasswordErrorMessage = false;
            $scope.showEditPasswordModal = false;
            $scope.editPasswordData = {};
            $scope.editPassword = function () {
                $rootScope.startLoader();
                SettingsService.updatePassword($scope.editPasswordData).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $scope.showEditPasswordModal = false;
                        $scope.editAccountMainSuccessMessage = 'Password has been successfully changed';
                        $scope.editPasswordData = {};
                    } else {
                        $scope.updatePasswordErrorMessage = trans(data.resource.error.text);
                    }
                });
            }

            /*
             |---------------------------------------------------------
             | Manage caller ids
             |---------------------------------------------------------
             | Add/remove caller ids .
             |
             */
            $scope.verificationCall = {};
            $scope.showVerificationCodeFilde = false;
            $scope.verificationErrorMessage = false;
            $scope.verificationModalErrorMessage = false;
            $scope.sendVerificationCall = function () {
                $rootScope.startLoader();
                SettingsService.sendVerificationCode($scope.verificationCall).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $scope.showVerificationModal = true;
                    } else {
                        $scope.verificationErrorMessage = trans(data.resource.error.text);
                    }
                });
            }

            $scope.checkVerificationCode = function () {
                $rootScope.startLoader();
                SettingsService.addCallerId($scope.verificationCall).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $scope.reloadUsersData();
                        $scope.verificationCall = {};
                        $scope.showVerificationModal = false;
                    } else {
                        $scope.verificationModalErrorMessage = trans(data.resource.error.text);
                    }
                })
            }

            $scope.removeNumber = function (id) {
                $rootScope.startLoader();
                SettingsService.removeNumber({id: id}).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $scope.reloadUsersData();
                    } else {

                    }
                });
            }

            /*
             |---------------------------------------------------------
             | MAIN DATA
             |---------------------------------------------------------
             | Manage users main data.
             | Manage users data for invoice
             |
             */

            $scope.timezones = timezones.resource.timezones;
            $scope.countries = countries.resource.countries;
            $scope.cities = {};

            var getCities = function (countryCode) {
                SettingsService.getCites({country_code: countryCode}).then(function (data) {
                    $scope.cities = data.resource.cities;
                })
            }

            if ($scope.currentUser.country_code) {
                getCities($scope.currentUser.country_code);
            }

            $scope.countryChanged = function () {
                if ($scope.userEditableData.country_code) {
                    console.log($scope.userEditableData.country_code);
                    getCities($scope.userEditableData.country_code);
                }
                $scope.editMainDetails();
            }

            $scope.userEditableData = {
                first_name: $rootScope.currentUser.first_name,
                vat: $rootScope.currentUser.vat,
                address: $rootScope.currentUser.address,
                timezone: $rootScope.currentUser.timezone,
                language_id: $rootScope.currentUser.language_id,
                country_code: $rootScope.currentUser.country_code,
                postal_code: $rootScope.currentUser.postal_code,
                city_id: $rootScope.currentUser.city_id ? $rootScope.currentUser.city_id : null,
            }

            $scope.editMainDetails = function () {
                $rootScope.startLoader();
                SettingsService.updateMainData($scope.userEditableData).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $rootScope.languages.forEach(function (lang) {
                            if (lang._id == $scope.userEditableData.language_id) {
                                $rootScope.currentLanguage = lang.code;
                                $rootScope.currentLanguageName = lang.name;
                            }
                        });
                        $scope.editAccountMainSuccessMessage = 'Account data updated';
                    } else {
                        $scope.editAccountMainErrorMessage = trans(data.resource.error.text);
                    }
                })
            }

            $scope.updateNewsletterSubscription = function () {
                var postData = {};
                postData.send_newsletter = !$rootScope.currentUser.send_newsletter;
                $rootScope.startLoader();
                SettingsService.updateMainData(postData).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        $scope.reloadUsersData();
                        $scope.editAccountMainSuccessMessage = 'Account data updated';
                    } else {
                        $scope.editAccountMainErrorMessage = trans(data.resource.error.text);
                    }
                })
            }

            $scope.reloadUsersData = function () {
                SettingsService.getShowUser().then(function (data) {
                    if (data.resource.user_data) {
                        $rootScope.currentUser = data.resource.user_data;
                    }
                })
            }

            $scope.removeToken = function (id) {
                SettingsService.removeById(id).then(function (data) {
                    $scope.reloadUsersData();
                });
            }

            $scope.changeCallerIdName = function (id, name) {
                $rootScope.startLoader();
                SettingsService.updateCallerId({name: name}).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        for (index in $scope.currentUser.numbers) {
                            if ($scope.currentUser.numbers[index]._id == id) {
                                $scope.currentUser.numbers[index].name = name;
                                $scope.showCallerIdInput[id] = false;
                                break;
                            }
                        }
                    }
                })
            }

            var arrayOfModals = [
                'showChangeEmailModal',
                'showEditPasswordModal',
                'showVerificationModal'
            ];
            $scope.$watchGroup(arrayOfModals, function (newValues, oldValues, scope) {
                var needBlur = false;
                for (index in newValues) {
                    if (newValues[index]) {
                        needBlur = true;
                        break;
                    }
                }
                $rootScope.showBlurEffect = needBlur;
            });

        }]);