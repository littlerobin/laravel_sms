var countries  =   {!! $countries !!};
var imagePath  =   '{{$imagePath}}';
var baseUrl    =   '{{$baseUrl}}';
window.snippet =   {!! $snippet !!};
var token      =   '{{$token}}';
var local      =   '{{$local}}';

window.calling = false;

var site_language = '{{$site_language}}';

var snippetType = {!! $type !!};

var customContent = snippet.default_text , snippetContent;

var weekDaysArray = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
];

var currentOffset = localStorage.getItem('user-offset');

if (!currentOffset) {
    localStorage.setItem('user-offset', -(new Date().getTimezoneOffset()/60));
}

function cloneObject(obj) {
    var copy;
    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = cloneObject(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = cloneObject(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}

var getDayTimes = function(weekDayDataString) {
    var momentEn = moment(weekDayDataString, "DD/MM/YYYY");

    momentEn.locale('en');

    var weekDay = momentEn.format('dddd');
    var currentDate = null;

    var snippetClone = cloneObject(window.snippet);
    var dateArray = snippetClone.allowed_date_times;
    for (key in snippetClone.allowed_date_times) {
        // if (key == weekDay) {
            currentDate = snippetClone.allowed_date_times[key];
        // }
    }

    if (!currentDate) {
        return false;
    }

    if (moment().format("DD/MM/YYYY") == weekDayDataString) {
        for (var i = 0; i < currentDate.length; i++) {
            var currentStartDateArray = currentDate[i].start.split(':');
            var currentEndDateArray = currentDate[i].end.split(':');

            var currentStartHour = currentStartDateArray[0];
            var currentStartMinute = currentStartDateArray[1];
            var currentEndHour = currentEndDateArray[0];
            var currentEndMinute = currentEndDateArray[1];

            var dateTimeStart = moment().set({
                hour : currentStartHour,
                minute : currentStartMinute,
            });
            var dateTimeEnd = moment().set({
                hour : currentEndHour,
                minute : currentEndMinute,
            });

            if (moment().diff(dateTimeStart) > 0 && moment().diff(dateTimeEnd) < 0) {
                var newDate = {};
                var currentHour = moment().format('H');
                var currentMinute = parseInt(moment().format('m'));
                currentMinute = "" + (currentMinute + (5 - currentMinute % 5)) + "";
                currentMinute = currentMinute.length > 1 ? currentMinute : "0" + currentMinute;

                newDate.start = currentHour + ":" + currentMinute;
                newDate.end = currentDate[i].end;
                currentDate[i] = newDate;
                break;
            }
        }

        return currentDate;
    } else {
        return currentDate;
    }
}

var getDayIndexByMoment = function(name) {
    if (weekDaysArray.indexOf(name) == 6) {
        return 0;
    }

    return weekDaysArray.indexOf(name) + 1
}

var localLang = (navigator.language || navigator.userLanguage).split("-")[0];
moment.locale(localLang);

if (customContent) {
    snippetContent = '<p id="information-text-1">' + customContent.trim() + '</p> <p id="information-text-2"> </p>';
} else {
    snippetContent = '<p id="information-text-1" class="snippet-text-center snippet-font-Raleway">{{trans('main.snippet.want_to_talk_with_us')}} <br>{{trans('main.snippet.lets_do_it')}} <strong>{{trans('main.snippet.now')}}</strong>, {{trans('main.snippet.totally')}} <strong>{{trans('main.snippet.free')}}</strong> {{trans('main.snippet.of_charge')}} </p> <p id="information-text-2" class="snippet-text-center snippet-font-Raleway snippet-font-size-14px">{{trans('main.snippet.tell_us_your_phonenumber_and_we_will')}} <br> <strong>{{trans('main.snippet.ring')}}</strong> {{trans('main.snippet.you_just_immediately')}} </p>';
}

if (snippet.image_url) {
    var snippetLogo = snippet.image_url;
} else {
    var snippetLogo = imagePath + '/images/click_to_call_icons/callerid-icon.svg';
}

var div = document.createElement('div');

if (local) {
    var body = document.getElementById('callburn-snippet');
    div.setAttribute("style", "position: inherit");
} else {
    var body = document.getElementsByTagName('body')[0];
}
div.className += " snippet-main-content ";

var getNextWeekDay = function (dayNumber, noTranslateMoment) {
    if (noTranslateMoment === undefined) {
        noTranslateMoment = false;
    }

    var momentEn = moment().locale('en');

    var finalDate;
    var today = moment();
    var nextWeekDay = moment(moment().day(dayNumber).format("DD/MM/YYYY"),"DD/MM/YYYY");
     if (today.diff(nextWeekDay) < 0 || today.format('DD/MM/YYYY') == nextWeekDay.format('DD/MM/YYYY')) {
        if (noTranslateMoment) {
            finalDate =  momentEn.day(dayNumber) 
        } else {
            finalDate = moment().day(dayNumber)
        }

        return finalDate.format("dddd, DD MMM");
    } else {
        if (noTranslateMoment) {
            return momentEn.add(7,'days').day(dayNumber).format("dddd, DD MMM");
        }

        return moment().add(7,'days').day(dayNumber).format("dddd, DD MMM");
    }
}

var getNextWeekDayByDD = function(dayNumber) {
    var today = parseInt(moment().format("DD"));

    var nextWeekDay = parseInt(moment().day(dayNumber).format("DD"));

    if (nextWeekDay >= today && moment().diff(moment().day(dayNumber)) <= 0) {
        return parseInt(moment().day(dayNumber).format("DD"));
    } else {
        return parseInt(moment().add(7,'days').day(dayNumber).format("DD"));
    }
}

var getNextWeekDayForHolidayMode = function (nextWeekday, start,end) {
    var nextWeekdayClone = nextWeekday.clone();
    var startClone = start.clone();
    var endClone = end.clone();
    if (nextWeekdayClone.diff(startClone) >= 0 && nextWeekdayClone.diff(endClone.add(1,'days')) <= 0) {
        while(nextWeekdayClone.diff(endClone) <= 0) {
            nextWeekdayClone = nextWeekdayClone.add(7,'days');
        }
    }

    return nextWeekdayClone;
}

//checking if todays hours are out of range
var checkHoursWithToday = function (date) {
    for (var i = 0; i < date.length; i++) {
        var currentEndDateArray = date[i].end.split(':');
        var currentEndHour = currentEndDateArray[0];
        var currentEndMinute = currentEndDateArray[1];
        var dateTime = moment().set({
            hour : currentEndHour,
            minute : currentEndMinute,
        });

        if (dateTime.diff(moment()) > 0) {
            return true;
        }
    }

    return false;
}

var fillDatesTo7 = function (dateArray) {
    var finalArray = [];
    dateArray.sort(function(a,b) {
        return moment(a,"dddd, DD MMM").valueOf() - moment(b,"dddd, DD MMM").valueOf() ;
    });
    
    if (snippet.is_active_holiday_mode) {
        var holidayStart = snippet.holiday_mode.split('-')[0].trim();
        var holidayEnd = snippet.holiday_mode.split('-')[1].trim();
    }
    var year = moment().format("YYYY");

    while(finalArray.length < 7) 
    {
        for(var i = 0; i < dateArray.length; i++) {
            var weekDay = dateArray[i];
            if(snippet.is_active_holiday_mode) {
                var date = moment(weekDay, "dddd, DD MMM").year(year).format('DD/MM/YYYY');
                date = moment(date,"DD/MM/YYYY");

                if (date.format('DD/MM/YYYY') == moment().format('DD/MM/YYYY')) {
                    var momentEn = moment().locale('en');
                    var hoursAndMinutesForToday = getDayTimes(momentEn.format('DD/MM/YYYY'));
                    var check = checkHoursWithToday(hoursAndMinutesForToday);

                    if (!check) {
                        continue;
                    }
                }

                var dateClone = date.clone();
                if (dateClone.diff(moment(holidayStart,'DD/MM/YYYY')) >= 0 && dateClone.diff(moment(holidayEnd,'DD/MM/YYYY')) <= 0) {
                    continue;
                }
            }

            finalArray.push(weekDay);
        
            if (finalArray.length == 7) {break;}
        }
        for (var j = 0; j < dateArray.length; j++) {
            dateArray[j] = getNextWeekDayByDaye(dateArray[j]);
        }
    }

    return finalArray;
}

var changeWeekDaysArrayFormat = function (weekDays, withDate) {
    if (withDate === undefined) {
        withDate = false;
    }

    var data = {};
    var finalData = [];
    var finalDataWithDate = [];

    var compared = weekDays.forEach(function (item) {
         var dayNumber = getDayIndexByMoment(item);
         var dayDD = getNextWeekDayByDD(dayNumber);
         data[dayDD] = item;
    });

    Object.keys(data)
      .sort()
      .forEach(function(key, value) {
          var dayNumber = getDayIndexByMoment(data[key]);
          finalData.push(data[key]);
          finalDataWithDate.push(getNextWeekDay(dayNumber));
        });

    // finalDataWithDate = fillDatesTo7(finalDataWithDate);
    finalDataWithDate.sort(function(a,b) {
        return moment(a,"dddd, DD MMM").valueOf() - moment(b,"dddd, DD MMM").valueOf() ;
    });
     
     if (withDate) {
        return finalDataWithDate;
     }

    return finalData;
}

var getNextWeekDayByDaye = function (dateString) {
    var year = moment().format("YYYY");
    var date = moment(dateString, "dddd, DD MMM").year(year).format("DD/MM/YYYY");
    date = moment(date,"DD/MM/YYYY");
    date.add(7,'days');

    return date.format("dddd, DD MMM");
}

var hideCallYouText = function () {
    var manually = document.getElementById('callburn-snippet-content');
    var callYouLetter = document.getElementById('call-you-letter');

    if (manually && callYouLetter) {
        if (parseInt(manually.getAttribute('manually-schedulation'))) {
            callYouLetter.classList.add('hidden');
        } else {
            callYouLetter.classList.remove('hidden');
        }
    }
}

function mainType (hidden) {
    if (hidden) {
        classHidden = 'hidden';
        idWithStyles = 'hideButton';
    } else {
        classHidden = '';
        idWithStyles = 'hideButton2';
    }
    window.dispatchEvent(new Event('resize'));
    return '<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet"><div id="callburn-snippet-content" class="snippet-bordered-box snippet-centering snippet-text-color ' + classHidden + '"> <div> <span class="br_box"><br><div id="' + idWithStyles + '" onclick="hide()"><img id="closeImg" onmouseover="imgHoverOn(\'closeImg\', \'ExitBlue\')" onmouseout="imgHoverOut(\'closeImg\', \'ExitWhite\')" src="'+imagePath+'/images/service_icons/ExitWhite.svg"></div></span> <img src="'+snippetLogo+'" class="snippet-img-centering"> </div> <br> <div id="termsAndConditionsHolder">  <div id="frontDivOFMain" class=""> <div class="snippet-font-Raleway" id="snippet-custom-text">   '+ snippetContent +'   </div><div id="snippet-scheduled-content" data-is-scheduleated="1"></div><div class=""> <br> <div id="snippet-call-status" class="snippet-text-center"> <p></p></div> <div class="snippet-form"> <div class="inline-element"> <div id="DropdownOpen" class="dropdown-content"></div><div class="left"> <span class="dropbtn-clicked"> <span class="dropdown dropbtn-clicked" > <span onclick="openSelect('+"'DropdownOpen'" + ')" class="dropbtn dropbtn-clicked" id="countries-dropdown-selected"> <img src="'+imagePath+'/images/flags/it.svg" class="selected-image dropbtn-clicked" alt=""> <span id="selected-text" class="snippet-text-color dropbtn-clicked"> </span> </span> </span> </span> </div><holder id="snippet-phonenumber-holder"> <input id="snippet-phonenumber" class="right" placeholder="{{trans('main.snippet.insert_your_phonenumber')}}"> </holder> </div></div></div><div id="clickToCallButtonDiv"> <button id="clickToCallButton" data-schedule="0" class="snippet-btn snippet-btn-block snippet-btn-success snippet-min_height_30">{{trans('main.snippet.call_me_now')}}</button> </div> <div class="snippet-text-center"> <p class="snippet-small snippet-font-Raleway snippet-font-size-10px">{{trans('main.snippet.by_clicking_above_button_you_agree_to_the')}} <span id="termsAndConditionsButton" onclick="termsAndConditions()" class="snippet-blue"> {{trans('main.snippet.terms__conditions')}}</span></p></div><p id="snippetFooterText" class="snippet-text-center snippet-font-Raleway snippet-font-size-12px">{{trans('main.snippet.cannot_answer_now')}} <br> {{trans('main.snippet.try_to')}} <span id="turnToScheduleButton" onclick="turnToSchedule()" href="" class="snippet-blue">{{trans('main.snippet.schedule_this_call_later')}} </span></p> </div> <div id="termsAndConditionsBox" class="hide-none"> <div class="snippet-text-center snippet-font-Raleway"><strong>{{trans('main.snippet.terms__conditions')}}</strong></div><div class="snippet-scrolling snippet-text-center" id="snippet-scroll"><p class="snippet-font-Raleway snippet-font-size-14px">{{trans('main.snippet.terms_and_conditions_text')}}</p></div><br><div><div class="snippet-text-center"><button onclick="termsAndConditions()" class="snippet-btn snippet-btn-block snippet-btn-success snippet-min_height_30">{{trans('main.snippet.ok')}}</button></div></div> </div> </div> <div class="snippet-pull-right "> <p class="snippet-font-Raleway snippet-font-size-14px">{{trans('main.snippet.powered_with_by')}} <a href="https://callburn.com/" target="_blank"> <img src="'+imagePath+'/images/call-burn-l-o-g-o.svg"></a>     </p></div><div class="snippet-clear-fix"></div></div>';
}

function semiOpenType () {
    return '<div id="semiOpenTypeDiv" class="snippet snippet-sm"><div class="snippet-logo"><img src="'+snippetLogo+'" class="snippet-img-centering"></div><a onclick="openSemiOpen()" id="semiOpenButton">&#60;&#60;</a><p class="text-center">Want to talk with us?</p></div>';
}

function closedType () {
    return '<div id="closedTypeDiv"  onclick="openClosed()" class="snippet snippet-xs"><div class="snippet-logo"><img src="'+snippetLogo+'" class="snippet-img-centering"></div><div class="snippet-popover">{{trans('main.snippet.want_to_talk_with_us')}}</div></div>';    
}

function scheduleMainType (param) {
    if (param) {
        backButtonHTML = '<div id="backButton" onclick="turnToSchedule(1)"><img id="backImg" onmouseover="imgHoverOn(\'backImg\', \'BackBlue\')" onmouseout="imgHoverOut(\'backImg\', \'BackWhite\')" src="' + imagePath + '/images/service_icons/BackWhite.svg"></div>';
    } else {
        backButtonHTML = '';
    }
    return backButtonHTML + '<div class="snippet-text-center"> <div> <div class="snippet-centering snippet-width-360px"><div class="snippet-select-a-date-block"><p class="snippet-select-a-date  snippet-font-Raleway">{{trans("snippet.select_a_date_from_available_ones")}}</p></div><br> <span class="snippet-text-center snippet-font-Raleway"> <div class="snippet-line-height-34px snippet-time"> <span for="phone_number" class="snippet-float-left snippet-vertical-align">{{trans('main.snippet.call_me')}} </span> <div class=" dropbtn-clicked snippet-input-group-addon snippet-form-control snippet-width-35 snippet-width-85px snippet-float-left snippet-vertical-align snippet-margin-horiz-2px snippet-display-inline"> <div class="dropdown dropbtn-clicked"> <div onclick="openSelect('+ " 'DropdownOpenWeekdays' " +')" class="dropbtn dropbtn-clicked" id="weekdays-dropdown-selected"> <span id="selected-text-weekdays" class="snippet-text-color dropbtn-clicked"></span> </div><div id="DropdownOpenWeekdays" class="dropdown-content"> </div></div></div><span for="">{{trans("snippet.at")}}</span> <div class="hour dropbtn-clicked " onclick="openSelect('+ " 'DropdownOpenHours' " +')" id="hour-dropdown-selected"> <span id="selectet-current-hour" class="front_weekday dropbtn-clicked">  </span> <div id="DropdownOpenHours" class="dropdown-content-hour-min dropdown-content2"></div></div><span for=""> : </span> <div class="hour dropbtn-clicked" onclick="openSelect('+ " 'DropdownOpenMinutes' " +')" id="minute-dropdown-selected"> <span id="selectet-current-minute" class="front_weekday dropbtn-clicked">  </span> <div id="DropdownOpenMinutes" class="dropdown-content-hour-min dropdown-content2"></div></div></div></span> </div><br><span class="snippet-text-center"> <p class="snippet-small snippet-font-Raleway snippet-font-size-12px snippet-clear-fix">{{trans('main.snippet.at_this_moment_your_time_should_be')}} <span id="real-time"></span></span><br>{{trans('main.snippet.based_on_your_timezone_gmt')}} <span id="timezone-offset"></span><br>{{trans('main.snippet.isnt_correct')}} <a onclick="openSelect('+ " 'DropdownOpenTimezones' " +')" class="snippet-blue dropbtn-clicked">{{trans('main.snippet.click_here_to_change')}}</a><div id="DropdownOpenTimezones" class="dropdown-content1"> </div></p></span> <br></div></div>';
}

function termsAndConditions () {
    document.getElementById('frontDivOFMain').classList.toggle('hide-none') ;
    document.getElementById('termsAndConditionsBox').classList.toggle('hide-none') ;
}

function hide () {
    document.getElementById('callburn-snippet-content').classList.add('hidden');
    if (document.getElementById('semiOpenTypeDiv')) {
        document.getElementById('semiOpenTypeDiv').classList.remove('hidden');
    } else if (document.getElementById('closedTypeDiv')) {
        document.getElementById('closedTypeDiv').classList.remove('hidden');
    }
}

if (snippetType == 1) {
    div.innerHTML = mainType();
    window.dispatchEvent(new Event('resize'));
} else if (snippetType == 2) {
    div.innerHTML += semiOpenType() + mainType(1);
    function openSemiOpen () {
        document.getElementById('callburn-snippet-content').classList.remove('hidden');
        document.getElementById('semiOpenTypeDiv').classList.add('hidden');
        window.dispatchEvent(new Event('resize'));
    }
    window.dispatchEvent(new Event('resize'));
} else if (snippetType == 3) {
    div.innerHTML += closedType() + mainType(1);
    function openClosed () {
        document.getElementById('callburn-snippet-content').classList.remove('hidden');
        document.getElementById('closedTypeDiv').classList.add('hidden');
        window.dispatchEvent(new Event('resize'));
    }
    window.dispatchEvent(new Event('resize'));
}

var turnToScheduleParam = false;

function turnToSchedule (param) {
    if (param) {
        document.getElementById('snippet-scheduled-content').setAttribute('data-is-scheduleated',0);
        document.getElementById('callburn-snippet-content').setAttribute('manually-schedulation',0);
        turnToScheduleParam = false;
    } else {
        document.getElementById('snippet-scheduled-content').setAttribute('data-is-scheduleated',1);
        document.getElementById('callburn-snippet-content').setAttribute('manually-schedulation',1);
        turnToScheduleParam = true;
    }
    checkSnippetTime();
}

var scheduleDiv = scheduleMainType();

body.append(div);

var images = document.querySelectorAll('#callburn-snippet-content img, #semiOpenTypeDiv img, #closedTypeDiv img');

var script = document.createElement('script');
script.onload = function() {

};
script.src = {!!  "'".config('snippet.socket_url')."socket.io/socket.io.js'"!!};

function imgHoverOn(id, imgName) {
    document.getElementById(id).setAttribute('src', imagePath + '/images/service_icons/' + imgName + '.svg');
}

function imgHoverOut(id, imgName) {
    document.getElementById(id).setAttribute('src', imagePath + '/images/service_icons/' + imgName + '.svg');
}

document.getElementsByTagName('head')[0].appendChild(script);

window.onclick = function(event) {
    if (event.target.className.indexOf('dropbtn-clicked') == -1) {
        var dropdowns = document.querySelectorAll('.dropdown-content, .dropdown-content2, .dropdown-content1')
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
};

function openSelect(id) {
    document.getElementById(id).classList.toggle("show");
}

var selectedCountry = document.getElementById('countries-dropdown-selected');

selectedCountry.getElementsByTagName('span')[0].innerText = '(+' + countries[0].phonenumber_prefix + ')';
selectedCountry.getElementsByTagName('span')[0].setAttribute("data-phonenumber", countries[0].phonenumber_prefix);
selectedCountry.getElementsByTagName('img')[0].setAttribute('src', baseUrl + '/assets/callburn/images/lang-flags/' + countries[0].code + '.svg');

var dropdownContent = document.getElementById('DropdownOpen');

countries.forEach(function (item) {
    var div = document.createElement('div');
    var img = document.createElement('img');
    img.setAttribute('src', baseUrl + '/assets/callburn/images/lang-flags/' + item.code + '.svg');
    img.className += " dropdown-image";
    var span = document.createElement('span');
    span.innerText = '(+' + item.phonenumber_prefix + ')';
    span.setAttribute("data-phonenumber", item.phonenumber_prefix);
    span.className += " dropdown-text";
    div.className += " country-name";
    div.append(img);
    div.append(span);
    dropdownContent.append(div);

    div.addEventListener('click', function (event) {
        selectedCountry.getElementsByTagName('span')[0].innerText = this.getElementsByTagName('span')[0].innerText;
        selectedCountry.getElementsByTagName('span')[0].setAttribute("data-phonenumber", this.getElementsByTagName('span')[0].getAttribute('data-phonenumber'));
        selectedCountry.getElementsByTagName('img')[0].setAttribute('src',  this.getElementsByTagName('img')[0].src);
    });
});

var cloneSnipet = Object.assign({}, window.snippet);

var weekDays = Object.keys(cloneSnipet.allowed_date_times);
weekDays = changeWeekDaysArrayFormat(weekDays);

// This is function checking value matches in array
var checkInArray = function (array, id) {
    return (array.indexOf(id) > -1) ? true : false;
};

var makeInterval = function (start, end, step=1) {
    start = parseInt(start);
    end   = parseInt(end);

    if (start > end) {
        var item = start;
        start = end;
        end = item;
    }

    var interval = [];

    for (var i = start; i <= end; i += step) {
        interval.push(i<10?"0"+i:""+i+"");
    }

    return interval;
}

var getHoursAndMinutes = function (currentDay) {
    return new Promise(function (resolve, reject) {
        var hours = [];
        try {
            currentDay.forEach(function(item) {
                var startHour = item.start.split(':')[0];
                var endHour   = item.end.split(':')[0];
                var startMin = item.start.split(':')[1];
                var endMin   = item.end.split(':')[1];
                var interval = [];
                interval = interval.concat(makeInterval(startHour,endHour));
                if (interval.length > 1) {
                    for (var i = 0; i < interval.length; i++ ) {
                        var minObject = {};
                        if (i == 0) {
                          minObject[interval[i]] = {
                              from : parseInt(startMin),
                              to   : 59
                          };
                        } else if (i == interval.length - 1) {
                          minObject[interval[i]] = {
                              from : 0,
                              to   : parseInt(endMin)
                          };
                        } else {
                          minObject[interval[i]] = {
                              from : 0,
                              to   : 59
                          };
                        }
                        interval[i] = minObject;
                    }
                } else {
                    var minObject = {};
                    minObject[interval[0]] = {
                        from : parseInt(startMin),
                        to   : parseInt(endMin)
                    };
                    interval[0] = minObject;
                }
                hours = hours.concat(interval);
            });
        } catch (e) {
             document.getElementById('selected-text-weekdays').innerText = '';
             document.getElementById('clickToCallButton').setAttribute('disabled',1);
        }

        resolve(hours);
    });
}

var pSuccess1Html  = document.getElementById('information-text-1').innerHTML;
var pSuccess2Html  = document.getElementById('information-text-2').innerHTML;
var pFooterHtml  = document.getElementById('snippetFooterText').innerHTML;

var checkSnippetDateRange = function () {
    return  new Promise(function (resolve, reject) {
        var request = new XMLHttpRequest;
        var data = {};
        var userOffset = localStorage.getItem('user-offset');
        var offset = document.getElementById('timezone-offset')?document.getElementById('timezone-offset').innerText:userOffset;
        data.offset = parseFloat(offset);
        data.token = token;
        request.onreadystatechange = function () {
            if (4 == this.readyState && 200 == this.status) {
                var response = JSON.parse(request.responseText);
                if (response.resource.error.no == 0) {
                    check = response.resource.check;
                    resolve(check);
                }
            }
        };
        request.open("POST", baseUrl + "/ctc/check-snippet-date-range/" + token);
        request.setRequestHeader('Content-type', 'application/json; charset=utf-8');

        request.send(JSON.stringify(data));
    });
}

var updateMinutes = function (weekDayDataString, currentHour) {
    var momentEn = moment(weekDayDataString, "DD/MM/YYYY");
    momentEn.locale('en');
    var weekDay = momentEn.format('dddd');
    var currentDay = getDayTimes(weekDayDataString);

    getHoursAndMinutes(currentDay).then(function (data) {
        var hours = data;
        var currentMinutes;

        var selectedMinutesContent = document.getElementById('DropdownOpenMinutes');
        document.getElementById('DropdownOpenMinutes').innerHTML = "";

        for (var i = 0; i < hours.length; i++) {
            var hour = Object.keys(hours[i])[0];
            if (hour == currentHour) {
                currentMinutes = hours[i][hour];
            }
        }

        try {
            var minutes = makeInterval(currentMinutes.from,currentMinutes.to,5);
            for (var i = 0; i < minutes.length; i++) {
                var div3 = document.createElement('div');
                div3.className += " minute-name"
                var span3 = document.createElement('span');
                span3.className += " dropdown-text-hour";
                span3.innerText = minutes[i];

                div3.append(span3);
                selectedMinutesContent.append(div3);

                div3.addEventListener('click', function (event) {
                    document.getElementById('minute-dropdown-selected').getElementsByTagName('span')[0].innerText = this.getElementsByTagName('span')[0].innerText;
                });
            }
            document.getElementById('selectet-current-minute').innerText = minutes[0];
        } catch(e) {
            document.getElementById('selected-text-weekdays').innerText = '';
            document.getElementById('clickToCallButton').setAttribute('disabled',1);
        }
    });
}

var updateHours = function(weekDayDataString) {
    var momentEn = moment(weekDayDataString, "DD/MM/YYYY");
    momentEn.locale('en');
    var weekDay = momentEn.format('dddd');
    return new Promise(function(resolve, reject) {
        var currentDay = getDayTimes(weekDayDataString);
        getHoursAndMinutes(currentDay).then(function (data) {
            var hours = data;
            var selectedHoursContent = document.getElementById('DropdownOpenHours');
            document.getElementById('DropdownOpenHours').innerHTML = "";
            for (var i = 0; i < hours.length; i++) {
                var hour = Object.keys(hours[i])[0];
                var div2 = document.createElement('div');
                div2.className += " hour-name"
                var span2 = document.createElement('span');
                span2.className += " dropdown-text-hour";
                span2.innerText = hour;
                div2.append(span2);
                selectedHoursContent.append(div2);
                div2.addEventListener('click', function (event) {
                    var hour =  this.getElementsByTagName('span')[0].innerHTML.trim();
                    document.getElementById('hour-dropdown-selected').getElementsByTagName('span')[0].innerText = hour;
                    updateMinutes(weekDayDataString,hour);
                });
            }

            try {
                document.getElementById('selectet-current-hour').innerText = Object.keys(hours[0])[0];
            } catch(e) {

            }
            resolve();
        });
    });
}

var updateWeekdays = function (weekDays) {
    document.getElementById('DropdownOpenWeekdays').innerHTML = "";
    var selectedWeekdaysContent = document.getElementById('DropdownOpenWeekdays');

    var selectedHoursContent = document.getElementById('DropdownOpenHours');
    var selectedMinutesContent = document.getElementById('DropdownOpenMinutes');

    var localLang =  (navigator.language || navigator.userLanguage).trim();
    var year = moment().format("YYYY");
    try {
        var orderedWeekDays = changeWeekDaysArrayFormat(weekDays, true);
        orderedWeekDays.forEach(function (item) {
            var div = document.createElement('div');
            div.className += " weekday-name";
            var span = document.createElement('span');
            span.className += " dropdown-text-weekday";
            var dayNumber = getDayIndexByMoment(item);
            span.innerText = moment(item, "dddd, DD MMM").year(year).locale(localLang).format("dddd, DD MMM");
            span.setAttribute('english-name',moment(item, "dddd, DD MMM").year(year).locale('en').format('dddd'));
            span.setAttribute('full-date',moment(item, "dddd, DD MMM").year(year).format('DD/MM/YYYY'));
            div.append(span);
            selectedWeekdaysContent.append(div);
            div.addEventListener('click', function (event) {
                var currentWeekDay = this.getElementsByTagName('span')[0].getAttribute('english-name').trim();
                var dayNumber = getDayIndexByMoment(item);
                document.getElementById('weekdays-dropdown-selected').getElementsByTagName('span')[0].innerText =  this.innerText;
                document.getElementById('selected-text-weekdays').setAttribute('english-weekday-name',moment(item, "dddd, DD MMM").locale('en').format('dddd'));
                var fullDate = this.getElementsByTagName('span')[0].getAttribute('full-date').trim();
                document.getElementById('selected-text-weekdays').setAttribute('full-date',fullDate);
                updateHours(fullDate).then(function () {
                    var hour = document.getElementById('selectet-current-hour').innerHTML.trim();
                    updateMinutes(fullDate,hour);
                    var currentDay = getDayTimes(fullDate);
                });
            });
        });
        var firtsDate = document.getElementsByClassName('dropdown-text-weekday')[0];
        document.getElementById('selected-text-weekdays').innerText = firtsDate.innerText;
    } catch(e) {
        document.getElementById('selected-text-weekdays').innerText = '';
        document.getElementById('clickToCallButton').setAttribute('disabled',1);
    }
}

var timeZones = moment.tz.names();
var offsetTmz = [];

var getUserTimezone = function(userOffset) {
    if (userOffset === undefined) {
        userOffset = localStorage.getItem('user-offset');
    }

    var timeZones = moment.tz.names();
    for (var i in timeZones) {
        var offset = moment.tz(timeZones[i]).utcOffset()/60;
        if (offset == userOffset) {
            return timeZones[i];
        }
    }
}

for (var i in timeZones) {
    var offset = moment.tz(timeZones[i]).utcOffset()/60;
    if( offsetTmz.indexOf(offset) == -1) {
        offsetTmz.push(offset);
    }
}

offsetTmz.sort(function(a, b) { return a - b;})

var choiceTimezone = function (TimezoneOffset) {
    var request = new XMLHttpRequest;
    request.onreadystatechange = function () {
        if (4 == this.readyState && 200 == this.status) {
            var response = JSON.parse(request.responseText);
            window.snippet.allowed_date_times = response.updatedDate;

            checkSnippetTime(true);
        }
    };
    request.open("POST", baseUrl + "/main-js/" + token + "/" + TimezoneOffset);
    request.setRequestHeader('Content-type', 'application/json; charset=utf-8');
    var data = JSON.stringify({
      changeTimezone: true,
      token:token,
    });
    request.send(data);
}

var getTime = function() {
    if (!window.userTZ) {
        tz = getUserTimezone();
    } else {
         tz = window.userTZ;
    }

    var time = moment().tz(tz).format('HH:mm');
    if (document.getElementById('real-time')) {
        document.getElementById('real-time').innerText = time;
    }
}

var checkSnippetTime = function (timezoneUpdate=false) {
    window.pageIsLoaded = true;
    var weekDays = Object.keys(window.snippet.allowed_date_times);
    weekDays = changeWeekDaysArrayFormat(weekDays);

    if (timezoneUpdate) {
        updateWeekdays(weekDays);
        var firtsDate = document.getElementsByClassName('dropdown-text-weekday')[0];
        var fullDate = firtsDate.getAttribute('full-date')
    
        updateHours(fullDate).then(function () {
            var hour = document.getElementById('selectet-current-hour').innerHTML.trim();
            updateMinutes(fullDate,hour);
        });
        return;
    }

    checkSnippetDateRange().then(function (check) {
        window.dispatchEvent(new Event('resize'));

        if (check && !turnToScheduleParam) {
            var button = document.getElementById('clickToCallButton');
            var p1 = document.getElementById('information-text-1');
            var p2 = document.getElementById('information-text-2');
            var pFooter = document.getElementById('snippetFooterText');
            document.getElementById('snippet-scheduled-content').innerHTML = '';

            button.classList.remove("snippet-btn-warning");
            button.classList.add('snippet-btn-success');
            button.innerText = '{{trans("snippet.call_me_now")}}';
            button.setAttribute("data-schedule", 0);

            p1.innerHTML = pSuccess1Html;
            p2.innerHTML = pSuccess2Html;
            pFooter.innerHTML = pFooterHtml;
            hideCallYouText();
        } else if (document.getElementById('clickToCallButton').getAttribute('data-schedule') == 0 || turnToScheduleParam) {
            if (parseInt(document.getElementById('snippet-scheduled-content').getAttribute('data-is-scheduleated'))) {
                if (turnToScheduleParam) {
                    document.getElementById('snippet-scheduled-content').innerHTML = scheduleMainType(1);
                } else {
                    document.getElementById('snippet-scheduled-content').innerHTML = scheduleDiv;
                }
                document.getElementById('snippet-scheduled-content').setAttribute('data-is-scheduleated',0);
            }

            var userOffset = parseInt(localStorage.getItem('user-offset'));

            var timezoneOffset = document.getElementById('timezone-offset');

            if (timezoneOffset) {
             timezoneOffset.innerText= (userOffset < 0?userOffset:("+" + userOffset));
            }

            var selectedWeekdaysContent = document.getElementById('DropdownOpenWeekdays');

            var selectedHoursContent = document.getElementById('DropdownOpenHours');
            var selectedMinutesContent = document.getElementById('DropdownOpenMinutes');

            updateWeekdays(weekDays);

            var button = document.getElementById('clickToCallButton');
            button.classList.remove('snippet-btn-success');
            button.classList.add("snippet-btn-warning");
            button.innerText = '{!!trans("snippet.schedule_a_call_later")!!}';
            button.setAttribute("data-schedule", 1);

            var p1 = document.getElementById('information-text-1');
            var p2 = document.getElementById('information-text-2');
            var pFooter = document.getElementById('snippetFooterText');

            p1.innerHTML = '{{trans('main.snippet.want_to_talk_with_us')}}<br><span id="call-you-letter">{{trans('main.snippet.we_are_not_available_now')}}<br>{{trans('main.snippet.but_we_can_call_you')}}<strong>&nbsp;{{trans('main.snippet.later')}}</strong></span> ';
            hideCallYouText();

            p2.innerHTML = '';

            pFooter.innerHTML = '';

            var dayNumber = getDayIndexByMoment(weekDays[0]);

            var selectedTextWeekdays = document.getElementById('selected-text-weekdays');
            var firstWeekDay = document.getElementsByClassName('dropdown-text-weekday')[0];

          
            if (selectedTextWeekdays) {
                selectedTextWeekdays.innerText = firstWeekDay.innerText;
                selectedTextWeekdays.setAttribute('english-weekday-name',weekDays[0]);
                var year = moment().format("YYYY");
                var fullDate = moment(getNextWeekDay(dayNumber),'dddd, DD MMM').year(year).format("DD/MM/YYYY");
                selectedTextWeekdays.setAttribute('full-date',fullDate);
            }

            updateHours(firstWeekDay.getAttribute('full-date')).then(function () {
                var hour = document.getElementById('selectet-current-hour').innerHTML.trim();
                updateMinutes(firstWeekDay.getAttribute('full-date'),hour);

                offsetTmz.forEach(function (item) {
                    var div = document.createElement('div');
                    div.className += " timezones-name";
                    var span = document.createElement('span');
                    span.className += " dropdown-text-timezone";
                    span.innerText = "GMT " + (item < 0?item:("+" + item));
                    div.setAttribute("data-offset", item);
                    div.append(span);
                    div.addEventListener('click', function() {
                        var offset = parseFloat(this.getAttribute('data-offset'));
                        localStorage.setItem('user-offset', offset);
                        document.getElementById('timezone-offset').innerText = (offset < 0?offset:("+" + offset));
                        window.userTZ = getUserTimezone(offset);
                        offset = -offset;
                        choiceTimezone(offset);
                    });
                    document.getElementById('DropdownOpenTimezones').append(div);
                });
            });

            setInterval(getTime,500);
        }
    });
}

checkSnippetTime();
setInterval (function () {
    if (!window.calling) {
        checkSnippetTime();
    }
},30000);

/*-----------------------------------------------------------------------------------------------------------------*/

var buttonDiv = document.getElementById('clickToCallButtonDiv');
var snippetPhonenumberHolder = document.getElementById('snippet-phonenumber-holder');
var snippetCallStatus = document.getElementById('snippet-call-status');

/*CALLING*/
function updateStatusCalling (number) {
    window.calling = true;
    document.getElementById('snippet-call-status').innerHTML = '<p> </p>';
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber"  value="' + number + '" class="right" disabled>';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" data-schedule="0" class="snippet-btn snippet-btn-block snippet-min_height_30" disabled>{{trans('main.snippet.making_connection')}}</button>';
    document.getElementById('turnToScheduleButton')
}

/*RINGING INSTANTLY*/
function updateStatusRingingInstantly (number) {
    window.calling = true;
    document.getElementById('snippet-call-status').innerHTML = '<p> </p>';
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber"  value="' + number + '" class="right" disabled>';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" data-schedule="0" class="snippet-btn snippet-btn-block snippet-min_height_30" disabled>{{trans('main.snippet.your_phone_should_be_ring_istantly')}}</button>';
}

/*CONNECTED*/
function updateStatusConnected (number) {
    window.calling = true;
    document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-blue snippet-font-Raleway">{{trans('main.snippet.connected_to')}}</p>';
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber"  value="' + number + '" class="right" disabled>';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" data-schedule="0"  class="snippet-btn snippet-btn-block snippet-min_height_30 snippet-btn-danger" disabled>{{trans('main.snippet.connected')}}</button>';
}

/*DISCONNECTING*/
function updateStatusDisconnecting (number) {
    window.calling = true;
    document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-blue snippet-font-Raleway">{{trans('main.snippet.connection_ended')}}</p>';
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber"  value="' + number + '" class="right" disabled>';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" data-schedule="0" class="snippet-btn snippet-btn-block snippet-min_height_30 snippet-btn-danger" disabled>{{trans('main.snippet.disconnecting')}}</button>';
}

/*SUCCEED*/
function updateStatusSucceed () {
    document.getElementById('snippet-call-status').innerHTML = '<p> </p>';
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber" class="right" placeholder="{{trans('main.snippet.insert_your_phonenumber')}}">';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" onclick="submiting()" data-schedule="0" class="snippet-btn snippet-btn-block snippet-btn-success snippet-min_height_30">{{trans('main.snippet.call_me_now')}}</button>';
}

/*FAILED*/
function updateStatusFailed (number, attr, failedStatus) {
    window.calling = true;
    if (failedStatus == 'failed') {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.we_wasnt_able_to_reach_you')}}</p>';
    } else if (failedStatus == 'transfer_not_connected') {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.transfer_not_connected')}}</p>';
    }
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber"  value="' + number + '" class="right" placeholder="{{trans('main.snippet.insert_your_phonenumber')}}" disabled>';
    if (attr == 1) {
        document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" onclick="updateStatusScheduledSucceed(0)" data-schedule="1" class="snippet-btn snippet-btn-block snippet-min_height_30 snippet-btn-warning">{{trans('main.snippet.try_again')}}</button>';
    } else {
        document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" onclick="updateStatusSucceed()" data-schedule="0" class="snippet-btn snippet-btn-block snippet-min_height_30 snippet-btn-warning">{{trans('main.snippet.try_again')}}</button>';
    }
}

/*INVALID NUMBER*/
function updateStatusInvalidNumber (number, attr, errNumber) {
    window.calling = true;
    if (errNumber == -1) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.snippet_not_exists')}}</p>';
    } else if (errNumber == -2) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.referrer_not_allowed_to_send_request')}}</p>';
    } else if (errNumber == -3) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.time_is_out_of_ranges')}}</p>';
    } else if (errNumber == -4) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.phonenumber_is_not_valid_or_not_supported')}}</p>';
    } else if (errNumber == -5) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.schedulation_already_reserved')}}</p>';
    } else if (errNumber == -6) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.schedulation_daily_max_limit_expired')}}</p>';
    }
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber"  value="' + number + '" class="right" placeholder="{{trans('main.snippet.insert_your_phonenumber')}}" disabled>';
    if (attr == 1) {
        document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" onclick="updateStatusScheduledSucceed(0)" data-schedule="1" class="snippet-btn snippet-btn-block snippet-min_height_30 snippet-btn-warning">{{trans('main.snippet.try_again')}}</button>';
    } else {
        document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" onclick="updateStatusSucceed()" data-schedule="0" class="snippet-btn snippet-btn-block snippet-min_height_30 snippet-btn-warning">{{trans('main.snippet.try_again')}}</button>';
    }
}

