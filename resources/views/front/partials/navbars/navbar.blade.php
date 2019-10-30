<nav class="navbar navbar-index navbar-toggleable-sm" id="navbarDinamic">
    <div class="container-fluid d-flex flex-row justify-content-center align-items-center w-100 m-0 p-0 flex-lg-row">
        <a class="navbar-brand ml-0 mr-auto text-center text-sm-left ml-lg-0" href="/">
            <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" width="150" height="40" alt="">
        </a>

        <ul class="navbar-nav mr-0 mr-lg-0 text-center">
            <div class="d-flex flex-row justify-content-center align-items-center">
                <li class="phonenumber_holder mr-1 mr-sm-0 nav-item text-muted hidden-xs-down">
                    <a ng-click="openCTCSnippet()" class="phonenumber text-center">
                        <span class="phonenumber_number">{{ trans('main.welcome.phonenumbers') }}</span>
                        <span class="phonenumber_ctc">(ClickToCall)</span>
                    </a>
                </li>   
                <li class="active lang nav-item mt-1 mb-1 ml-2">
                    <select class="form-control choose_lang">
                        @foreach ($languages as $lang)
                        <option {{\App::getLocale() == $lang->code ? 'selected' : ''}} value="{{ $lang->code }}">{{ $lang->full_name }}</option>
                        @endforeach
                    </select>
                </li>
                <li class="nav-item ml-2 hidden-sm-down">
                    <button ng-click="checkAuth('register')" class="font-weight-bold btn btn-reg pointer">{{trans('main.welcome.register')}}</button>
                </li>
                <li class="nav-item ml-2 mr-sm-0 hidden-sm-down">
                    <button ng-click="checkAuth('login')" class="font-weight-bold btn btn-log pointer">{{trans('main.welcome.login')}}</button>
                </li>
                <li class="nav-item hidden-md-up">
                    <div class="bar pointer" ng-click="toggleBar = toggleBar ? false : true" ng-class="{opened:toggleBar}">
                        <div class="bar_inner"></div>
                        <div class="bar_inner"></div>
                        <div class="bar_inner"></div>
                    </div>
                </li>
            </div>
        </ul>
    </div>
</nav>
<!--
<button type="button" class="hidden-up menu_bar pointer @{{menuClass ? 'active' : ''}}" ng-click="menuClass = !menuClass" >
    <span class="menu_bar_inner"></span>
    <span class="menu_bar_inner"></span>
    <span class="menu_bar_inner"></span>
</button>
<nav class="navbar_menu navbar navbar-toggleable-sm pt-0">
    <ul class="navbar-nav menu_toggle d-flex flex-column justify-content-start align-items-start flex-md-row justify-content-md-center align-items-md-center pt-0 @{{menuClass ? 'active' : ''}}">
        @if(config('app.voice_messages'))
        <li class="{{$tab == 'voice-message'?'active':''}} nav-item m-0">
            <a href="/voice-message" class="nav-link p-0">{{trans('main.welcome.voice_messages')}}</a>
            <div class="bot_line"></div>
        </li>
        @endif
        <li class="{{$tab == 'click-to-call'?'active':''}} nav-item m-0 m-sm-1">
            <a href="/click-to-call" class="nav-link p-0">{{trans('main.welcome.ClickToCall')}}</a>
            <div class="bot_line"></div>
        </li>
        <li class="nav-item m-0 m-sm-1">
            <a href="/developers" class="nav-link p-0">{{trans('main.welcome.developers')}}</a>
            <div class="bot_line"></div>
        </li>
        <li class="{{$tab == 'doc'?'active':''}} nav-item m-0 m-sm-1">
            <a href="/documentation" class="nav-link p-0">{{trans('main.welcome.docs')}}</a>
            <div class="bot_line"></div>
        </li>
        <li class="{{$tab == 'contact-us'?'active':''}} nav-item m-0 m-sm-1">
            <a href="/contact-us" class="nav-link p-0">{{trans('main.welcome.contact_us')}}</a>
            <div class="bot_line"></div>
        </li>
    </ul>
</nav> -->