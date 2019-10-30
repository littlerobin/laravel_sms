module.exports = function (Restangular, $state, $rootScope, notify) {
    var isNotified = false;
    Restangular.addResponseInterceptor(function (resp) {
        if (resp.resource.error && resp.resource.error.no == -10) {
            if (!isNotified) {
                alert('Your session is expired, please login again!');
                isNotified = true;
            }
            window.location = frontUrl;
        } else if (resp.resource.error && resp.resource.error.no != 0) {
            notify({message: resp.resource.error.text, classes: ['notification-alert-danger']})
        }
        return resp;
    });

    Restangular.setErrorInterceptor(function (response) {
        if (response.status == 500) {
            $rootScope.stopLoader();
            notify({message: 'Something went wrong', classes: ['notification-alert-danger']})
        }
    });
};
