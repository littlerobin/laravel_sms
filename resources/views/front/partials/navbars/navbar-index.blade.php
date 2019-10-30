<div class="mobile_menu hidden-md-up" ng-class="{opened:toggleBar}">
    <ul class="navbar-nav">
        <!--  -->
        <!-- <li class="head nav-item">
            <h2>{{trans('main.crud.services')}}</h2>
        </li> -->
        <li class="nav-item">
            <a class="" href="/voice-message">
                <h2>
                    {{ trans('main.vm.voice_messages') }}
                </h2>
            </a>
            <div class="bot_line"></div>
        </li>
        <li class="nav-item">
            <a class="" href="/sms">
                <h2>
                    SMS
                </h2>
            </a>
        </li>
        <li class="nav-item">
            <a class="" href="/developers">
                <h2>
                    {{trans('main.welcome.developers')}}
                </h2>
            </a>
        </li>
        <li class="nav-item" ng-class="{'active':toggled}" ng-click="openChat()"> {{--toggled = toggled ? false : true--}}
            <a>
                <h2>{{trans('main.welcome.contact_us')}}</h2>
                <!-- <img src="{{ asset('laravel_assets/images/mainStyleImages/caret.svg') }}" alt=""> -->
            </a>
            <ul class="contacts_list hidden" ng-class="{'active':toggled}">
                <li class="nav-item">
                    <a href="https://www.facebook.com/callburn.services/" target="_blank">
                        <h2>Facebook</h2>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="https://blog.callburn.com/">
                        <h2>{{trans('main.crud.blog')}}</h2>
                    </a>
                </li>
                <li class="nav-item">
                    <a id="chat-launch-button">
                        <h2>{{trans('main.welcome.contact_us')}}</h2>
                    </a>
                </li>
            </ul>
        </li>
        <!-- <li class="head nav-item">
            <h2>{{trans('main.welcome.contact_us')}}</h2>
        </li> -->
        <div class="reg_part">
            <li class="nav-item" ng-click="fromLink()">
                <a href="/login">
                    <h2>
                        {{trans('main.welcome.login')}}
                    </h2>
                </a>
            </li>
            <li class="nav-item" ng-click="checkAuth('register')">
                <a href="">
                    <h2>
                        {{trans('main.welcome.register')}}
                    </h2>
                </a>
            </li>
        </div>
    </ul>