/*SCHEDULED_DISCONNECTING*/
function updateStatusScheduledDisconnecting (number) {
    window.calling = true;
    document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-blue snippet-font-Raleway">{{trans('main.snippet.schedulation_is_in_process')}}</p>';
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber" value="' + number + '" class="snippet-form-control snippet-widt-145 " placeholder="     {{trans('main.snippet.your_phonenumber')}}" disabled>';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" data-schedule="1" class="snippet-btn snippet-btn-block snippet-min_height_30" disabled>{{trans('main.snippet.disconnecting')}}</button>';
}

/*SCHEDULED_SUCCEED*/
function updateStatusScheduledSucceed (status) {
    if (status == 'scheduled') {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-blue snippet-font-Raleway">{{trans('main.snippet.scheduled')}}</p>';
        setTimeout(function() {
            document.getElementById('snippet-call-status').innerHTML = '<p> </p>';
        }, 2000);
    } else {
        document.getElementById('snippet-call-status').innerHTML = '<p> </p>';
    }
    document.getElementById('snippet-phonenumber-holder').innerHTML = '<input id="snippet-phonenumber" class="right" placeholder="{{trans('main.snippet.insert_your_phonenumber')}}">';
    document.getElementById('clickToCallButtonDiv').innerHTML = '<button id="clickToCallButton" onclick="submiting()" data-schedule="1" class="snippet-btn snippet-btn-block snippet-btn-warning snippet-min_height_30">{!!trans('main.snippet.schedule_a_call_later')!!}</button>';
}

