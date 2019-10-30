<div id="main-content" class="vm_main">
    <header class="pl-2 pr-2 text-center" ng-controller="AuthenticationController">
        <img src="{{asset('laravel_assets/images/mainStyleImages/vm_icon_line.png')}}" class="vm_icon mb-2" alt="">
        <h1>{{trans('main.vm.first_worldwide')}} <br> {{trans('main.vm.web_platform')}}</h1>
        <h3 class="pt-3 mb-0">{{trans('main.vm.still_sending_sms?')}}</h3>
        <div class="reg_part_holder animated mt-5 mb-5" ng-class="{'fadeIn':showInput}">
            <div class="close_btn" ng-class="{'show':showInput}" ng-click="showInput = false"><i class="far fa-times-circle"></i></div>
            <span class="social d-flex flex-row justify-content-center align-items-center pt-2 pb-3" ng-class="{'show':showInput}">
                <p class="m-0 font-weight-bold">{{trans('main.vm.register_now_with')}}</p>
                <img ng-click="loginFacebook($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/facebook.png')}}" alt="">
                <img ng-click="loginGoogle($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/g+.png')}}" alt="">
                <img ng-click="loginGitHub($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/github.png')}}" alt="">
            </span>
            <div class="text-center">
                <small ng-show="wrongEmail" class="angular-hidden angular-wrong font-weight-bold font-italic">
                    {{trans('main.crud.wrong_email_address')}}
                </small>
                <p ng-show="userAlreadyRegistered " class="angular-hidden mb-0">
                    <small class="angular-wrong">
                        <strong>{{trans('main.crud.user_already_registered_you_may_want_to')}}
                            <span class="user-already-registered" ng-click="redirect('login')">
                                {{trans('main.crud.login_1')}}
                            </span>
                            {{trans('main.crud.instead')}}
                        </strong>
                    </small>
                </p>
                <div class="reg_free d-flex flex-column flex-md-row justify-content-center align-items-center">
                    <input type="text" ng-model="registrationData.email_address" ng-keypress="checkEnter($event, 'register', '.vm_main_email')" placeholder="{{trans('main.vm.or_write_email')}}" ng-class="{'show':showInput}">
                    <button ng-click="showInput = true;registration('.vm_main_email')" class="btn btn-success pointer mr-md-3 mt-2 mt-md-0 reg_btn" ng-class="{'show':showInput}">{{trans('main.welcome.register')}}</button>
                    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center animated fadeInRight" ng-class="{'hide': showInput}">
                        <button ng-click="checkAuth('register')" class="btn_blue pointer mr-0 mr-md-2 mb-2 mb-md-0">{{trans('main.vm.receive_free_credit')}}</button><!-- showInput = true -->
                        <button ng-click="scrollToDiv('demo')" class="demo pointer">{{trans('main.vm.listen_to_demo')}}</button>
                    </div>
                </div>
                <div class="vm_main_email hidden">
                    <p class="text-center mb-1" style="color: #8dff85;">{{trans('main.crud.check_your_email_address')}}</p>
                </div>
            </div>
        </div>
        <div class="are_you_dev text-center">
            <p class="d-inline-block m-0 mb-md-4 font-weight-normal">{{trans('main.vm.are_you_dev')}}</p>
            <a class="d-inline-block m-0 mb-md-4 font-weight-normal" href="/developers">{{trans('main.vm.click_here_now')}}</a>
        </div>
    </header>
    <div class="pb-3 pt-3 pl-1 pr-1 things">
        <ul class="d-flex flex-row hidden-sm-down justify-content-center align-items-center">
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.cheaper')}} {{trans('main.vm.than_sms')}}</p>
            </li>
            <li class="line"></li>
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.interactive')}}</p>
            </li>
            <li class="line"></li>
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.landline')}}</p>
            </li>
            <li class="line"></li>
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.no_need_for_data_connect')}}</p>
            </li>
            <li class="line"></li>
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.support_audio_and_text')}}</p>
            </li>
            <li class="line"></li>
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.easy_to_listen')}}</p>
            </li>
            <li class="line"></li>
            <li>
                <i class="fas fa-check"></i>
                <p>{{trans('main.vm.custom_callerid')}}</p>
            </li>
        </ul>
        <ul>
            <slick slides-to-show=1 class="hidden-md-up" settings="servicesSettings" autoplay=true autoplay-speed=2000 dots=false>
                <li>
                    <i class="fas fa-check"></i>
                    <p>{{trans('main.vm.cheaper')}} {{trans('main.vm.than_sms')}}</p>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <p>{{trans('main.vm.interactive')}}</p>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <p>{{trans('main.vm.custom_callerid')}}</p>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <p>{{trans('main.vm.no_need_for_data_connect')}}</p>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <p>{{trans('main.vm.support_audio_and_text')}}</p>
                </li>
                <li>
                    <i class="fas fa-check"></i>
                    <p>{{trans('main.vm.easy_to_listen')}}</p>
                </li>
            </slick>
        </ul>
    </div>
    <div class="service_about text-center pt-5 pb-5">
        <h1>{{trans('main.vm.service_title_1')}}</h1>
        <h2>{{trans('main.vm.service_title_2')}}</h2>
    </div>
    <div class="charts text-center pl-2 pr-2">
        <!-- <h1>{{trans('main.vm.paying_for_undeliver')}}</h1> -->
        <!-- <h2>{{trans('main.vm.free_callerid')}}</h2> -->
        <div class="chart_holder">
            <ul ng-init="arrowPosition()">
                <div class="sms_column">
                    <img class="" src="{{asset('laravel_assets/images/mainStyleImages/sms_column.png')}}" alt="" height="100%" width="22">
                </div>
                <div class="sms_label hidden-md-down">
                    <p>{{trans('main.vm.before_you_pay_lot')}}</p>
                </div>
                <ul class="bg">
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                </ul>
                <ul class="sms">
                    <div class="lab" ng-class="{'active':labelBar}">
                        {{trans('main.vm.sms')}}
                    </div>
                    <li ng-class="{'active':thirdBar}">
                        <i class="fas fa-check"></i>
                        <span>{{trans('main.vm.key1')}}</span>
                    </li>
                    <li ng-class="{'active':secondBar}">
                        <i class="fas fa-check"></i>
                        <span>{{trans('main.vm.key2')}}</span>
                    </li>
                    <li ng-class="{'active':firstBar}">
                        <i class="fas fa-check"></i>
                        <span>{{trans('main.vm.key3')}}</span>
                    </li>
                </ul>
                <ul class="vm" ng-class="{'animate':vmAnim}">
                    <div class="lab" ng-class="{'active':firstBar}">{{trans('main.vm.voice_messages')}}</div>
                    <li ng-class="{'active':firstBar}">
                        <i class="fas fa-check"></i>
                        <span>{{trans('main.vm.vm_key')}}</span>
                    </li>
                </ul>
                <div class="vm_label hidden-md-down">
                    <p>{{trans('main.vm.save_money')}}</p>
                </div>
            </ul>
        </div>
    </div>
    <div class="warranty pt-5 pb-5 pl-2 pr-2" style="background-image: url('{{asset('laravel_assets/images/mainStyleImages/warranty.png')}}');background-size: 120px;">
        <h1>{{trans('main.vm.why_pay_for_percent')}} <br> {{trans('main.vm.vm_offer')}} {{trans('main.vm.opening_warranty')}} {{trans('main.vm.when_delivered')}}</h1>
        <h2>{{trans('main.vm.innovative_way')}} {{trans('main.vm.not_like_whatsapp')}} {{trans('main.vm.requering_data')}} {{trans('main.vm.normal_call')}}</h2>
    </div>
    <!-- ****** -->
    <div class="example_parent m-0">
        <div class="ex_about pl-2 pr-2 p-md-0 text-center">
            <div class="">
                <h1>{{trans('main.vm.text_1_key')}}</h1>
                <p class="">{{trans('main.vm.text_2_key')}} {{trans('main.vm.text_3_key')}}</p>
            </div>
            <!-- <div>
                <h1>{{trans('main.vm.text_4_key')}}</h1>
                <p class="mb-0">{{trans('main.vm.text_5_key')}} {{trans('main.vm.text_6_key')}} {{trans('main.vm.text_7_key')}}</p>
            </div> -->
        </div>
        <div class="example_holder d-flex flex-column flex-md-row">
            <div class="ex_list hidden-sm-down mt-4">
                <ul>
                    <li>
                        <div class="circle">1</div>
                        <p>{{trans('main.vm.list_1_key')}}</p>
                    </li>
                    <li>
                        <div class="circle">2</div>
                        <p>{{trans('main.vm.list_2_key')}}</p>
                    </li>
                    <li>
                        <div class="circle">3</div>
                        <p>{{trans('main.vm.list_3_key')}}</p>
                    </li>
                </ul>
            </div>
            <div class="ex_phone mr-md-2 ml-md-2 hidden-sm-down">
                <p>
                    <span>{{trans('main.vm.incoming_call')}}</span>
                    {{trans('main.vm.your_callerid')}}
                </p>
                <img src="{{asset('/laravel_assets/images/mainStyleImages/demo_phone1.png')}}" alt="">
                <h2 class="dec">{{trans('main.vm.decline')}}</h2>
                <h2 class="acc">{{trans('main.vm.accept')}}</h2>
            </div>
            <div class="ex_checks">
                <ul class="d-flex flex-column justify-content-start">
                    <li><i class="fas fa-check"></i><p>{{trans('main.vm.checks_1')}}</p></li>
                    <li><i class="fas fa-check"></i><p>{{trans('main.vm.checks_2')}}</p></li>
                    <li><i class="fas fa-check"></i><p>{{trans('main.vm.checks_3')}}</p></li>
                    <li><i class="fas fa-check"></i><p>{{trans('main.vm.checks_4')}}</p></li>
                    <li><i class="fas fa-check"></i><p>{{trans('main.vm.checks_5')}}</p></li>
                    <li><i class="fas fa-check"></i><p>{{trans('main.vm.checks_6')}}</p></li>
                </ul>
            </div>
            <div class="ex_list mt-3 ml-3 mr-3 hidden-md-up">
                <ul>
                    <li>
                        <div class="circle">1</div>
                        <p>{{trans('main.vm.list_1_key')}}</p>
                    </li>
                    <li>
                        <div class="circle">2</div>
                        <p>{{trans('main.vm.list_2_key')}}</p>
                    </li>
                    <li>
                        <div class="circle">3</div>
                        <p>{{trans('main.vm.list_3_key')}}</p>
                    </li>
                </ul>
            </div>
            <div class="ex_phone hidden-md-up">
                <p>
                    <span>{{trans('main.vm.incoming_call')}}</span>
                    {{trans('main.vm.your_callerid')}}
                </p>
                <img src="{{asset('/laravel_assets/images/mainStyleImages/demo_phone1.png')}}" alt="">
                <h2 class="dec">{{trans('main.vm.decline')}}</h2>
                <h2 class="acc">{{trans('main.vm.accept')}}</h2>
            </div>
        </div>
        <!-- ****** -->
        <div class="ex_hint pt-5 pb-5">
            <p>{{trans('main.vm.hint1')}} {{trans('main.vm.hint2')}} {{trans('main.vm.hint3')}}</p>
        </div>
        <!-- ****** -->
        <div class="demo_holder pl-2 pr-2 p-md-0">
            <div class="demo_text text-center">
                <h1 class="">{{trans('main.vm.demo_text_1')}}</h1>
                <p>{{trans('main.vm.demo_text_2')}} {{trans('main.vm.demo_text_3')}}</p>
            </div>
            <div class="listen_holder_demo d-flex flex-column flex-md-row justify-content-between">
                <div class="listen" id="demo">
                    <div class="listen_now pt-3" >
                        <!-- <h1>{{trans('main.vm.listen_now_text')}}</h1> -->
                        <div class="voice_example" ng-controller="VoiceMessagesController">
                            <ul class="nav nav-tabs d-flex justify-content-center align-items-center" role="tablist">
                                <li role="presentation" class="nav-item">
                                    <a href="#Authenticate" aria-controls="Authenticate" role="tab" data-toggle="tab" class="active nav-link" ng-click="choiceAudio(0)">{{trans('main.vm.authenticate')}}</a>
                                </li>
                                <li role="presentation" class="nav-item">
                                    <a href="#Notify" aria-controls="Notify" role="tab" data-toggle="tab" ng-click="choiceAudio(1)" class="nav-link">{{trans('main.vm.notify')}}</a>
                                </li>
                                <li role="presentation" class="nav-item">
                                    <a href="#Promote" aria-controls="Promote" role="tab" data-toggle="tab" ng-click="choiceAudio(2)" class="nav-link">{{trans('main.vm.promote')}}</a>
                                </li>
                            </ul>
                            <div role="tabpanel" class="tab-pane active fade show">
                                <p class="demo_txt" ng-if="audioIndex === 0">{{trans('main.vm.auth_demo')}}</p>
                                <p class="demo_txt" ng-if="audioIndex === 1">{{trans('main.vm.notify_demo')}}</p>
                                <p class="demo_txt" ng-if="audioIndex === 2">{{trans('main.vm.promote_demo')}}</p>
                                <img class="play-audio angular-hidden" src="{{asset('/laravel_assets/images/mainStyleImages/Play.png')}}" alt="" ng-show="play" ng-click="playAudio()">
                                <img class="play-audio angular-hidden" src="{{asset('/laravel_assets/images/mainStyleImages/Stop.png')}}" alt="" ng-show="!play" ng-click="pauseAudio()">
                                <p class="demo_p">{{trans('main.vm.real_demo')}}</p>
                            </div>
                            <audio controls class="hidden" id="audio-file">
                                <source ng-src="@{{audios[audioIndex]}}" type="audio/mpeg"/>
                            </audio>
                        </div>
                    </div>
                </div>
                <div class="demo_phone ml-md-2 mr-md-3">
                    <p>{{trans('main.vm.your_callerid')}}</p>
                    <h2>{{trans('main.vm.end_call')}}</h2>
                    <img src="{{asset('/laravel_assets/images/mainStyleImages/demo_phone2.png')}}" alt="">
                </div>
                <div class="demo_list ml-3 mr-3 mt-4 ml-md-0 mr-md-0">
                    <ul>
                        <li>
                            <div class="circle">4</div>
                            <p>{{trans('main.vm.demo_list_1')}}</p>
                        </li>
                        <li>
                            <div class="circle">5</div>
                            <p><span class="apostr">“</span>{{trans('main.vm.offering_services')}}</p>
                            <p class="m-0">
                                {{trans('main.vm.press')}} 1 {{trans('main.vm.for_buying_them')}}
                            </p>
                            <p class="m-0">{{trans('main.vm.press')}} 2 {{trans('main.vm.for_support')}}<span class="apostr">”</span>
                            </p>
                        </li>
                        <li>
                            <div class="circle">6</div>
                            <p>{{trans('main.vm.demo_list_3')}}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- ****** -->