</div>
<nav class="fixed-top navbar navbar-index navbar-toggleable-sm pl-2 pr-2 pl-md-4 pr-md-4 pt-0 pb-0 flex-column" ng-class="{opened:toggleBar}">
    <div class="container-fluid d-flex flex-row justify-content-center align-items-center w-100 m-0 p-0 flex-lg-row" ng-controller="AuthenticationController">
        <a class="navbar-brand ml-0 mr-auto text-center text-sm-left ml-lg-0" href="/">
            <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" width="150" height="40" alt="">
        </a>
        <ul class="navbar-nav mr-0 mr-lg-0 text-center">
            <div class="d-flex flex-row justify-content-center align-items-center">
                {{--<li class="nav-item ml-2 hidden-sm-down home_li">
                    <a class="m-0 nav-link" ng-href="{{ $tab == 'developers' || $tab == 'affiliation' || $tab == 'api' ? '/voice-message' : '#' }}" ng-click="scrollToDiv('demo')">Demo</a><div class="bot_line"></div>
                </li>--}}
                {{--<li class="nav-item ml-2 hidden-sm-down home_li">
                    <a class="m-0 nav-link" href="/">{{trans('main.crud.home')}}</a><div class="bot_line"></div>
                </li>
                <li class="nav-item ml-2 hidden-sm-down menu_ul_holder service_ul_holder">
                    <a class="m-0 nav-link">{{trans('main.crud.services')}}</a><div class="bot_line"></div>
                    <ul class="p-0 mt-1 list-group">
                        <li class="list-group-item">
                            <a href="/voice-message">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                    <img src="{{asset('/laravel_assets/images/front/img/img2.svg')}}" class="" height="26" width="35" alt="">
                                    <h2>{{trans('main.welcome.voice_messages')}}</h2>
                                </div>
                                <p>{{trans('main.crud.ring_it_instead')}}</p>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="/click-to-call">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                    <img src="{{asset('/laravel_assets/images/front/img/img3.svg')}}" class="" height="26" width="35" alt="">
                                    <h2>Click to call</h2>
                                </div>
                                <p>{{trans('main.crud.let_website_connect')}}</p>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="/en/developers">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                    <img src="{{asset('/laravel_assets/images/mainStyleImages/small_api.png')}}" height="26" width="35" class="" alt="">
                                    <h2>Developers API</h2>
                                </div>
                                <p>{{trans('main.crud.most_advenced_api')}}</p>
                            </a>
                        </li>
                    </ul>
                </li>--}}
                <li class="phonenumber_holder mr-1 mr-sm-0 nav-item text-muted hidden-xs-down">
                    <a ng-click="openCTCSnippet()" class="nav-link m-0 phonenumber text-center">
                        <span class="phonenumber_number">{{ trans('main.welcome.phonenumbers') }}</span>
                        <span class="phonenumber_ctc">(ClickToCall)</span>
                    </a>
                </li>   
                <li class="nav-item hidden-sm-down home_li">
                    <a class="m-0 nav-link" href="/voice-message">{{ trans('main.vm.voice_messages') }}</a>
                    <div class="bot_line"></div>
                </li>
                <li class="nav-item ml-2 hidden-sm-down home_li">
                    <a class="m-0 nav-link" href="/sms">SMS</a>
                    <div class="bot_line"></div>
                </li>
                <li class="nav-item ml-2 hidden-sm-down home_li">
                    <a class="m-0 nav-link" ng-href="{{ $tab == 'developers' || $tab == 'affiliation' || $tab == 'api' ? '/voice-message' : '#' }}" ng-click="scrollToDiv('prices')">{{trans('main.ctc.prices')}}</a>
                    <div class="bot_line"></div>
                </li>
                <li class="nav-item ml-2 hidden-sm-down home_li {{$tab == 'developers'?'active':''}}">
                    <a class="m-0 nav-link" href="/developers">{{trans('main.welcome.developers')}}</a><div class="bot_line"></div>
                </li>
                <li class="nav-item ml-2 hidden-sm-down menu_ul_holder contactus_holder" id="chat-launch-button" ng-click="openChat()">
                    <a class="m-0 nav-link">{{trans('main.welcome.contact_us')}}</a><div class="bot_line"></div>
                    <ul class="p-0 mt-1 list-group hidden">
                        <li class="list-group-item">
                            <a href="https://www.facebook.com/callburn.services/" target="_blank">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                    <img src="{{asset('/laravel_assets/images/mainStyleImages/fb_small.png')}}" class="hidden-sm-down" alt="">
                                    <h2>Facebook</h2>
                                </div>
                                <p>{{trans('main.crud.follow_on_fb')}}</p>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="https://blog.callburn.com/">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                    <img src="{{asset('/laravel_assets/images/mainStyleImages/blog.png')}}" height="27" width="32" class="hidden-sm-down" alt="">
                                    <h2>{{trans('main.crud.blog')}}</h2>
                                </div>
                                <p>{{trans('main.crud.blog_text')}}</p>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a id="chat-launch-button" ng-click="openChat()">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                    <img src="{{asset('/laravel_assets/clickToCall/images/click_to_call_icons/callerid-icon.svg')}}" height="27" width="32" class="hidden-sm-down" alt="">
                                    <h2>{{trans('main.vm.contact_us_navbar')}}</h2>
                                </div>
                                <p>{{trans('main.crud.click_to_chat')}}</p>
                            </a>
                        </li>  
                    </ul>
                </li>
                <li class="active lang nav-item mt-1 mb-1 ml-2">
                    <select class="form-control choose_lang">
                        @foreach ($languages as $lang)
                        <option {{\App::getLocale() == $lang->code ? 'selected' : ''}} value="{{ $lang->code }}">{{ $lang->full_name }}</option>
                        @endforeach
                    </select>
                </li>
                <li class="nav-item hidden-sm-down menu_ul_holder login_holder">
                    <a class="nav-link pointer" style="border-right: 1px solid #CFCED0;" href="/login">{{trans('main.welcome.login')}}</a><div class="bot_line"></div>
                    <ul class="p-0 mt-1 list-group hidden" ng-class="{'hide':showRegHover}">
                        <span class="social d-flex flex-row justify-content-center align-items-center pt-2 pb-0">
                            <p class="m-0 font-weight-bold">{{trans('main.crud.or_login_with')}}</p>
                            <img ng-click="loginFacebook($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/facebook.png')}}" alt="">
                            <img ng-click="loginGoogle($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/g+.png')}}" alt="">
                            <img ng-click="loginGitHub($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/github.png')}}" alt="">
                            <br>
                        </span>
                        <span class="social">
                            <p class="m-0 font-weight-bold">
                                {{trans('main.vm.or')}}
                            </p>
                        </span>
                        <div class="list-group-item">
                            <i class="fas fa-user" style="color: #9B9B9B;position: absolute;left: 28px;top: 20px;"></i>
                            <input ng-model="loginData.email" type="text" class="form-control" placeholder="yourmail@example.com" ng-keypress="checkEnter($event, 'login')">
                        </div>
                        <div class="list-group-item">
                            <i class="fas fa-lock" style="color: #9B9B9B;position: absolute;left: 28px;top: 20px;"></i>
                            <input ng-model="loginData.password" type="password" class="form-control" placeholder="******" ng-keypress="checkEnter($event, 'login')">
                        </div>
                        <div class="list-group-item">
                            <small ng-show="wrongCredentials" class="angular-hidden angular-wrong font-weight-bold font-italic">
                                {{trans('main.crud.wrong_credentials')}}
                            </small>
                            <small ng-show="accountDeactivated" class="angular-hidden angular-wrong">
                                {{trans('main.crud.we_are_sorry_your_account_was_blocked')}}
                                <a href="{{url('click-to-call')}}">
                                    {{trans('main.crud.contact_us')}}
                                </a>
                                {{trans('main.crud.for_further_assistance')}}
                            </small>
                            <button ng-click="login()" class="login_nav_btn btn btn-reg w-100 pointer">{{trans('main.welcome.login')}}</button>
                        </div>
                        <div class="list-group-item">
                            <a class="">
                                <p ng-click="toggleRegHover()" class="mb-0">{{trans('main.vm.not_member?')}}</p>
                            </a>
                            <a href="/login" ng-click="fromForgot()" class="">
                                <p>{{trans('main.vm.forgot_password')}}</p>
                            </a>
                        </div>
                    </ul>
                </li>
                <li class="nav-item hidden-sm-down menu_ul_holder reg_holder reg_id">
                    <a class="nav-link pointer pt-0 pb-0 reg_id">
                        <button style="height: 37px;min-width: 100px;" class="btn_blue pointer reg_id" ng-mouseenter="fixedRegHover()" ng-click="checkAuth('register')">{{trans('main.welcome.register')}}</button>
                    </a><div style="opacity: 0;" class="bot_line reg_id"></div><!-- || showRegHolder -->
                    <ul class="p-0 mt-1 list-group reg_id hidden" ng-mouseleave="toggleRegHover()"> <!-- ng-class="{'show': showRegHover}"  -->
                        <span class="social d-flex flex-row justify-content-center align-items-center pt-2 pb-0 reg_id">
                            <p class="m-0 font-weight-bold reg_id">{{trans('main.vm.register_now_with')}}</p>
                            <img class="reg_id" ng-click="loginFacebook($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/facebook.png')}}" alt="">
                            <img class="reg_id" ng-click="loginGoogle($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/g+.png')}}" alt="">
                            <img class="reg_id" ng-click="loginGitHub($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/github.png')}}" alt="">
                            <br class="reg_id">
                        </span>
                        <span class="social reg_id">
                            <p class="m-0 font-weight-bold reg_id">
                                {{trans('main.vm.or')}}
                            </p>
                        </span>
                        <div class="list-group-item reg_id">
                            <i class="fas fa-user reg_id" style="color: #9B9B9B;position: absolute;left: 28px;top: 20px;"></i>
                            <input ng-model="registrationData.email_address" type="text" class="form-control reg_id" placeholder="yourmail@example.com" ng-keypress="checkEnter($event, 'register', '.navbar_main_email')" ng-class="{'input-err':inputErr}">
                        </div>
                        <div class="list-group-item reg_id">
                            <div class="callburn-checkbox-white reg_id" ng-class="{'error':checkboxErr}">
                                <input type="checkbox" id="agreeTerms" ng-checked="agreedTerms" ng-model="agreedTerms" ng-true-value="1" ng-false-value="0" ng-click="enableRegisterMail()" class="reg_id">
                                <label for="agreeTerms" class="pt-0 reg_id">
                                    <span class="small reg_id">
                                        {{trans('main.snippet.by_clicking_above_button_you_agree_to_the')}}
                                        <a href="/privacy/#/?tab=1" class="reg_id">{{trans('main.snippet.terms__conditions')}}</a>
                                        {{trans('main.cu.and')}}
                                        <a href="/privacy/#/?tab=2" class="reg_id">
                                            {{trans('main.welcome.privacy_policy')}}
                                        </a>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="list-group-item reg_id">
                            <small ng-show="wrongEmail" class="angular-hidden angular-wrong font-weight-bold font-italic reg_id">
                                {{trans('main.crud.wrong_email_address')}}
                            </small>
                            <p ng-show="userAlreadyRegistered " class="angular-hidden mb-0 reg_id">
                                <small class="angular-wrong reg_id">
                                    <strong class="reg_id">{{trans('main.crud.user_already_registered_you_may_want_to')}}
                                        <span class="user-already-registered reg_id" ng-click="redirect('login')">
                                            {{trans('main.crud.login_1')}}
                                        </span>
                                        {{trans('main.crud.instead')}}
                                    </strong>
                                </small>
                            </p>
                            <button type="submit" class="login_nav_btn btn btn-reg btn-full-width pointer reg_id" ng-click="registration('.navbar_main_email')">
                                {{trans('main.crud.register_with_your_email_address')}}
                            </button>
                        </div>
                        <div id="showReg" class="list-group-item reg_id">
                            <div id="showReg" class="navbar_main_email hidden reg_id">
                                <p id="showReg" class="text-center mb-1 reg_id" style="color: #00cf76;">
                                    {{trans('main.crud.check_your_email_address')}}
                                </p>
                            </div>
                        </div>
                    </ul>
                </li>
                <li class="nav-item hidden-md-up">
                    <div class="bar pointer" ng-click="toggleFunc()" ng-class="{opened:toggleBar}">
                        <div class="bar_inner"></div>
                        <div class="bar_inner"></div>
                        <div class="bar_inner"></div>
                    </div>
                </li>
            </div>
        </ul>
    </div>
</nav>
