module.exports = function($stateProvider){
    $stateProvider
        .state('price', {
            url: '/price',
            templateUrl: '/app/front/price/view/prices.html',
            controller: 'PricesController',
            resolve: {
                callRoutes: function (Restangular) {
                    return Restangular.one('data/call-routes').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'frontCallburnApp',
                        files: [
                            '/app/front/price/controller/PricesController.js',
                        ]
                    });
                }]
            }
        })
}