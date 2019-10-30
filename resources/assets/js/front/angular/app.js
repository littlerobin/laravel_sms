var frontDependencies = [
  'restangular',
  'angular-toArrayFilter',
  'satellizer',
  'angular-growl',
  'angular-jwt',
  'angular-ladda',
  'ngIntlTelInput',
  'slickCarousel'
];

function appConfig($rootScopeProvider) {
  $rootScopeProvider.digestTtl(12);
  console.log('12')
}

angular
  .module('frontCallburnApp', frontDependencies)
  .config(function($interpolateProvider, $authProvider) {
    $authProvider.loginUrl = '/auth/login';
  })

  .config(function(ngIntlTelInputProvider) {
    ngIntlTelInputProvider.set({
      autoPlaceholder: 'aggressive',
      initialCountry: 'auto',
      excludeCountries: [],
      geoIpLookup: function(callback) {
        $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
          var countryCode = resp && resp.country ? resp.country : '';
          callback(countryCode);
        });
      },
      // onlyCountries: ['es', 'it', 'am', 'au', 'us', 'gb', 'se',
      //                 'pl', 'pt', 'ro', 'dk', 'gr', 'my', 'bg',
      //                 'lu', 'lt', 'mt', 'is', 'lv', 'ee', 'fr',
      //                 'no', 'nl', 'fi', 'de', 'hu', 'ie'],
      utilsScript: '/../../../bower_components/intl-tel-input/build/js/utils.js',
      customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
        sessionStorage.setItem('dial', selectedCountryData.dialCode);
        return '+ ' + sessionStorage.getItem('dial') + ' ' + selectedCountryPlaceholder;
      }
    });
  })

  .config(function(RestangularProvider) {})

  .config(['$rootScopeProvider', appConfig]);
