angular.module('frontCallburnApp').controller('AuthenticationController', [
  '$scope',
  '$rootScope',
  'Restangular',
  'UserFactory',
  '$timeout',
  '$auth',
  'growl',
  '$location',
  '$document',
  '$interval',
  '$q',
  function($scope, $rootScope, Restangular, UserFactory, $timeout, $auth, growl, $location, $document, $interval, $q) {
    $scope.registrationData = {};
    $scope.borderStyle = {};
    $scope.currentCountry = {};
    $scope.verificationCall = {};
    $scope.regVerificationStep = 2;
    $scope.countryFilter = {};
    $scope.countryFilter.phonenumber_prefix = '';
    $scope.callRoutes = [];
    $scope.emailMessage = null;
    $scope.successOrWrong = '';
    $scope.wrongBorder = '';
    $scope.errorMessage = '';
    $scope.recoverUsernameStep = 1;
    $scope.username = null;
    $scope.finalyPhonenumber = null;
    $scope.wrongEmail = false;
    $scope.showInvalidLogin = false;
    $scope.wrongCredentials = false;
    $scope.disableButton = false;
    $scope.loginLoading = false;
    $scope.disableVerificationCodeButton = false;
    $scope.counter = 0;
    $scope.wrongVerificationCode = false;
    $scope.finishUsernameReset = true;
    $scope.finishUsernameResetCode = true;
    $scope.verificationCodeLoading = false;
    $scope.wrongVerificationCodeBorder = '';
    $scope.animate = '';
    $scope.disableCredentials = false;
    $scope.loginData = {};
    $scope.userAlreadyRegistered = false;
    $scope.accountDeactivated = false;
    $scope.disableRegistrationByEmailButton = true;
    $scope.resetPasswordErrorMessage = '';
    $scope.selectedMessageLength = 20;
    $scope.selectedSmsLength = 160;
    $scope.dotToCommaReg = '/./';
    $scope.disableSendPasswordResetLink = false;

    $scope.minLength = 20;
    $scope.maxLength = 600;
    $scope.minSmsLength = 160;
    $scope.maxSmsLength = 1600;

    $scope.labelOpened = false;
    $scope.showPriceLabel = false;
    $scope.autoOpen = false;
    $scope.labelInit = function() {
      $timeout(function() {
        $scope.showPriceLabel = true;
      }, 500);
      $timeout(function() {
        $scope.autoOpen = true;
        $scope.labelOpened = true;
      }, 1500);
    };

    $scope.toggleLabel = function() {
      $scope.labelOpened = !$scope.labelOpened;
    };

    window.addEventListener('scroll', function() {
      // if (window.scrollY >= 550) {
      //     $scope.labelOpened = false;
      // } else {
      //     $scope.labelOpened = true;
      // }
    });

    $scope.enableRegisterMail = function() {
      $scope.enableRegister = $scope.enableRegister ? false : true;
    };

    $scope.checkEnter = function($event, type, section) {
      var keyCode = $event.which || $event.keyCode;
      if (keyCode === 13) {
        if (type === 'login') {
          $scope.login();
        } else if (type === 'register') {
          if (!$scope.loginLoading) {
            if (!$scope.emailSent) {
              $scope.registration(section);
            }
          }
        }
      }
    };

    $rootScope.showRegHover = false;

    window.addEventListener('click', function(event) {
      var elem = angular.element(event.target);
      if (!elem.hasClass('reg_id')) {
        $rootScope.showRegHover = false;
        $scope.firstTime = false;
      }
    });
    $scope.fixedRegHover = function() {
      if (!$scope.firstTime) {
        $rootScope.showRegHover = true;
      }
    };

    $scope.firstTime = false;
    $scope.checkRegClick = function() {
      if (!$scope.firstTime) {
        $scope.firstTime = true;
        // $rootScope.showRegHover = true;
      } else {
        $rootScope.showRegHover = false;
        $scope.firstTime = false;
      }
    };

    // $rootScope.toggleRegHolder = function () {
    //     $rootScope.showRegHolder = true;
    // }

    $scope.goToReset = function() {
      if (sessionStorage.getItem('fromForgot')) {
        setTimeout(function() {
          angular.element('#recover_link').trigger('click');
        }, 10);
      }
    };

    $scope.messageValidator = function() {
      if ($scope.selectedMessageLength < $scope.minLength) {
        $scope.selectedMessageLength = 20;
      } else if ($scope.selectedMessageLength > $scope.maxLength) {
        $scope.selectedMessageLength = 600;
      }
    };

    $scope.smsValidator = function() {
      if ($scope.selectedSmsLength < $scope.minSmsLength) {
        $scope.selectedSmsLength = 160;
      } else if ($scope.selectedSmsLength > $scope.maxSmsLength) {
        $scope.selectedSmsLength = 1600;
      }
    };

    $scope.num = 1;
    $scope.calcSmsText = function() {
      var txt = $scope.selectedSmsLength;
      if (txt >= 0 && txt <= 160) {
        $scope.num = 1;
      } else if (txt >= 161 && txt <= 320) {
        $scope.num = 2;
      } else if (txt >= 321 && txt <= 480) {
        $scope.num = 3;
      } else if (txt >= 481 && txt <= 640) {
        $scope.num = 4;
      } else if (txt >= 641 && txt <= 800) {
        $scope.num = 5;
      } else if (txt >= 801 && txt <= 960) {
        $scope.num = 6;
      } else {
        return;
      }
    };

    var urlParams = JSON.parse(currentUrl);
    $scope.finishRegistrationData = { email_confirmation_token: urlParams[2], email: urlParams[3] };

    $scope.timer = function(count) {
      var queue = $q.defer();
      var timer = $interval(function() {
        $scope.counter = count;
        $scope.counter = count--;
        if (count < 0) {
          $interval.cancel(timer);
          queue.resolve();
        }
      }, 1000);

      return queue.promise;
    };

    var updateAnimate = ($scope.updateAnimate = function() {
      $scope.wrongBorder = $scope.wrongBorder.replace($scope.animate, '');
      $scope.wrongVerificationCodeBorder = $scope.wrongVerificationCodeBorder.replace($scope.animate, '');
    });

    var checkHtmlElement = function(selector) {
      var queue = $q.defer();

      var timer = $interval(function() {
        if (angular.element(selector).length) {
          $interval.cancel(timer);
          queue.resolve(angular.element(selector));
        }
      }, 300);

      return queue.promise;
    };

    $scope.addPrefix = function() {
      var elem = angular.element('#intel-input')[0];
      elem.value[0] === '+' || elem.value === ''
        ? null
        : (elem.value = '+ ' + sessionStorage.getItem('dial') + ' ' + elem.value);
    };

    checkHtmlElement('#intel-input').then(function(element) {
      element.on('countrychange', function(e, countryData) {
        element.value = sessionStorage.getItem('dial');
      });

      var countryData = angular.element('#intel-input').intlTelInput('getSelectedCountryData');
    });

    $scope.validVerificationCallData = true;
    $scope.validator = function() {
      if (angular.element('#intel-input').intlTelInput('isValidNumber')) {
        $scope.isValidNumberClass = 'input-success';
        $scope.validVerificationCallData = false;
      } else {
        $scope.isValidNumberClass = 'input-error';
        $scope.validVerificationCallData = true;
        if ($scope.finishRegistrationData.phonenumber === '') {
          $scope.isValidNumberClass = 'inp-placeholder';
        }
      }
    };

    Restangular.one('data/call-routes')
      .get()
      .then(function(data) {
        var countryCode = data.resource.countryCode;
        $scope.callRoutes = data.resource.routes;
        var first = $scope.callRoutes[0];
        $scope.callRoutes.forEach(function(item, i) {
          if (item.code == countryCode) {
            $scope.callRoutes[0] = item;
            $scope.callRoutes[i] = first;
            $scope.currentCountry.phonenumber_prefix = item.phonenumber_prefix;
            $scope.currentCountry.name = item.name;
            $scope.price = item.customer_price;
            $rootScope.prices = item.customer_price;
            $scope.smsPrice = item.sms_price;
            $rootScope.smsPrices = item.sms_price;
            return;
          }
        });

        if ($scope.currentCountry.phonenumber_prefix) {
          $scope.currentCountry.phonenumber_prefix = $scope.callRoutes[0].phonenumber_prefix;
        }
      });

    $scope.showCredentials = false;
    $rootScope.showRegHolder = false;
    $scope.inputErr = false;
    $scope.checkboxErr = false;
    $scope.emailSent = false;
    $scope.showRegModal = false;
    $scope.showRegErrModal = false;
    $scope.registration = function(section) {
      $scope.inputErr = false;
      $scope.checkboxErr = false;
      $scope.disableRegistrationByEmailButton = true;
      $scope.loginLoading = true;
      if ($scope.registrationData.email_address && $scope.enableRegister) {
        updateAnimate();
        $scope.registrationData.language = window.locale;
        $scope.registrationData.localDateFormat = UserFactory.getLocaleDateString();
        Restangular.all('auth/registration')
          .post($scope.registrationData)
          .then(
            function(data) {
              angular.element('#register_modal').removeClass('fadeOut');
              if (data.resource !== undefined && data.resource.error.no == 0) {
                $scope.emailSent = true;
                $scope.loginLoading = false;
                $rootScope.showRegHolder = true;
                $scope.showRegModal = true;
                angular.element(section).removeClass('hidden');
                angular.element('.register-user').attr('disabled', 'disabled');
                $scope.wrongEmail = false;
                $scope.wrongBorder = '';
                $scope.disableCredentials = true;

                $scope.showCredentials = true;

                $crisp.push(['set', 'user:email', [$scope.registrationData.email_address]]);
                $crisp.push(['set', 'session:event', ['user:registration']]);
                $crisp.push(['set', 'session:data', ['funnel', 'step1']]);

                dataLayer.push({
                  email: $scope.finishRegistrationData.email,
                  userId: data.resource.user_data._id,
                  event: 'registration_step_1'
                });
                // Getting Token at pushing register
                localStorage.removeItem('justLoggedIn');
                localStorage.setItem('justLoggedIn', 1);
              } else if (data.resource !== undefined && data.resource.error.no == -10) {
                $scope.showRegErrModal = true;
                // angular.element(section).addClass('hidden');
                $scope.userAlreadyRegistered = true;
                $scope.loginLoading = false;
                $scope.wrongEmail = false;
                $scope.animate = getAnimation();
                $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
                $scope.disableCredentials = false;
                $scope.disableRegistrationByEmailButton = false;
              } else {
                $scope.showRegErrModal = true;
                // angular.element(section).addClass('hidden');
                $scope.loginLoading = false;
                $scope.wrongEmail = true;
                $scope.animate = getAnimation();
                $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
                $scope.disableCredentials = false;
                $scope.disableRegistrationByEmailButton = false;
              }
            },
            function(err) {
              //growl.error(trans('registration_error_message_try_again'));
            }
          );
      } else {
        $scope.loginLoading = false;
        if (!$scope.registrationData.email_address) {
          $scope.inputErr = true;
        } else {
          $scope.checkboxErr = true;
        }
      }
    };

    $scope.closeRegModal = function(err) {
      var elem = angular.element('#register_modal');
      elem.removeClass('fadeIn');
      elem.addClass('fadeOut');
      $timeout(function() {
        $scope.showRegModal = false;
        $scope.showRegErrModal = false;
        if (!err) {
          var base_url = window.location.origin;
          window.location.href = base_url + '/finish-registration/activation/' + $scope.registrationData.email_address;
        }
      }, 700);
    };

    $scope.closeLoginModal = function() {
      var elem = angular.element('#register_modal');
      elem.removeClass('fadeIn');
      elem.addClass('fadeOut');
      $timeout(function() {
        $scope.showRegModal = false;
        $scope.showRegErrModal = false;
      }, 700);
    };

    $scope.login = function() {
      $scope.loginLoading = true;

      updateAnimate();

      $scope.loginData.language = $rootScope.currentLanguage;
      $scope.loginData.localDateFormat = UserFactory.getLocaleDateString();

      $auth.login($scope.loginData).then(
        function(data) {
          angular.element('#register_modal').removeClass('fadeOut');
          if (data.data.resource.error.no == 0) {
            $scope.wrongBorder = '';
            $scope.wrongCredentials = false;
            console.log('wrongCredentials: ', $scope.wrongCredentials);
            var previousUrl = localStorage.getItem('redirectFromBackend');

            var token = data.data.resource.jwtToken;

            localStorage.removeItem('jwtToken');
            localStorage.removeItem('isAdmin');
            localStorage.setItem('jwtToken', token);

            if (previousUrl) {
              localStorage.removeItem('redirectFromBackend');
              window.location.assign(previousUrl);
            } else {
              window.location.assign('/myaccount');
            }

            localStorage.removeItem('justLoggedIn');
            localStorage.setItem('justLoggedIn', 1);
          } else {
            $scope.showRegErrModal = true;
            $scope.loginLoading = false;
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            $scope.wrongCredentials = true;
            $scope.showInvalidLogin = true;
            console.log('showRegErrModal: ', $scope.showRegErrModal);
            console.log('wrongCredentials: ', $scope.wrongCredentials);
          }
        },
        function(errors) {
          if (errors.data.error.no === -70) {
            $scope.showRegErrModal = true;
            $scope.loginLoading = false;
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            $scope.accountDeactivated = true;
            $scope.showInvalidLogin = true;
          }
        }
      );
    };

    // $scope.resetCredentials = function() {
    //   $scope.wrongBorder = '';
    //   $scope.wrongCredentials = false;
    //   $scope.wrongEmail = false;
    //   $scope.wrongBorder = '';
    //   $scope.errorMessage = '';
    //   $scope.loginLoading = false;
    //   $scope.wrongVerificationCode = false;
    //   $scope.verificationCodeLoading = false;
    //   $scope.userAlreadyRegistered = false;
    //   $scope.wrongVerificationCodeBorder = '';
    //   $scope.animate = '';
    //   $scope.resetPasswordErrorMessage = '';
    //   $scope.accountDeactivated = false;
    // };

    $scope.loginGoogle = function(event) {
      var url = window.location.origin + '/social/google-login?local_date_format=' + UserFactory.getLocaleDateString();
      if ($scope.registrationData.phonenumber && $scope.registrationData.voice_code) {
        url +=
          '?phonenumber=' + $scope.registrationData.phonenumber + '&voice_code=' + $scope.registrationData.voice_code;
      }
      var width = 800;
      var height = 800;
      var left = screen.width / 2 - width / 2;
      var top = screen.height / 2 - height / 2;
      var googleWindow = window.open(
        url,
        'Connect google account',
        'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left
      );
      var interval = setInterval(function() {
        try {
          if (googleWindow.success == 'success') {
            clearInterval(interval);
            $crisp.push(['set', 'session:event', ['user:registration']]);
            $crisp.push(['set', 'session:data', ['funnel', 'step2']]);
            $crisp.push(['set', 'session:data', ['funnel_status', 'registered']]);
            if (googleWindow.is_registration) {
              dataLayer.push({
                email: $scope.finishRegistrationData.email,
                userId: googleWindow.user_id,
                event: 'sign_up'
              });
              //$crisp.push(["set", "session:event", ["user:login"]]);
              localStorage.removeItem('isAdmin');
              localStorage.setItem('jwtToken', googleWindow.jwtToken);
              window.location.assign('/myaccount');
            } else {
              localStorage.removeItem('isAdmin');
              localStorage.setItem('jwtToken', googleWindow.jwtToken);
              window.location.assign('/myaccount');
            }
            localStorage.removeItem('justLoggedIn');
            localStorage.setItem('justLoggedIn', 1);
          } else if (googleWindow.success == 'deactivated') {
            $scope.accountDeactivated = true;
            $scope.showInvalidLogin = true;
            $scope.$apply();
          } else if (googleWindow.success == 'error') {
            $scope.errors = [['Validator failed . Please contact support.']];
          }
        } catch (err) {}
      }, 1000);
    };

    $scope.loginGitHub = function(event) {
      var url = window.location.origin + '/social/github-login?local_date_format=' + UserFactory.getLocaleDateString();
      var width = 800;
      var height = 800;
      var left = screen.width / 2 - width / 2;
      var top = screen.height / 2 - height / 2;
      var githubLogin = window.open(
        url,
        'Connect google account',
        'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left
      );
      var interval = setInterval(function() {
        try {
          if (githubLogin.success == 'success') {
            $crisp.push(['set', 'session:event', ['user:registration']]);
            $crisp.push(['set', 'session:data', ['funnel', 'step2']]);
            $crisp.push(['set', 'session:data', ['funnel_status', 'registered']]);
            clearInterval(interval);
            if (githubLogin.is_registration) {
              dataLayer.push({
                email: $scope.finishRegistrationData.email,
                userId: githubLogin.user_id,
                event: 'sign_up'
              });
              //$crisp.push(["set", "session:event", ["user:login"]]);
              localStorage.removeItem('isAdmin');
              localStorage.setItem('jwtToken', googleWindow.jwtToken);
              window.location.assign('/myaccount');
            } else {
              localStorage.removeItem('isAdmin');
              localStorage.setItem('jwtToken', githubLogin.jwtToken);
              window.location.assign('/myaccount');
            }
            localStorage.removeItem('justLoggedIn');
            localStorage.setItem('justLoggedIn', 1);
          } else if (githubLogin.success == 'deactivated') {
            $scope.accountDeactivated = true;
            $scope.showInvalidLogin = true;
            $scope.$apply();
          } else if (githubLogin.success == 'error') {
            $scope.errors = [['Validator failed . Please contact support.']];
          }
        } catch (err) {}
      }, 1000);
    };

    $scope.loginFacebook = function(event) {
      var url =
        window.location.origin + '/social/facebook-login?local_date_format=' + UserFactory.getLocaleDateString();
      if ($scope.registrationData.phonenumber && $scope.registrationData.voice_code) {
        url +=
          '?phonenumber=' + $scope.registrationData.phonenumber + '&voice_code=' + $scope.registrationData.voice_code;
      }
      var width = 800;
      var height = 800;
      var left = screen.width / 2 - width / 2;
      var top = screen.height / 2 - height / 2;
      var facebookWindow = window.open(
        url,
        'Connect facebook account',
        'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left
      );
      var interval = setInterval(function() {
        try {
          if (facebookWindow.success == 'success') {
            clearInterval(interval);
            $crisp.push(['set', 'session:event', ['user:registration']]);
            $crisp.push(['set', 'session:data', ['funnel', 'step2']]);
            $crisp.push(['set', 'session:data', ['funnel_status', 'registered']]);
            if (facebookWindow.is_registration) {
              dataLayer.push({
                email: $scope.finishRegistrationData.email,
                userId: facebookWindow.user_id,
                event: 'sign_up'
              });
              //$crisp.push(["set", "session:event", ["user:login"]]);
              localStorage.removeItem('isAdmin');
              localStorage.setItem('jwtToken', googleWindow.jwtToken);
              window.location.assign('/myaccount');
            } else {
              localStorage.removeItem('isAdmin');
              localStorage.setItem('jwtToken', facebookWindow.jwtToken);
              window.location.assign('/myaccount');
            }
            localStorage.removeItem('justLoggedIn');
            localStorage.setItem('justLoggedIn', 1);
            window.location.assign('/myaccount');
          } else if (facebookWindow.success == 'deactivated') {
            $scope.accountDeactivated = true;
            $scope.showInvalidLogin = true;
            $scope.$apply();
          } else if (facebookWindow.success == 'error') {
            $scope.errors = [['Validator failed . Please contact support.']];
          }
        } catch (err) {
          console.log(err);
        }
      }, 1000);
    };

    $scope.changeStep = function(step) {
      // $scope.resetCredentials();
      $scope.regVerificationStep = step;
    };

    $scope.choiceCountry = function(country, price) {
      console.log(country);
      if (price == undefined) {
        price = false;
      }

      $scope.currentCountry = country;
      var button = angular.element('.selected-phonenumber-image');
      button.find('img').attr('src', '/laravel_assets/callburn/images/lang-flags/' + country.code + '.svg');
      if (price) {
        button.find('span').text(country.name + ' (+' + country.phonenumber_prefix + ')');
      } else {
        button.find('span').text('(+' + country.phonenumber_prefix + ')');
      }
    };

    $scope.choiceCountryPrice = function(country) {
      $scope.choiceCountry(country, true);
      $scope.price = country.customer_price;
      $rootScope.prices = country.customer_price;
      $scope.smsPrice = country.sms_price;
      console.log(country.sms_price);
      $rootScope.smsPrices = country.sms_price;
    };

    /****************SHOW PRICE ON VOICE MESSAGES******************/

    $scope.phonenumber_id = false;

    $scope.verification_status_text = null;
    $scope.sendVerificationCallReg = function() {
      var postData = {
        phonenumber: /*$scope.currentCountry.phonenumber_prefix +*/ $scope.finishRegistrationData.phonenumber,
        action: 'registration'
      };
      $crisp.push(['set', 'user:phone', postData.phonenumber]);
      console.log(postData);

      updateAnimate();
      $scope.loginLoading = true;
      $scope.canMakeVerificationCall = false;
      $scope.regVerificationStepStatus = false;
      Restangular.all('verification/send-verification-code-front')
        .post(postData)
        .then(function(data) {
          if (data.resource.error.no == 0) {
            $rootScope.socket.on('update-verification-message', function(data) {
              if (!$scope.phonenumber_id || $scope.phonenumber_id == data.message.phonenumber_id) {
                $scope.phonenumber_id = data.message.phonenumber_id;
                $scope.verification_status = data.message.status;
                switch ($scope.verification_status) {
                  case 'CALLING':
                    $scope.verification_status_text = 'verification_calling_in_progress';
                    break;
                  case 'FAILED':
                    $scope.verification_status_text = 'failed';
                    break;
                  case 'SUCCEED':
                    $scope.verification_status_text = 'succeed';
                    break;
                  default:
                    $scope.verification_status_text = 'failed';
                    break;
                }
              }
            });
            $scope.loginLoading = false;
            $scope.counter = 40;
            $scope.disableButton = true;
            $scope.timer(40).then(function() {
              $scope.disableButton = false;
            });
            $scope.disableVerificationCodeButton = true;
            $scope.regVerificationStepSucces2 = true;
            $scope.finalyPhonenumber = data.resource.phonenumber;
            $scope.changeStep(3);
          } else if (data.resource.error.no == -5) {
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            $scope.loginLoading = false;
            $scope.wrongEmailExpired = true;
          } else {
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            $scope.loginLoading = false;
            $scope.wrongEmail = true;
          }
        });
    };

    $scope.$watch(
      'finishRegistrationData.voice_code',
      function(newVal) {
        if (newVal && newVal.length == 4) {
          $scope.disableVerificationCodeButton = false;
        } else {
          $scope.disableVerificationCodeButton = true;
        }
      },
      true
    );

    $scope.$watch(
      'registrationData.email_address',
      function(newVal) {
        if (newVal && newVal.length > 0) {
          $scope.disableRegistrationByEmailButton = false;
        } else {
          $scope.disableRegistrationByEmailButton = true;
        }
      },
      true
    );

    $scope.validateVoiceCodeReg = function() {
      $scope.verificationCodeLoading = true;
      updateAnimate();
      var postData = {
        phonenumber: /*$scope.currentCountry.phonenumber_prefix +*/ $scope.finishRegistrationData.phonenumber.slice(1),
        voice_code: $scope.finishRegistrationData.voice_code
      };
      Restangular.all('verification/check-voice-code-validation-front')
        .post(postData)
        .then(function(data) {
          if (data.resource.error.no == 0) {
            $scope.verificationCodeLoading = false;
            $scope.disableButton = true;
            $scope.disableVerificationCodeButton = true;
            $scope.finishUsernameReset = false;
            $scope.changeStep(4);
          } else {
            $scope.verificationCodeLoading = false;
            $scope.wrongVerificationCode = true;
            $scope.animate = getAnimation();
            $scope.wrongVerificationCodeBorder = 'wrong-border-style ' + $scope.animate;
          }
        });
    };

    $scope.checkPassword = function() {
      if ($scope.finishRegistrationData.password == $scope.finishRegistrationData.password_confirmation) {
        if (!$scope.finishRegistrationData.password) {
          return false;
        } else {
          return true;
        }
      } else {
        return false;
      }
    };

    $scope.sendPasswordResetLink = function() {
      updateAnimate();
      $scope.loginLoading = true;
      $scope.disableSendPasswordResetLink = true;
      Restangular.all('/auth/send-reset-link')
        .post({
          email: $scope.resetPaswordEmail
        })
        .then(
          function(data) {
            $scope.loginLoading = false;
            if (data.resource.error.no == 0) {
              $scope.emailMessage = true;
              $scope.wrongEmail = false;
              $scope.wrongBorder = '';
            } else {
              $scope.loginLoading = false;
              $scope.emailMessage = false;
              $scope.wrongEmail = true;
              $scope.animate = getAnimation();
              $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            }
            $scope.successOrWrong = 'angular-success';
          },
          function(err) {
            $scope.loginLoading = false;
            $scope.successOrWrong = 'angular-wrong';
          }
        );
    };

    $scope.submitSaving = function() {
      updateAnimate();
      if ($scope.finalyPhonenumber) {
        $scope.finishRegistrationData.phonenumber = $scope.finalyPhonenumber;
      }
      $scope.finishRegistrationData.language = $rootScope.currentLanguage;
      $scope.loginLoading = true;
      Restangular.all('auth/activate-account')
        .post($scope.finishRegistrationData)
        .then(function(data) {
          $scope.loginLoading = false;
          if (data.resource.error.no == 0) {
            $crisp.push(['set', 'user:email', [$scope.finishRegistrationData.email]]);
            $crisp.push(['set', 'session:data', ['funnel', 'step2']]);
            $crisp.push(['set', 'session:data', ['funnel_status', 'registered']]);

            dataLayer.push({
              email: $scope.finishRegistrationData.email,
              userId: data.resource.user_data._id,
              event: 'sign_up'
            });
            localStorage.removeItem('isAdmin');
            localStorage.setItem('jwtToken', data.resource.jwtToken);
            window.location.assign('/myaccount');
          } else {
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            $scope.wrongCredentials = true;
            console.log('wrongCredentials: ', $scope.wrongCredentials);
            $scope.errorMessage = data.resource.error.text;
          }
        });
    };

    $scope.resetPassword = function() {
      var resetData = {
        password: $scope.finishRegistrationData.password,
        password_confirmation: $scope.finishRegistrationData.password_confirmation,
        token: urlParams[2],
        language: $rootScope.currentLanguage
      };
      updateAnimate();
      $scope.loginLoading = true;
      Restangular.all('auth/make-reset-password')
        .post(resetData)
        .then(function(data) {
          if (data.resource.error.no == 0) {
            $scope.loginLoading = false;
            localStorage.setItem('jwtToken', data.resource.jwtToken);
            redirect('myaccount/#/dashboard/dashboard?password=true');
          } else {
            $scope.resetPasswordErrorMessage = data.resource.error.text;
            $scope.loginLoading = false;
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
            $scope.wrongCredentials = true;
            console.log('wrongCredentials: ', $scope.wrongCredentials);
            $scope.errorMessage = data.resource.error.text;
          }
        });
    };

    $scope.resetRecoveryPhonenumber = function(number) {
      $scope.verificationCall.phonenumber = number;
      if (number.length === 0 && ($scope.wrongEmail || $scope.wrongNumberNotExist || $scope.dailyLimit)) {
        $scope.wrongEmail = false;
        $scope.wrongNumberNotExist = false;
        $scope.dailyLimit = false;
      }
    };

    $scope.makeResetcall = function() {
      var data = {};
      $scope.loginLoading = true;
      updateAnimate();
      data.phonenumber = $scope.verificationCall.phonenumber;
      data.is_login_recovery = true;
      Restangular.all('/verification/send-verification-code-front')
        .post(data)
        .then(function(data) {
          if (data.resource.error.no === 0) {
            $scope.loginLoading = false;
            $scope.disableButton = true;
            $scope.counter = 20;
            $scope.timer(40).then(function() {
              $scope.disableButton = false;
            });
            $scope.disableVerificationCodeButton = false;
            $scope.recoverUsernameStep = 2;
          } else if (data.resource.error.no === -5) {
            $scope.loginLoading = false;
            $scope.wrongEmail = false;
            $scope.wrongNumberNotExist = false;
            $scope.dailyLimit = true;
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
          } else if (data.resource.error.no === -6) {
            $scope.loginLoading = false;
            $scope.wrongEmail = false;
            $scope.wrongNumberNotExist = true;
            $scope.dailyLimit = false;
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
          } else {
            $scope.loginLoading = false;
            $scope.wrongEmail = true;
            $scope.wrongNumberNotExist = false;
            $scope.dailyLimit = false;
            $scope.animate = getAnimation();
            $scope.wrongBorder = 'wrong-border-style ' + $scope.animate;
          }
        });
    };

    $scope.checkVerification = function() {
      $scope.disableVerificationCodeButton = true;
      $scope.verificationCodeLoading = true;
      updateAnimate();

      var data = {
        phonenumber: +$scope.verificationCall.phonenumber,
        code: $scope.verificationCall.code
      };
      Restangular.all('auth/recover-username')
        .post(data)
        .then(function(data) {
          if (data.resource.error.no == 0) {
            $scope.verificationCodeLoading = false;
            $scope.disableButton = true;
            $scope.finishUsernameReset = false;
            $scope.username = data.resource.username;
          } else {
            $scope.verificationCodeLoading = false;
            $scope.wrongVerificationCode = true;

            $scope.animate = getAnimation();
            $scope.wrongVerificationCodeBorder = 'wrong-border-style ' + $scope.animate;
          }
          $scope.disableVerificationCodeButton = false;
        });
    };

    // $document.on('keydown', function($event) {
    //     var key = $event.keyCode || $event.charCode;
    //     if (key === 13) {
    //         switch ($scope.regVerificationStep) {
    //             case 2:
    //                 $scope.sendVerificationCallReg();
    //                 break;
    //             case 4:
    //                 $scope.submitSaving();
    //                 break
    //         }
    //     }
    // });
  }
]);
