module.exports = function ($stateProvider, $urlRouterProvider) {
    require("../front/price/routes/route")($stateProvider);
    require("../front/contactus/routes/route")($stateProvider);
    require("../front/home-page/routes/route")($stateProvider);
    $urlRouterProvider.otherwise('home-page/');
};