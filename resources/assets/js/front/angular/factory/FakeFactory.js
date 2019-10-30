angular.module('frontCallburnApp').factory('fakeFac',
    function ($q, $timeout, $log) {

        var standardDelay = 1000;
        return {
            success: function ()
            {
                var defer = $q.defer();
                $timeout(function ()
                {
                    $log.info('resolve');
                    defer.resolve({
                        msg: 'SUCCESS'
                    });
                }, standardDelay);
                return defer.promise;
            },
            error: function ()
            {
                var defer = $q.defer();
                $timeout(function ()
                {
                    $log.info('error');
                    defer.reject({
                        msg: 'ERROR'
                    });
                }, standardDelay);
                return defer.promise;
            },
            endless: function ()
            {
                var defer = $q.defer();
                return defer.promise;
            }
        };



    });