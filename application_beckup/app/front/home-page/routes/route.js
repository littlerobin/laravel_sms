module.exports = function($stateProvider){
    $stateProvider.state('home-page', {
        url: '/home-page/:token?',
        templateUrl: '/app/front/home-page/view/home-page.html',
        controller: 'HomePageController',
        resolve: {
            deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                return $ocLazyLoad.load({
                    name: 'frontCallburnApp',
                    files: [
                        '/app/front/home-page/controller/HomePageController.js',
                        '/app/front/finish-registration/controller/FinishRegistrationController.js',
                    ]
                });
            }]
        }
    });
}