</div>
<div>

</div>
<div class="two_service pt-5 pb-5 text-center">
    <h1 class="title">{{trans('main.sms.two_service_type')}}</h1>
    <h2>{{trans('main.sms.two_service_vm_subtitle')}}</h2>
    <div class="types">
        <div class="box">
            <div class="main">
                <div class="img_holder vm_type">
                    <h1>{{trans('main.sms.incoming_call')}}</h1>
                    <h2>{{trans('main.sms.from_your_number')}}</h2>
                    <img src="{{asset('laravel_assets/images/front/vm.svg')}}" alt="vm">
                </div>
                <div class="content">
                    <h1>{{trans('main.sms.standard')}}</h1>
                    <h1>{{trans('main.sms.dashboard_welcome_voice_messages')}}</h1>
                    <h2>{{trans('main.sms.only_vm_text')}}</h2>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="main">
                <div class="img_holder vm_sms_type">
                    <h1>{{trans('main.sms.missed_call')}} (3)</h1>
                    <h2>{{trans('main.sms.from_your_number')}}</h2>
                    <h3>1 {{trans('main.sms.new_sms')}}</h3>
                    <img src="{{asset('laravel_assets/images/front/vm_sms.svg')}}" alt="vm and sms">
                </div>
                <div class="content">
                    <h1>{{trans('main.sms.hibrid')}}</h1>
                    <h1>{{trans('main.sms.vm_and_sms_title')}}</h1>
                    <h2>{{trans('main.sms.vm_and_sms_text')}}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="inter_holder pt-5 pb-5 text-center">
    <h1>{{trans('main.vm.inter_title_1')}}</h1>
    <h2>{{trans('main.vm.inter_title_2')}}</h2>
    <div class="interactions slick_feature_area">
        <ul class="ints hidden-sm-down">
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter1.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_1')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_1')}}
                </div>
            </li>
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter2.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_2')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_2')}}
                </div>
            </li>
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter3.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_3')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_3')}}
                </div>
            </li>
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter4.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_4')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_4')}}
                </div>
            </li>
        </ul>
        <slick slides-to-show=1 class="hidden-md-up ints" settings="servicesSettings">
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter1.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_1')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_1')}}
                </div>
            </li>
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter2.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_2')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_2')}}
                </div>
            </li>
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter3.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_3')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_3')}}
                </div>
            </li>
            <li class="p-0">
                <div class="heading">
                    <div class="heading_inner">
                        <img src="{{asset('laravel_assets/images/mainStyleImages/inter4.png')}}" alt="">
                        <h1>{{trans('main.vm.inter_head_4')}}</h1>
                    </div>
                    <div>
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="body">
                    {{trans('main.vm.inter_body_4')}}
                </div>
            </li>
        </slick>
    </div>
