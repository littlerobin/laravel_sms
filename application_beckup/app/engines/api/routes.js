module.exports = function ($stateProvider) {
    $stateProvider
        .state('api', {
            url: '/api',
            templateUrl: '/app/engines/api/main.html',
            controller: function ($state) {
                if ($state.current.name == 'api') {
                    $state.go('api.settings');
                }
            }
        })
        .state('api.settings', {
            url: '/settings',
            templateUrl: '/app/engines/api/settings/views/index.html',
            controller: 'SettingsController',
            resolve: {
                apiKeys: function (Restangular) {
                    return Restangular.one('api-keys/api-keys').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/api/settings/controllers/SettingsController.js',
                        ]
                    });
                }]
            }
        })
};