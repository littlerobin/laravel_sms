angular.module('frontCallburnApp').controller('InvitationController', [
    '$scope',
    '$rootScope',
    '$location',
    'Restangular',
    'UserFactory',
    function (
        $scope,
        $rootScope,
        $location,
        Restangular,
        UserFactory
    ) {
        $scope.getLocaleDateString = UserFactory.getLocaleDateString();
        $scope.registrationData = {};
        $scope.email = '';
        $scope.wrongCredentials = false;
        $scope.errorMessage = '';
        $scope.wrongBorder = '';
        $scope.userAlreadyRegistered = false;
        $scope.somethingWentWrong = false;
        $scope.accountDeactivated = false;

        function init() {
            Restangular.one('invitations/invitation').get().then(function(data) {
                $scope.invitation = data.resource;
                if (!$scope.invitation) {
                    $scope.somethingWentWrong = true;
                    $scope.wrongCredentials = false;
                    $scope.userAlreadyRegistered = false;
                    $scope.wrongBorder = '';
                } else if ($scope.invitation.lead) {
                    $scope.email = $scope.invitation.lead.email;
                } else if ($scope.invitation.customer) {
                    $scope.email = $scope.invitation.customer.email;
                }
            });
        }

        $scope.registration = function () {
            Restangular.all('invitations/'+ $scope.invitation.token +'/register')
            .post($scope.registrationData)
            .then(function (data) {
                $scope.wrongBorder = '';
                $scope.wrongCredentials = false;
                $scope.errorMessage = '';
                dataLayer.push({
                  'email': $scope.registrationData.email,
                  'event': 'sign_up'
                });
                localStorage.setItem("jwtToken", googleWindow.jwtToken);
                window.location.assign('/myaccount');
            }, function (response) {
                if (response.status == 400) {
                    $scope.animate = getAnimation();
                    $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
                    $scope.wrongCredentials = true;
                    $scope.errorMessage = response.data.message;
                    $scope.userAlreadyRegistered = false;
                    $scope.somethingWentWrong = false;
                } else if (response.status == 409) {
                    $scope.userAlreadyRegistered = true;
                    $scope.somethingWentWrong = false;
                    $scope.wrongCredentials = false;
                    $scope.wrongBorder = '';
                } else {
                    $scope.somethingWentWrong = true;
                    $scope.wrongCredentials = false;
                    $scope.userAlreadyRegistered = false;
                    $scope.wrongBorder = '';
                }
            });
        }

        $scope.loginFacebook = function (event) {
            var url = window.location.origin + '/social/facebook-login?local_date_format=' + UserFactory.getLocaleDateString();
            if ($scope.registrationData.phonenumber && $scope.registrationData.voice_code) {
                url += '?phonenumber=' + $scope.registrationData.phonenumber + '&voice_code=' + $scope.registrationData.voice_code;
            }
            var width = 800;
            var height = 800;
            var left = (screen.width / 2) - (width / 2);
            var top = (screen.height / 2) - (height / 2);
            var facebookWindow = window.open(url, 'Connect facebook account', 'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left);
            var interval = setInterval(function () {
                try {
                    if (facebookWindow.success == 'success') {
                        clearInterval(interval);
                        if (facebookWindow.is_registration) {
                            dataLayer.push({
                              'email': $scope.registrationData.email,
                              'event': 'sign_up'
                            });
                            localStorage.setItem("jwtToken", googleWindow.jwtToken);
                            window.location.assign('/myaccount');
                        } else {
                            localStorage.setItem("jwtToken", facebookWindow.jwtToken);
                            localStorage.setItem("justLoggedIn", 1);
                            window.location.assign ('/myaccount');
                        }
                        window.location.assign ('/myaccount');
                    } else if(facebookWindow.success == 'deactivated') {
                        $scope.accountDeactivated = true;
                        $scope.showInvalidLogin = true;
                        $scope.$apply();
                    } else if (facebookWindow.success == 'error') {
                        $scope.errors = [['Validator failed . Please contact support.']];
                    }
                } catch (err) {
                    console.log(err)
                }
            }, 1000)
        }

        $scope.loginGoogle = function (event) {
            var url = window.location.origin + '/social/google-login?local_date_format=' + UserFactory.getLocaleDateString();

            var width = 800;
            var height = 800;
            var left = (screen.width / 2) - (width / 2);
            var top = (screen.height / 2) - (height / 2);
            var googleWindow = window.open(url, 'Connect google account', 'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left);
            var interval = setInterval(function () {
                try {
                    if (googleWindow.success == 'success') {
                        clearInterval(interval);
                        if (googleWindow.is_registration) {
                            dataLayer.push({
                              'email': $scope.registrationData.email,
                              'event': 'sign_up'
                            });
                            localStorage.setItem("jwtToken", googleWindow.jwtToken);
                            window.location.assign('/myaccount');
                        } else {
                            localStorage.setItem("jwtToken", googleWindow.jwtToken);
                            localStorage.setItem("justLoggedIn", 1);
                            window.location.assign ('/myaccount');
                        }
                    } else if(googleWindow.success == 'deactivated') {
                        $scope.accountDeactivated = true;
                        $scope.showInvalidLogin = true;
                        $scope.$apply();
                    } else if (googleWindow.success == 'error') {
                        $scope.errors = [['Validator failed . Please contact support.']];
                    }
                } catch (err) {
                }
            }, 1000);
        }

        $scope.loginGitHub = function (event) {
            var url = window.location.origin + '/social/github-login?local_date_format=' + UserFactory.getLocaleDateString();

            var width = 800;
            var height = 800;
            var left = (screen.width / 2) - (width / 2);
            var top = (screen.height / 2) - (height / 2);
            var githubLogin = window.open(url, 'Connect google account', 'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left);
            var interval = setInterval(function () {
                try {
                    if (githubLogin.success == 'success') {
                        clearInterval(interval);

                        if(githubLogin.is_registration) {
                            dataLayer.push({
                              'email': $scope.registrationData.email,
                              'event': 'sign_up'
                            });
                            localStorage.setItem("jwtToken", googleWindow.jwtToken);
                            window.location.assign('/myaccount');
                        } else {
                            localStorage.setItem("jwtToken", githubLogin.jwtToken);
                            localStorage.setItem("justLoggedIn", 1);
                            window.location.assign ('/myaccount');
                        }
                    } else if(githubLogin.success == 'deactivated') {
                        $scope.accountDeactivated = true;
                        $scope.showInvalidLogin = true;
                        $scope.$apply();
                    } else if (githubLogin.success == 'error') {
                        $scope.errors = [['Validator failed . Please contact support.']];
                    }
                } catch (err) {
                }
            }, 1000);
        }

        init();
    }
]);