</div>
<div class="fixed_line text-center pt-5 pb-5 pl-2 pr-2">
    <h1>{{trans('main.vm.fixed_line')}} <br>{{trans('main.vm.sms_are_not')}}</h1>
    <p>{{trans('main.vm.grandfather_can')}}</p>
    <div class="tree mt-3">
        <div class="d-flex flex-column justify-content-center align-items-center">
            <div class="vm">
                <img src="{{asset('/laravel_assets/images/front/img/img2.svg')}}" alt="">
                <p>{{trans('main.vm.voice_messages')}}</p>
            </div>
            <div class="vertical good_connect">
                <div class="line"></div>
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="d-flex flex-row justify-content-center align-items-center">
                <div class="sms">
                    <img src="{{asset('/laravel_assets/images/mainStyleImages/sms.png')}}" alt="">
                </div>
                <div class="horizontal bad_connect">
                    <div class="line"></div>
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="home_call">
                    <img src="{{asset('/laravel_assets/images/mainStyleImages/home_call.png')}}" alt="">
                </div>
                <div class="horizontal bad_connect">
                    <div class="line"></div>
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="call">
                    <img src="{{asset('/laravel_assets/images/mainStyleImages/call.png')}}" alt="">
                </div>
            </div>
        </div>
    </div>
    <div class="text-center vm_main" ng-controller="AuthenticationController">
        <div class="reg_part_holder animated mt-4" ng-class="{'fadeIn':showInput}">
            <div class="close_btn" ng-class="{'show':showInput}" ng-click="showInput = false"><i class="far fa-times-circle"></i></div>
            <span class="social d-flex flex-row justify-content-center align-items-center pt-2 pb-3" ng-class="{'show':showInput}">
                <p class="m-0 font-weight-bold">{{trans('main.vm.register_now_with')}}</p>
                <img ng-click="loginFacebook($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/facebook.png')}}" alt="">
                <img ng-click="loginGoogle($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/g+.png')}}" alt="">
                <img ng-click="loginGitHub($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/github.png')}}" alt="">
            </span>
            <div class="text-center" >
                <small ng-show="wrongEmail" class="angular-hidden angular-wrong font-weight-bold font-italic">
                    {{trans('main.crud.wrong_email_address')}}
                </small>
                <p ng-show="userAlreadyRegistered " class="angular-hidden mb-0">
                    <small class="angular-wrong">
                        <strong>{{trans('main.crud.user_already_registered_you_may_want_to')}}
                            <span class="user-already-registered" ng-click="redirect('login')">
                                {{trans('main.crud.login_1')}}
                            </span>
                            {{trans('main.crud.instead')}}
                        </strong>
                    </small>
                </p>
                <div class="reg_free d-flex flex-column flex-md-row justify-content-center align-items-center">
                    <input type="text" ng-model="registrationData.email_address" ng-keypress="checkEnter($event, 'register', '.footer_main_email')" placeholder="{{trans('main.vm.or_write_email')}}" ng-class="{'show':showInput}">
                    <button ng-click="showInput = true;registration('.footer_main_email')" class="btn btn-success pointer mr-md-3 mt-2 mt-md-0 reg_btn" ng-class="{'show':showInput}">{{trans('main.welcome.register')}}</button>
                    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center animated fadeInRight" ng-class="{'hide': showInput}">
                        <button ng-click="checkAuth('register')" class="btn_blue pointer m-0">{{trans('main.vm.receive_free_credit')}}</button><!-- showInput = true -->
                    </div>
                </div>
                <div class="footer_main_email hidden">
                    <p class="text-center mb-1" style="color: #8dff85;">{{trans('main.crud.check_your_email_address')}}</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="reach_holder pt-5">
    <h1>{{trans('main.vm.reach_title_1')}}</h1>
    <h2>{{trans('main.vm.reach_title_2')}}</h2>
    <div class="contacts">
        <ul ng-controller="AuthenticationController">
            <img src="{{asset('laravel_assets/images/mainStyleImages/reach_vm.png')}}" class="reach_vm" alt="">
            <div class="blue">
                {{trans('main.vm.contacts')}}
            </div>
            <li>
                <i class="fas fa-check"></i>
                <h3>{{trans('main.vm.clients_gold')}}</h3>
                <span>(132)</span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <h3>Hakob</h3>
                <span></span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <h3>Roberto</h3>
                <span></span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <h3>{{trans('main.vm.verifications')}}</h3>
                <span>(67)</span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <h3>Irene</h3>
                <span></span>
            </li>
            <button ng-click="toggleRegHover()"><img src="{{asset('laravel_assets/images/mainStyleImages/send_now.png')}}" alt="">{{trans('main.vm.send_now')}}</button>
        </ul>
    </div>
