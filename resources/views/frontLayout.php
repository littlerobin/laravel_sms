<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="google-site-verification" content="1iZhbrM1T1sVKAguVyvOYQQagBpDsCBQ4jf-enuJ1ZA" />
    <meta name="keywords" content="callmessages, callmessages, clicktocall, marketing, sms alternatives, voice sms, voice messaging api, clicktocall snippets, text to audio, text to voice, text to call, send sms, send messages, replace sms, call from website, free calls, interactive website, conversion rate, increase conversion rate, get new customers, messaggi vocali, chiamate messaggi, chiamata-messaggio, messaggi vocali, alternativa sms, sms vocali, aple chiamate-messaggi, riquadri clicktocall, da testo ad audio, da testo a voce, chiamata da un testo, invio sms, invio messaggi, sostituire gli sms, chiamare da un sito, chiamata dal sito internet, sito web interattivo, sito internet interattivo, chiamate gratuite, tasso di conversione, incrementare tasso di conversione, nuovi clienti, aumentare i clienti">	
    <title>Callburn</title>
    <link rel="stylesheet" href="/dist/assets/front/css/front.css">
</head>
<body data-ng-app="frontCallburnApp" data-ng-controller="FrontController" data-ng-class="{'body-no-scroll' : showBlurEffect,'blue-background':showRegistration">
<!-- notifications-->
<div growl></div>
<!-- notifications-->
<div ng-show="showWhiteMenu && !showRegistration">
    <ng-include src="'/app/include/front/nav-all-white.html'" ng-if="showWhiteMenu && !showRegistration" ng-show="showWhiteMenu && !showRegistration"></ng-include>
</div>
<div ng-show="!showWhiteMenu && !showRegistration">
    <ng-include src="'/app/include/front/nav-all-black.html'" ng-if="!showWhiteMenu && !showRegistration" ng-show="!showWhiteMenu && !showRegistration"></ng-include>
</div>

<div ng-show="showRegistration">
    <ng-include src="'/app/include/front/registration.html'" ng-if="showRegistration"></ng-include>
</div>



<div ui-view></div>
<div ng-show="showFooter && !showRegistration">
    <ng-include src="'/app/include/front/footer.html'" ng-if="showFooter && !showRegistration" ng-show="showFooter && !showRegistration"></ng-include>
</div>

<script type="text/javascript" src="/dist/assets/front/js/front.min.js"></script>
<script type="text/javascript" src="/dist/app.js"></script>
</body>
</html>