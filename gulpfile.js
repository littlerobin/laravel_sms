var gulp = require('gulp');
const elixir = require('laravel-elixir');
var plugins = require('gulp-load-plugins')();

/*

 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
  mix.scripts(
    [
      '../bower_components/jquery/dist/jquery.min.js',
      '../bower_components/jquery-ui/jquery-ui.min.js',
      '../bower_components/select2/dist/js/select2.full.min.js',
      'front/select2.js'
    ],
    './public/laravel_assets/front/js/select2.js'
  );

  mix.scripts(
    [
      '../bower_components/angular/angular.min.js',
      '../bower_components/lodash/lodash.min.js',
      '../bower_components/angular-animate/angular-animate.min.js',
      '../bower_components/restangular/dist/restangular.min.js',
      '../bower_components/angular-toArrayFilter/toArrayFilter.js',
      '../bower_components/angular-growl-v2/build/angular-growl.min.js',
      '../bower_components/angular-jwt/dist/angular-jwt.min.js',
      '../bower_components/js-cookie/src/js.cookie.js',
      '../bower_components/ladda/dist/spin.min.js',
      '../bower_components/ladda/dist/ladda.min.js',
      '../bower_components/angular-ladda/dist/angular-ladda.min.js',
      '../bower_components/moment/min/moment.min.js',
      '../bower_components/moment-timezone/builds/moment-timezone-with-data.min.js',
      '../bower_components/cookieconsent/build/cookieconsent.min.js',
      '../bower_components/intl-tel-input/build/js/utils.js',
      '../bower_components/ng-intl-tel-input/dist/ng-intl-tel-input.min.js',
      '../bower_components/intl-tel-input/build/js/intlTelInput.min.js',
      '../bower_components/intl-tel-input/examples/js/defaultCountryIp.js',
      '../bower_components/intl-tel-input/examples/js/isValidNumber.js',
      '../bower_components/slick-carousel/slick/slick.min.js',
      '../bower_components/angular-slick-carousel/dist/angular-slick.min.js',
      '../bower_components/tether/dist/js/tether.min.js',
      '../bower_components/aos/dist/aos.js',
      '../bower_components/chartjs/dist/Chart.min.js',
      '../bower_components/socket.io-client/dist/socket.io.js',

      '../node_modules/satellizer/dist/satellizer.min.js',
      '../node_modules/bootstrap/dist/js/bootstrap.min.js',
      'front/ie10-viewport-bug-workaround.js',

      //controllers
      'front/angular/config/config.js',
      'front/angular/app.js',
      'front/angular/controllers/MainController.js',
      'front/angular/factory/LanguageControl.js',
      'front/angular/factory/UserFactory.js',
      'front/angular/directives/callBurnSelect.js',
      'front/angular/controllers/AuthenticationController.js',
      'front/angular/controllers/InvitationController.js',
      'front/angular/controllers/ClickToCallController.js',
      'front/angular/controllers/TermsAndConditionsPrivacyPolicyController.js',
      'front/angular/controllers/VoiceMessagesController.js',
      'front/scripts.js',
      'front/cookie.js'
    ],
    './public/laravel_assets/front/js/app.js'
  );

  mix.styles(
    [
      '../node_modules/bootstrap/dist/css/bootstrap.min.css',
      'front/styles.css',
      '../bower_components/intl-tel-input/build/css/intlTelInput.css',
      '../bower_components/angular-growl-v2/build/angular-growl.min.css',
      '../bower_components/glyphicons/styles/glyphicons.css',
      '../bower_components/ladda/dist/ladda-themeless.min.css',
      '../bower_components/animate.css/animate.min.css',
      '../bower_components/cookieconsent/build/cookieconsent.min.css',
      '../bower_components/slick-carousel/slick/slick.css',
      '../bower_components/slick-carousel/slick/slick-theme.css',
      '../bower_components/aos/dist/aos.css',
      '../bower_components/balloon-css/balloon.min.css'
    ],
    './public/laravel_assets/front/css/all.css'
  );

  mix.sass(
    [
      '../bower_components/slick-carousel/slick/slick.css',
      '../bower_components/slick-carousel/slick/slick-theme.css',
      '../bower_components/intl-tel-input/build/css/intlTelInput.css',
      '../bower_components/angular-growl-v2/build/angular-growl.min.css',
      '../bower_components/glyphicons/styles/glyphicons.css',
      '../bower_components/ladda/dist/ladda-themeless.min.css',
      '../bower_components/animate.css/animate.min.css',
      '../bower_components/cookieconsent/build/cookieconsent.min.css'
    ],
    './public/laravel_assets/front/css/app.css'
  );

  mix.copy('resources/assets/js/front/callme.js', './public/laravel_assets/front/js/callme.js');
  mix.copy(
    'resources/assets/bower_components/jquery/dist/jquery.min.js',
    './public/laravel_assets/front/js/jquery.min.js'
  );

  mix.copy(
    'resources/assets/bower_components/select2/dist/css/select2.min.css',
    './public/laravel_assets/front/css/select2.css'
  );

  mix.scripts(['front/snippet.js'], './public/laravel_assets/front/js/snippet.js');

  mix.scripts(['front/snippet-production.js'], './public/laravel_assets/front/js/snippet-production.js');

  mix.scripts(['front/snippet-beta.js'], './public/laravel_assets/front/js/snippet-beta.js');

  mix.version([
    'public/laravel_assets/front/js/app.js',
    'public/laravel_assets/front/js/snippet.js',
    'public/laravel_assets/front/js/snippet-production.js',
    'public/laravel_assets/front/js/snippet-beta.js',
    'public/laravel_assets/front/css/all.css'
  ]);
});
