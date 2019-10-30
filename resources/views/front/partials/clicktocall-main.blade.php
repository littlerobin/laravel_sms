<div id="main-content" class="vm_main">
    <div id="include_part" class="articles">
        <div class="holder description">
            <div ng-init="descr = true" ng-click="descr = !descr" data-toggle="collapse" href="#descr" aria-expanded="false" aria-controls="descr" class="toggler d-flex flex-row align-items-center pl-2 pr-2 pl-lg-4 pr-lg-4 pt-3 pb-3 pointer">
                <div class="triangle" ng-class="{toggled:descr}"></div>
                <h3 class="ml-2 mb-0">{{trans('main.crud.service_description')}}</h3>
            </div>
            <div class="collapse show" id="descr">
                <div class="col-12 pt-5 pb-5">
                    <h5 class="m-auto text-center">{{trans('main.crud.ctc_description')}}</h5>
                </div>
            </div>
        </div>
        <div class="holder">
            <div ng-init="snippet = true" ng-click="snippet = !snippet" data-toggle="collapse" href="#snippet" aria-expanded="false" aria-controls="snippet" class="toggler d-flex flex-row align-items-center pl-2 pr-2 pl-lg-4 pr-lg-4 pt-3 pb-3 pointer">
                <div class="triangle" ng-class="{toggled:snippet}"></div>
                <h3 class="ml-2 mb-0">{{trans('main.crud.service_example')}}</h3>
            </div>
            <div class="collapse show" id="snippet">
                <div class="">
                    <div class="text pt-5 pb-5">
                        <h3 ng-click="openCTCOpened()">{{trans('main.crud.try_real_demo')}}</h3>
                    </div>
                    {{--<div class="text-center" ng-controller="ClickToCallController">
                        <div class="live-snippet-holder">
                            <div class="service service_n">
                                <div class="tab-content">
                                    <div id="callburn-snippet">
                                        <div style="margin: 10px auto" ng-show="showLoader" class="loader"></div>
                                        <script>
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                </div>
            </div>
        </div>
        
        <div class="holder advent">
            <div ng-init="toggle2 = true" ng-click="toggle2 = !toggle2" data-toggle="collapse" href="#advent" aria-expanded="false" aria-controls="advent" class="toggler d-flex flex-row align-items-center pl-2 pr-2 pl-lg-4 pr-lg-4 pt-3 pb-3 pointer">
                <div class="triangle" ng-class="{toggled:toggle2}"></div>
                <h3 class="ml-2 mb-0">{{trans('main.crud.service_adventages')}}</h3>
            </div>
            <div class="collapse show" id="advent">
                <div class="row m-0">
                    <div class="col-md-6 col-lg-5 p-sm-2 p-lg-4 pt-3 pt-sm-0 text_holder">
                        <div class="text">
                            <h3>{{trans('main.ctc.detailed_snippet_reporting_statistics')}}</h3>
                            <h4>{{trans('main.ctc.clicks_pending_actions_spent_amount_and_more_always_under_your_control')}}</h4>
                            <ul class="">
                                <li>{{trans('main.ctc.easily_create_your_snippets')}}</li>
                                <li>{{trans('main.ctc.check_your_costs')}}</li>
                                <li>{{trans('main.ctc.analyze_conversion_rate_and_try_to_enhance_it')}}</li>
                                <li>{{trans('main.ctc.export_statistics_into_pdf_csv_xls_or_xlsx_files')}}</li>
                            </ul>
                        </div>
                        <img src="{{asset('laravel_assets/images/mainStyleImages/tablet_img.png')}}" class="img-fluid mt-5 mb-4 mb-md-0" alt="">
                    </div>
                    <div class="col-md-6 col-lg-7 p-sm-2 p-lg-4 pb-5">
                        <div class="row hidden-sm-down">
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img27.svg')}}" alt="">
                                <h4>{{trans('main.crud.awesome_exp')}}</h4>
                                <p>{{trans('main.crud.modern_fresh_look')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img28.svg')}}" alt="">
                                <h4>{{trans('main.crud.free_for_clients')}}</h4>
                                <p>{{trans('main.crud.will_receive_call')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img30.svg')}}" alt="">
                                <h4>{{trans('main.ctc.cheaper')}} </h4>
                                <p>{{trans('main.ctc.pay_only_successful_calls_no_monthly_fixed_amount_or_special_rates')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img31.svg')}}" alt="">
                                <h4>{{trans('main.ctc.immediate_activation')}} </h4>
                                <p>{{trans('main.ctc.get_it_ready_in_few_minutes_there_is_no_need_to_wait')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img33.svg')}}" alt="">
                                <h4>{{trans('main.ctc.fully_customizable')}}</h4>
                                <p>{{trans('main.ctc.personalize_style_wait_message_restrict_availability_hours')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img34.svg')}}" alt="">
                                <h4>{{trans('main.ctc.woldwide_availability')}} </h4>
                                <p>{{trans('main.ctc.realiable_calls_connections_and_various_languages_available_for_your_snippets')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img40.svg')}}" alt="">
                                <h4>{{trans('main.ctc.protected_from_spam')}}</h4>
                                <p>{{trans('main.ctc.we_defend_you_from_cyberattacks_with_a_full_refund_protection_included')}}</p>
                            </div>
                            <div class="art col-sm-6">
                                <img src="{{asset('laravel_assets/images/front/img/img35.svg')}}" alt="">
                                <h4>{{trans('main.ctc.unlimited_call_channels')}}</h4>
                                <p>{{trans('main.ctc.there_is_no_need_to_pay_more_for_having_got_more_call_channels')}}</p>
                            </div>
                        </div>
                        <div class="slick_feature_area hidden-md-up">
                            <slick autoplay="true" autoplay-speed="7000" slides-to-show=1 class="hidden-md-up slick_services" settings="servicesSettings" ng-if="toggle2">
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img27.svg')}}" alt="">
                                    <h4>{{trans('main.crud.awesome_exp')}}</h4>
                                    <p>{{trans('main.crud.modern_fresh_look')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img28.svg')}}" alt="">
                                    <h4>{{trans('main.crud.free_for_clients')}}</h4>
                                    <p>{{trans('main.crud.will_receive_call')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img30.svg')}}" alt="">
                                    <h4>{{trans('main.ctc.cheaper')}} </h4>
                                    <p>{{trans('main.ctc.pay_only_successful_calls_no_monthly_fixed_amount_or_special_rates')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img31.svg')}}" alt="">
                                    <h4>{{trans('main.ctc.immediate_activation')}} </h4>
                                    <p>{{trans('main.ctc.get_it_ready_in_few_minutes_there_is_no_need_to_wait')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img33.svg')}}" alt="">
                                    <h4>{{trans('main.ctc.fully_customizable')}}</h4>
                                    <p>{{trans('main.ctc.personalize_style_wait_message_restrict_availability_hours')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img34.svg')}}" alt="">
                                    <h4>{{trans('main.ctc.woldwide_availability')}} </h4>
                                    <p>{{trans('main.ctc.realiable_calls_connections_and_various_languages_available_for_your_snippets')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img40.svg')}}" alt="">
                                    <h4>{{trans('main.ctc.protected_from_spam')}}</h4>
                                    <p>{{trans('main.ctc.we_defend_you_from_cyberattacks_with_a_full_refund_protection_included')}}</p>
                                </div>
                                <div class="art text-center">
                                    <img src="{{asset('laravel_assets/images/front/img/img35.svg')}}" alt="">
                                    <h4>{{trans('main.ctc.unlimited_call_channels')}}</h4>
                                    <p>{{trans('main.ctc.there_is_no_need_to_pay_more_for_having_got_more_call_channels')}}</p>
                                </div>
                            </slick>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="holder">
            <div ng-init="toggle1 = true" ng-click="toggle1 = !toggle1" data-toggle="collapse" href="#easyStep" aria-expanded="false" aria-controls="easyStep" class="toggler d-flex flex-row align-items-center pl-2 pr-2 pl-lg-4 pr-lg-4 pt-3 pb-3 pointer">
                <div class="triangle" ng-class="{toggled:toggle1}"></div>
                <h3 class="ml-2 mb-0">{{trans('main.crud.3_easy_steps')}}</h3>
            </div>
            <div class="collapse show pt-4 pb-5 pl-4 pr-4" id="easyStep">
                <div class="row m-0 hidden-sm-down">
                    <div class="art col-sm-4">
                        <img src="{{asset('laravel_assets/images/front/img/img36.svg')}}" height="45" alt="">
                        <h4>{{trans('main.ctc.create')}}</h4>
                        <p>{{trans('main.crud.customizalbe_snippet')}}</p>
                    </div>
                    <div class="art col-sm-4">
                        <img src="{{asset('laravel_assets/images/front/img/img37.svg')}}" width="45" height="45" alt="">
                        <h4>{{trans('main.ctc.implement')}}</h4>
                        <p>{{trans('main.crud.on_own_website')}}</p>
                    </div>
                    <div class="art col-sm-4">
                        <img src="{{asset('laravel_assets/images/front/img/img38.svg')}}" width="45" height="45" alt="">
                        <h4>{{trans('main.ctc.start_to_receive_calls')}}</h4>
                        <p>{{trans('main.crud.analize_stats')}}</p>
                    </div>
                </div>
                <div class="hidden-md-up slick_feature_area">
                    <slick slides-to-show=1 class="hidden-md-up slick_services" settings="servicesSettings" ng-if="toggle1">
                        <div class="art text-center">
                            <img src="{{asset('laravel_assets/images/front/img/img36.svg')}}" height="45" alt="">
                            <h4>{{trans('main.ctc.create')}}</h4>
                            <p>{{trans('main.crud.customizalbe_snippet')}}</p>
                        </div>
                        <div class="art text-center">
                            <img src="{{asset('laravel_assets/images/front/img/img37.svg')}}" width="45" height="45" alt="">
                            <h4>{{trans('main.ctc.implement')}}</h4>
                            <p>{{trans('main.crud.on_own_website')}}</p>
                        </div>
                        <div class="art text-center">
                            <img src="{{asset('laravel_assets/images/front/img/img38.svg')}}" width="45" height="45" alt="">
                            <h4>{{trans('main.ctc.start_to_receive_calls')}}</h4>
                            <p>{{trans('main.crud.analize_stats')}}</p>
                        </div>
                    </slick>    
                </div>
            </div>
        </div>
        <div class="holder advent">
            <div ng-init="togglePrice = true" ng-click="togglePrice = !togglePrice" data-toggle="collapse" href="#togglePrice" aria-expanded="false" aria-controls="togglePrice" class="toggler d-flex flex-row align-items-center pl-2 pr-2 pl-lg-4 pr-lg-4 pt-3 pb-3 pointer">
                <div class="triangle" ng-class="{toggled:togglePrice}"></div>
                <h3 class="ml-2 mb-0">{{trans('main.ctc.prices')}}</h3>
            </div>
            <div class="collapse show" id="togglePrice">
                <div class="pt-5 pb-5 col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2 mb-2 text-center" ng-controller="AuthenticationController">
                    <div class="row">
                        <div class="col-12 col-md-11 offset-md-1 col-lg-12 offset-lg-0 mb-2">
                            <div class="service service_n p-0">
                                <p class="">{{trans('main.ctc.price_depends_from_your_website_visitor_country')}}</p>
                                <div class="price_calc d-flex flex-column align-items-center justify-content-between flex-md-row">
                                    <div class="send_to_section">
                                        <p class="blue">{{trans('main.ctc.website_visitor_country')}}</p>
                                        <div class="button_holder">
                                            <button type="button" class="btn dropdown-toggle selected-phonenumber-image" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <img ng-src="{{asset('/laravel_assets/callburn/images/lang-flags')}}/@{{callRoutes[0].code}}.svg" alt="">
                                                <span>
                                                    <span class="country">@{{callRoutes[0].name}}</span><span class="prefix">(+@{{callRoutes[0].phonenumber_prefix}})</span>
                                                </span>
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
                                    <div class="message_length">
                                        <p class="blue">{{trans('main.ctc.call_lenght')}}</p>
                                        <div class="btn-group">
                                            <input class="form-control form_message_length" type="number" value="42" ng-model="selectedMessageLength" ng-blur="messageValidator()" step="1" id="example-number-input">
                                        </div>
                                        <span class="seconds_placeholder">{{ trans('main.vm.seconds') }}</span>
                                    </div>
                                    <div class="calc_section">
                                        <div class="text-center">
                                            <h2 class="text-white" ng-show="price * selectedMessageLength / 60 < 1.00">€ @{{price * selectedMessageLength / 60 | number:4 }}*</h2>
                                            <h2 class="text-white" ng-show="price * selectedMessageLength / 60 >= 1.00">€ @{{price * selectedMessageLength / 60 | number:2 }}*</h2>
                                            <span class="text-white" ng-show="price * selectedMessageLength / 60 * 100 < 100">{{ trans('main.ctc.nearly') }} @{{price * selectedMessageLength / 60 * 100 | number:0}} {{ trans('main.ctc.cents') }}</span>
                                            <p class="text-white">{{trans('main.ctc.success_connection')}}</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="">{{trans('main.crud.ctc_price_txt')}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('front.partials.customers_reviews_click_to_call')
    </div>
</div>