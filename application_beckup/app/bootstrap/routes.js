module.exports = function ($stateProvider, $urlRouterProvider) {
    require("../engines/account/routes")($stateProvider);
    require("../engines/dashboard/routes")($stateProvider);
    require("../engines/addressbook/routes")($stateProvider);
    require("../engines/api/routes")($stateProvider);
    require("../engines/campaign/routes")($stateProvider);
    $urlRouterProvider.otherwise('dashboard/dashboard');
};
