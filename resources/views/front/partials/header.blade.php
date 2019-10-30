<!DOCTYPE html>
<html lang="en" ><head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-KXMQPQV');</script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <!-- End Google Tag Manager -->
   
    <!-- Crisp -->
    <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="e4ef3e4c-3291-431d-bdfc-eef78a98190f";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
    <!-- End of Crisp -->
    <script type="text/javascript">
    $crisp.push(["on", "session:loaded", function() {
        // Set as Adwords visitor when GCLID isset
        if(($crisp.get("session:data", "adwords") === null) && (window.location.href.indexOf("gclid") > -1)) {
            $crisp.push(["set", "session:data", [[["adwords", "visitor"]]]]);
        }
        $crisp.push(["on", "message:sent", function(message) {
            if ($crisp.get("session:data", "adwords") === "visitor") {
                $crisp.push(["set", "session:data", [[["adwords", "conversion"]]]]);
                gtag('event', 'conversion', {'send_to': 'AW-3783320323/HO-1021468702'});
            }
        }]);
    }]);
</script>
    
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="First Worldwide Callmessage Web-Platform" />
    <meta name="_token" content="{{csrf_token()}}"><meta>
    <meta name="author" content="">
    <!-- FB metatags -->
    <meta property="og:title" content="First Worldwide Callmessages Web-Platform" />
    <meta property="og:description" content="Are you still sending old SMS? - Try our Callmessages for free!" />
    <meta property="og:image" content="https://callburn.com/laravel_assets/images/logo_for_share_links.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="imavagrant upge/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="google-site-verification" content="1iZhbrM1T1sVKAguVyvOYQQagBpDsCBQ4jf-enuJ1ZA" />


    <title>Callburn @yield('title')</title>

    <link rel="stylesheet" href="{{ asset('laravel_assets/front/css/select2.css') }}">
    <link rel="stylesheet" href="{{ elixir('laravel_assets/front/css/all.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('../callburn-angular/assets/css/front.css') }}"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans" rel="stylesheet">
</head>
<body role="document" ng-app="frontCallburnApp" ng-controller="MainController" data-ng-class="{'active':menuClass}">
    <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KXMQPQV"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<!-- @{{loadCrisp()}} -->

    <!-- notifications-->
    <div growl></div>
    <!-- notifications-->
<div growl></div>