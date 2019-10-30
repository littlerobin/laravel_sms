@extends('front.layouts.app-index')

@section('content')
@include('front.price_label')
<div class="toTop" ng-click="scrollToTop()">
    <div class="arrow-up"></div>
</div>
<div class="main_page">
    <section class="main_section container-fluid pl-3 pr-3 pt-3 pb-3 pb-lg-0 pl-lg-4" style="background-image: url({{asset('/laravel_assets/images/mainStyleImages/back.png')}})">
        <div class="d-flex flex-column justify-content-center justify-content-md-between align-items-center flex-sm-row">
            <div class="text-center text-lg-left text-white content">
                <h2 class="mb-0 hidden-sm-down">{{trans('main.welcome.hello')}}! {{trans('main.welcome.we_are_a_company_providing_communications_to_humans')}}</h2>
                <h4 class="d-block hidden-sm-down">{{trans('main.crud.making_life_simple')}}</h4>
                <h2 class="hidden-md-up mb-2">
                    {{trans('main.welcome.hello')}}!
                </h2>
                <h4 class="hidden-md-up">
                    {{trans('main.crud.making_life_simple_mobile_size')}}
                </h4>
                <div ng-click="redirect('documentation')" class="text-white pointer d-inline-block">
                    <img src="{{asset('/laravel_assets/images/mainStyleImages/Play.png')}}" alt="">
                    <a>{{trans('main.crud.video_content')}}</a>
                </div>
            </div>
            <div class="phone hidden-md-down">
                <img src="{{asset('/laravel_assets/images/mainStyleImages/Getstarted.png')}}" alt="" class="" width="500">
                <div class="content">
                    <h3 class="text-white">
                        {{trans('main.crud.free_service')}}
                    </h3>
                    <h2 class="text-white">{{trans('main.crud.gift_credit')}}</h2>
                    <button class="btn" ng-click="checkAuth('register')">
                        {{trans('main.crud.start_now')}}
                    </button>
                </div>
            </div>
        </div>
    </section>
    <div class="container-fluid p-0">
        <section class="services_section row text-left services hidden-sm-down m-0">
            <div class="col-4 well text-left pointer d-flex flex-column justify-content-center justify-content-sm-baseline align-items-center" ng-click="redirect('voice-message')">
                <a href="" class="d-flex flex-column justify-content-center justify-content-sm-baseline align-items-start">
                    <img src="{{asset('/laravel_assets/images/front/img/img2.svg')}}" class="hidden-sm-down" alt="">
                    <h2 class="mt-3 mb-3">{{trans('main.welcome.voice_messages')}}</h2>
                    <p class="text-left mb-3 hidden-sm-down">{{trans('main.crud.ring_it_instead')}}</p>
                    <p class="more hidden-sm-down">
                        {{trans('main.crud.learn_more')}}
                    </p>
                </a>
            </div>
            <div class="col-4 well text-left pointer d-flex flex-column justify-content-center justify-content-sm-baseline align-items-center" ng-click="redirect('click-to-call')">
                <a href="" class="d-flex flex-column justify-content-center justify-content-sm-baseline align-items-start">
                    <img src="{{asset('/laravel_assets/images/front/img/img3.svg')}}" class="hidden-sm-down" alt="">
                    <h2 class="mt-3 mb-3">{{trans('main.welcome.ClickToCall')}}</h2>
                    <p class="mb-3 hidden-sm-down">{{trans('main.crud.let_website_connect')}}</p>
                    <p class="more hidden-sm-down">
                        {{trans('main.crud.learn_more')}}
                    </p>
                </a>
            </div>
            <div class="col-4 well text-left pointer d-flex flex-column justify-content-center justify-content-sm-baseline align-items-center">
                <!-- <h2 class="">{{trans('main.welcome.designed_for_professionals')}}</h2> -->
                <a href="https://callburn.com/developers" target="_blank" class="d-flex flex-column justify-content-center justify-content-sm-baseline align-items-start">
                    <img src="{{asset('/laravel_assets/images/front/img/img4.svg')}}" class="hidden-sm-down" alt="">
                    <h2 class="mt-3 mb-3">Developers API</h2>
                    <p class="mb-3 hidden-sm-down">{{trans('main.crud.most_advenced_api')}}</p>
                    <p class="more hidden-sm-down">
                        {{trans('main.crud.learn_more')}}
                    </p>
                </a>
            </div>
        </section>
        <section class="services_mobile hidden-md-up">
            <div class="holder pointer" ng-click="redirect('voice-message')">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h3>{{trans('main.welcome.voice_messages')}}</h3>
                    <img src="{{asset('/laravel_assets/images/front/img/img2.svg')}}" width="65" alt="callmessage">
                </div>
                <div>
                    {{trans('main.crud.ring_it_instead')}}
                    <span class="more float-right">
                        {{trans('main.crud.learn_more')}}
                    </span>
                </div>
            </div>
            <div class="holder pointer" ng-click="redirect('click-to-call')">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h3>Click to call</h3>
                    <img src="{{asset('/laravel_assets/images/front/img/img3.svg')}}" width="65" alt="callmessage">
                </div>
                <div>
                    {{trans('main.crud.let_website_connect')}}
                    <span class="more float-right">
                        {{trans('main.crud.learn_more')}}
                    </span>
                </div>
            </div>
            <div class="holder pointer">
                <div class="d-flex flex-row justify-content-between align-items-center" ng-click="redirect('documentation')">
                    <h3>Developers API</h3>
                    <img src="{{asset('/laravel_assets/images/front/img/img4.svg')}}" width="125" alt="callmessage">
                </div>
                <div>
                    {{trans('main.crud.most_advenced_api')}}
                    <span class="more float-right">
                        {{trans('main.crud.learn_more')}}
                    </span>
                </div>
            </div>
        </section>
    </div>
    <section class="subsection pt-5 text-center pl-2 pr-2 pl-md-4 pr-md-4">
        <div class="container-fluid p-0">
            <h2 class="text-uppercase text-left m-0">{{trans('main.crud.how_it_work')}}</h2>
            <p class="ml-auto mr-auto mb-5 mt-4 text-left">
                {{trans('main.crud.com_with_customer')}}
            </p>
            <div class="m-0 row feature-area">
                <div class="hidden-sm-down col-12 col-sm-6 offset-sm-0 col-lg-3 item">
                    <div class="content">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/free.png')}}" alt="">
                        <h5>
                            {{trans('main.crud.free')}}
                        </h5>
                        <p>
                            {{trans('main.crud.service_is_free')}}
                        </p>
                    </div>
                </div>
                <div class="hidden-sm-down col-12 col-sm-6 offset-sm-0 col-lg-3 item">
                    <div class="content">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/price.png')}}" alt="">
                        <h5>
                            {{trans('main.crud.lowest_price')}}
                        </h5>
                        <p>
                            {{trans('main.crud.have_lowest_price')}}
                        </p>
                    </div>
                </div>
                <div class="hidden-sm-down col-12 col-sm-6 offset-sm-0 col-lg-3 current item">
                    <div class="content">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/support.png')}}" alt="">
                        <h5>
                            {{trans('main.crud.24_support')}}
                        </h5>
                        <p>
                            {{trans('main.crud.have_24_support')}}
                        </p>
                    </div>
                </div>
                <div class="hidden-sm-down col-12 col-sm-6 offset-sm-0 col-lg-3 item">
                    <div class="content">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/cost.png')}}" alt="">
                        <h5>
                            {{trans('main.crud.no_fixed_cost')}}
                        </h5>
                        <p>
                            {{trans('main.crud.no_pay_for_plan')}}
                        </p>
                    </div>
                </div>
                <div id="marker" class="movable_border col-12 hidden-md-down"></div>
                <!-- -->
                <div class="col-12 col-sm-8 offset-sm-2 slick_feature_area">
                    <slick slides-to-show=1 class="hidden-md-up slick_services" settings="servicesSettings">
                        <li class="media-body text-center">
                            <img src="{{asset('/laravel_assets/images/mainStyleImages/free.png')}}" alt="">
                            <h5 class="text-uppercase">{{trans('main.crud.free')}}</h5>
                            <p class="m-auto">{{trans('main.crud.service_is_free')}}</p>
                        </li>
                        <li class="media-body text-center">
                            <img src="{{asset('/laravel_assets/images/mainStyleImages/price.png')}}" alt="">
                            <h5 class="text-uppercase">{{trans('main.crud.lowest_price')}}</h5>
                            <p class="m-auto">{{trans('main.crud.have_lowest_price')}}</p>
                        </li>
                        <li class="media-body text-center">
                            <img src="{{asset('/laravel_assets/images/mainStyleImages/support.png')}}" alt="">
                            <h5 class="text-uppercase">{{trans('main.crud.24_support')}}</h5>
                            <p class="m-auto">{{trans('main.crud.have_24_support')}}</p>
                        </li>
                        <li class="media-body text-center">
                            <img src="{{asset('/laravel_assets/images/mainStyleImages/cost.png')}}" alt="">
                            <h5 class="text-uppercase">{{trans('main.crud.no_fixed_cost')}}</h5>
                            <p class="m-auto">{{trans('main.crud.no_pay_for_plan')}}</p>
                        </li>
                    </slick>
                </div>
            </div>
        </div>
        <img data-aos="fade-up" src="{{asset('/laravel_assets/images/mainStyleImages/rotate_phone.png')}}" width="700" alt="" class="rotate_phone hidden-sm-down">
    </section>
</div>

@stop