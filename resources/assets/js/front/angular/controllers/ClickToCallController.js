angular.module('frontCallburnApp').controller('ClickToCallController',
    ['$scope', '$rootScope', '$document', '$interval',
    function ($scope, $rootScope, $document, $interval) {

    	$scope.weekDays = [
        	'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ];

        $scope.hours = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24'];

        $scope.minutes = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

        $scope.dropdownOpen = false;
        $scope.dropdownOpen2 = false;
        $scope.dropdownOpen3 = false;
        $scope.dropdownOpen4 = false;

        $scope.showLoader = true;

        angular.element(document).ready(function() {
        	var stop = $interval(function() {
	        	if (window.pageIsLoaded) {
	        		$scope.showLoader = false;
	        		angular.element(".snippet-main-content").attr('style', "position: inherit").show();
	        	}
	        }, 100);
	    });

        var ids = [
            'weekDay',
            'hour',
            'minute',
            'timezones'
        ];

       	angular.element(document).on('click',  function($event) {
            if (ids.indexOf(event.target.id) == -1) {
	       		$scope.dropdownOpen = false;
		        $scope.dropdownOpen2 = false;
		        $scope.dropdownOpen3 = false;
		        $scope.dropdownOpen4 = false;
                $scope.$apply();
            }
       	});

       	var userOffset =- (new Date().getTimezoneOffset()/60);

       	var timeZones = moment.tz.names();
		var offsetTmz = [];

		$scope.getUserTimezone = function(userOffset) {

			if(userOffset == undefined) {
				userOffset =-(new Date().getTimezoneOffset()/60)
			}

		    var timeZones = moment.tz.names();
		    for (var i in timeZones) {
		        var offset = moment.tz(timeZones[i]).utcOffset()/60;

		        if (offset == userOffset) {
		            return timeZones[i];
		        }
		    }
		}

		$scope.UserTimezone = userOffset > 0 ? '+' + userOffset : userOffset;

		for (var i in timeZones) {
		    var offset = moment.tz(timeZones[i]).utcOffset()/60;

		    if( offsetTmz.indexOf(offset) == -1) {
		        offsetTmz.push(offset);
		    }
		}

		offsetTmz.sort(function(a, b) { return a - b;});

		$scope.timeZones = offsetTmz.sort(function(a, b) { return a - b;});

		var choiceTimezone = function(TimezoneOffset) {
		    var request = new XMLHttpRequest;
		    request.onreadystatechange = function () {

		        if (4 == this.readyState && 200 == this.status) {
		            var response = JSON.parse(request.responseText);
		           
		            window.snippet.allowed_date_times = response.updatedDate;
		            //var newweekDays = Object.keys(response.updatedDate);
		            checkSnippetTime(true);
		        }
		    };
		    request.open("POST", baseUrl + "/main-js/" + token + "/" + TimezoneOffset);
		    request.setRequestHeader('Content-type', 'application/json; charset=utf-8');
		    var data = JSON.stringify({
		      changeTimezone: true,
		    });
		    request.send(data);
		}

		var getTime = function() {
		    var time = moment().format('hh:mm');
	        $scope.time = time;
	       	$scope.$apply();
		}

		setInterval(function() {
			getTime();
		}, 500);        
}]);
