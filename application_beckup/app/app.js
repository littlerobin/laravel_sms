var dependencies = [

    'ngSanitize',
    'ui.router',
    'restangular',
    'angularFileUpload',
    'oc.lazyLoad',
    'datePicker',
    'ui-rangeSlider',
    '720kb.tooltips',
    'angular-timezone-selector',
    'ui.select',
    'ngAnimate',
    'cgNotify',
    'angularModalService'
];

var frontDependencies = [
    'ngSanitize',
    'ui.router',
    'restangular',
    'oc.lazyLoad',
    'ui.select',
    'ngAnimate'
];
window.translate = {};

//config
require("./config/config");


//authenticated application
angular.module('callburnApp', dependencies)

    //filters
    .filter('removeFirstPart', require("./filters/removeFirstPart"))

    //directives
    .directive("callburnSelect", require("./directives/callBurnSelect"))
    .directive("callModal", require("./directives/callModal"))

    //factories
    .factory("CallBournModal", require("./factory/CallburnModal"))

    //routes
    .config(require("./bootstrap/routes"))


    //run
    .run(require("./config/run"));


//front application
// angular.module('frontCallburnApp', frontDependencies)
//
//     //directives
//     .directive("callburnSelect", require("./directives/callBurnSelect"))
//     .directive("callModal", require("./directives/callModal"))
//
//     //factories
//     .factory("CallBournModal", require("./factory/CallburnModal"))
//
//     //routes
//     .config(require("./bootstrap/routes.frontApp"));


//controllers
require("./bootstrap/controllers");