</div>
<div class="pl-2 pr-2 pl-md-0 pr-md-0 op_holder pt-5 pb-5">
    <h1>{{trans('main.vm.op_title_1')}}</h1>
    <h2>{{trans('main.vm.op_title_2')}}</h2>
    <div class="operators">
        <img src="{{asset('laravel_assets/images/mainStyleImages/op1.png')}}" alt="">
        <img src="{{asset('laravel_assets/images/mainStyleImages/op2.png')}}" alt="">
        <img src="{{asset('laravel_assets/images/mainStyleImages/op3.png')}}" alt="">
        <img src="{{asset('laravel_assets/images/mainStyleImages/op4.png')}}" alt="">
        <img src="{{asset('laravel_assets/images/mainStyleImages/op5.png')}}" alt="">
        <img src="{{asset('laravel_assets/images/mainStyleImages/op6.png')}}" alt="">
    </div>
</div>
<!-- ***** -->
<!-- <div class="broadcast text-center pt-5 pb-5 pl-2 pr-2">
    <h1>{{trans('main.vm.broadcasting')}}</h1>
    <p>{{trans('main.vm.easily_create')}}</p>
    <div class="steps d-flex flex-column flex-md-row align-items-center">
        <div class="art">
            <img src="{{asset('laravel_assets/images/front/img/img16.svg')}}" height="45" alt="">
            <h4>{{trans('main.vm.compose')}} {{trans('main.vm.a_message')}}</h4>
            <p>{{trans('main.vm.from_a_file_a_text_or_a_template')}}</p>
        </div>
        <i class="fas fa-chevron-right"></i>
        <div class="art">
            <img src="{{asset('laravel_assets/images/front/img/img17.svg')}}" width="45" height="45" alt="">
            <h4>{{trans('main.vm.choose')}} {{trans('main.vm.your_recipients')}}</h4>
            <p>{{trans('main.vm.import_from_a_device_from_a_file_o_manually_typed')}}</p>
        </div>
        <i class="fas fa-chevron-right"></i>
        <div class="art">
            <img src="{{asset('laravel_assets/images/front/img/img18.svg')}}" width="45" height="45" alt="">
            <h4>{{trans('main.vm.review__send')}} {{trans('main.vm.or_save_it_as_template')}}</h4>
            <p>{{trans('main.vm.review_all_the_message_details_add_interactions_or_schedule_delivery')}}</p>
        </div>
    </div>