function submiting () {
    var submit = document.getElementById('clickToCallButton');

    if (!document.getElementById('snippet-phonenumber').value) {
        document.getElementById('snippet-call-status').innerHTML = '<p class="snippet-text-red snippet-font-Raleway">{{trans('main.snippet.please_enter_phonenumber')}}</p>';
        return;
    }
    var snippetPhonenumberValue = document.getElementById('snippet-phonenumber').value;
    updateStatusCalling(snippetPhonenumberValue);
    var data = {};
    data.token = token;
    data.recipient = document.getElementById('selected-text').getAttribute('data-phonenumber') + document.getElementById('snippet-phonenumber').value;
    if (document.getElementById('timezone-offset')) {
        var offset = document.getElementById('timezone-offset').innerText;
        data.offset = parseFloat(offset);
    }

    if (parseInt(submit.getAttribute('data-schedule'))) {
        data.callburn_week_day = document.getElementById('selected-text-weekdays').getAttribute('english-weekday-name').trim();
        data.callburn_hour = parseInt(document.getElementById("selectet-current-hour").innerHTML);
        data.callburn_minute = parseInt(document.getElementById("selectet-current-minute").innerHTML);
        data.full_date = document.getElementById("selected-text-weekdays").getAttribute('full-date');
        data.status = 'scheduled';
    } else {
        data.status = 'saved';
         var offset = -(new Date().getTimezoneOffset()/60);
         data.offset = offset,
         data.full_date = moment().format("DD/MM/YYYY")
    }

    if (local) {
        data.site_language = site_language;
    }

    var request = new XMLHttpRequest();

    request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(request.responseText);
            if (response.resource.error.no == 0) {
                var phonenumberId = response.resource.phonenumber_id;
                if (parseInt(submit.getAttribute('data-schedule')) == 0) {
                    updateStatusRingingInstantly(snippetPhonenumberValue);
                    updateSocket(phonenumberId, snippetPhonenumberValue);
                } else {
                    updateStatusScheduledDisconnecting(snippetPhonenumberValue);
                    setTimeout(function() {
                        updateStatusScheduledSucceed('scheduled');
                    }, 2000);
                }
            } else {
                attr = parseInt(submit.getAttribute('data-schedule')) == 0 ? 0 : 1;
                if (response.resource.error.no == -1) {
                    updateStatusInvalidNumber(snippetPhonenumberValue, attr, response.resource.error.no);
                } else if (response.resource.error.no == -2) {
                    updateStatusInvalidNumber(snippetPhonenumberValue, attr, response.resource.error.no);
                } else if (response.resource.error.no == -3) {
                    updateStatusInvalidNumber(snippetPhonenumberValue, attr, response.resource.error.no);
                } else if (response.resource.error.no == -4) {
                    updateStatusInvalidNumber(snippetPhonenumberValue, attr, response.resource.error.no);
                } else if (response.resource.error.no == -5) {
                    updateStatusInvalidNumber(snippetPhonenumberValue, attr, response.resource.error.no);
                } else if (response.resource.error.no == -6) {
                    updateStatusInvalidNumber(snippetPhonenumberValue, attr, response.resource.error.no);
                }
            }
        }
    };
    request.open('POST', baseUrl + '/ctc/create-message');
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify(data));
};

document.getElementById('clickToCallButton').addEventListener('click', submiting);

/********************************************************/
function updateSocket(phonenumberId, snippetPhonenumberValue) {
    try {
        var socket = io({!!  "'".config('snippet.socket_url')."'"!!});
        socket.on('update-phonenumber-status:' + phonenumberId , function(data) {
            if (data.update.status == 'connected') {
                updateStatusConnected(snippetPhonenumberValue);
            } else if (data.update.status == 'transfer_not_connected') {
                updateStatusFailed(snippetPhonenumberValue, 0, data.update.status);
            }
            if (data.update.status == 'succeed') {
                updateStatusDisconnecting(snippetPhonenumberValue);
                setTimeout(function() {
                    updateStatusSucceed();
                }, 2000);
            } else if (data.update.status == 'failed') {
                updateStatusFailed(snippetPhonenumberValue);
            }
        });
    } catch(err) {

    }
}

document.addEventListener('keypress', function (e) {
       if (e.keyCode == 13 && document.getElementById('callburn-snippet-content').classList.value.indexOf('hidden') == -1) {
         document.getElementById('clickToCallButton').click();
    }
})

window.loaded = true;