</div> -->
<div class="prices_holder pt-5 pb-5" id="prices">
    <h1 class="title vm">{{trans('main.sms.prices_title')}}</h1>
    <h2 class="sub_title vm">{{trans('main.sms.prices_sub_title')}}</h2>
    <div class="prices" ng-controller="AuthenticationController">
        <div class="handle">
            <div class="send_to">
                <p class="blue">{{trans('main.vm.i_want_to_send_messages_to')}}</p>
                <div class="send_to_inner">
                    <button type="button" class="btn dropdown-toggle selected-phonenumber-image" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div>
                            <img ng-src="{{asset('/laravel_assets/callburn/images/lang-flags')}}/@{{callRoutes[0].code}}.svg" alt="" width="20">
                            <span>
                                <span class="country">@{{callRoutes[0].name}}</span><span class="prefix">(+@{{callRoutes[0].phonenumber_prefix}})</span>
                            </span>
                        </div>
                    </button>
                    <ul class="dropdown-menu notificationScrollStyle">
                        <li ng-repeat="country in callRoutes" ng-click="choiceCountryPrice(country);">
                            <a href="#">
                                <img ng-src="{{asset('/laravel_assets/callburn/images/lang-flags')}}/@{{country.code}}.svg" alt="">
                                <span>
                                    <span class="country">@{{country.name}}</span><span class="prefix">(+@{{country.phonenumber_prefix}})</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="calc_section">
            <div class="vm">
                <div class="txt_price">
                    <div>
                        <div class="text">
                            <h1>{{trans('main.vm.voice_messages')}}</h1>
                        </div>
                        <div class="price">
                            <h2 ng-show="!prices">----</h2>
                            <h2 class="text-white" ng-show="prices && prices * selectedMessageLength / 60 < 1.00">€ @{{(prices * selectedMessageLength / 60 | number:4).toString().replace('.', ',') }}*</h2>
                            <h2 class="text-white" ng-show="prices && prices * selectedMessageLength / 60 >= 1.00">€ @{{(prices * selectedMessageLength / 60 | number:2).toString().replace('.', ',') }}*</h2>
                        </div>
                    </div>
                    <h2 class="small_txt text-white" ng-show="prices * selectedMessageLength / 60 * 100 < 100">@{{(prices * selectedMessageLength / 60 * 100 | number:2).toString().replace('.', ',')}} {{ trans('main.ctc.cents') }} {{trans('main.vm.single_message')}} {{trans('main.vm.delivered_and_fully_listened')}}</h2>
                </div>
                <div class="message_length">
                    <div class="sub_box_inner">
                        <p>*{{trans('main.vm.message_lenght')}}</p>
                    </div>
                    <div class="calc_inp">
                        <input class="inp-placeholder form_message_length has-type" type="number" ng-model="selectedMessageLength" ng-blur="messageValidator()" step="1" id="example-number-input" autocomplete="false">
                        <label for="example-sms-input" class="lab-placeholder">
                            {{ trans('main.vm.seconds') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="sms">
                <div class="txt_price">
                    <div>
                        <div class="text">
                        <i class="sms_count">@{{num}}</i>
                            <h1>SMS</h1>
                            <!-- <h2>{{trans('main.sms.pay_also_for_undelivered')}}</h2> -->
                        </div>
                        <div class="price">
                            <h2 class="text-white" ng-show="smsPrices && smsPrices * num < 1.00">€ @{{(smsPrices * num | number:4).toString().replace('.', ',') }}*</h2>
                            <h2 class="text-white" ng-show="smsPrices && smsPrices * num >= 1.00">€ @{{(smsPrices * num | number:2).toString().replace('.', ',') }}*</h2>
                            <h2 class="text-white" ng-show="!smsPrices">----</h2>
                            <!-- <h2 class="text-white" ng-show="price * selectedMessageLength / 60 * 100 < 100">@{{(price * selectedMessageLength / 60 * 100 | number:2).toString().replace('.', ',')}} {{ trans('main.ctc.cents') }}</h2> -->
                        </div>
                    </div>
                    <h2 class="small_txt text-white">{{trans('main.sms.instant_delivery_prices')}}</h2>
                </div>
                <div class="message_length">
                    <div class="calc_inp">
                        <input ng-change="calcSmsText()" class="inp-placeholder form_message_length has-type" type="number" ng-model="selectedSmsLength" ng-blur="smsValidator()" step="1" id="example-sms-input" autocomplete="false">
                        <label for="example-sms-input" class="lab-placeholder">
                            {{ trans('main.sms.characters') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="sub_box">
                <p>*{{trans('main.crud.vm_price_txt')}}</p>
            </div>
        </div>
    </div>
</div>
<!--  -->
<div class="promo_holder pt-5">
    <h1>{{trans('main.vm.promo_title_1')}}</h1>
    <h2>{{trans('main.vm.promo_title_2')}}</h2>
    <div class="promo_video embed-responsive embed-responsive-16by9">
        <iframe src="@{{youtubeUrl}}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="embed-responsive-item"></iframe> {{--getVideoFromPlaylist(VMVideosObj.promo[1])--}}
    </div>
</div>
<!--  -->
<div class="dev_holder text-center pt-5 pb-5 pl-2 pr-2" style="background-image: url('{{asset('laravel_assets/images/mainStyleImages/api_background.svg')}}');background-size: cover;">
    <h1 class="">{{trans('main.vm.developer_or_custom_app')}}</h1>
    <h2>{{trans('main.vm.easily_integrate')}}</h2>
    <a href="/developers">
        <button class="">
            {{trans('main.vm.dev_btn_text')}}
        </button>
    </a>
</div>
</div>
@include('front.partials.customers_reviews_voice_messages')
<div class="text-center vm_main" ng-controller="AuthenticationController">
    <div class="reg_part_holder animated mt-3 mb-3 mt-md-5 mb-md-5" ng-class="{'fadeIn':showInput}">
        <div class="close_btn" ng-class="{'show':showInput}" ng-click="showInput = false"><i class="far fa-times-circle"></i></div>
        <span class="social d-flex flex-row justify-content-center align-items-center pt-2 pb-3" ng-class="{'show':showInput}">
            <p class="m-0 font-weight-bold">{{trans('main.vm.register_now_with')}}</p>
            <img ng-click="loginFacebook($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/facebook.png')}}" alt="">
            <img ng-click="loginGoogle($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/g+.png')}}" alt="">
            <img ng-click="loginGitHub($event)" class="pointer ml-2" src="{{asset('/laravel_assets/images/mainStyleImages/github.png')}}" alt="">
        </span>
        <div class="text-center" >
            <small ng-show="wrongEmail" class="angular-hidden angular-wrong font-weight-bold font-italic">
                {{trans('main.crud.wrong_email_address')}}
            </small>
            <p ng-show="userAlreadyRegistered " class="angular-hidden mb-0">
                <small class="angular-wrong">
                    <strong>{{trans('main.crud.user_already_registered_you_may_want_to')}}
                        <span class="user-already-registered" ng-click="redirect('login')">
                            {{trans('main.crud.login_1')}}
                        </span>
                        {{trans('main.crud.instead')}}
                    </strong>
                </small>
            </p>
            <div class="reg_free d-flex flex-column flex-md-row justify-content-center align-items-center">
                <input type="text" ng-model="registrationData.email_address" ng-keypress="checkEnter($event, 'register', '.footer_main_email')" placeholder="{{trans('main.vm.or_write_email')}}" ng-class="{'show':showInput}">
                <button ng-click="showInput = true;registration('.footer_main_email')" class="btn btn-success pointer mr-md-3 mt-2 mt-md-0 reg_btn" ng-class="{'show':showInput}">{{trans('main.welcome.register')}}</button>
                <div class="d-flex flex-column flex-md-row justify-content-center align-items-center animated fadeInRight" ng-class="{'hide': showInput}">
                    <button ng-click="checkAuth('register')" class="btn_blue pointer m-0 mr-md-3 mb-2 mb-md-0">{{trans('main.vm.receive_free_credit')}}</button><!-- showInput = true -->
                    <button ng-click="scrollToDiv('demo')" class="demo pointer">{{trans('main.vm.listen_to_demo')}}</button>
                </div>
            </div>
            <div class="footer_main_email hidden">
                <p class="text-center mb-1" style="color: #8dff85;">{{trans('main.crud.check_your_email_address')}}</p>
            </div>
        </div>
    </div>
</div>

