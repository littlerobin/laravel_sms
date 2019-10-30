<html lang="en" ng-app='frontCallburnApp' ng-controller='FrontController'>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--<link rel="stylesheet" type="text/css" href="/dist/assets/css/style.css">-->
    <link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/font.css">
    <link rel="stylesheet" type="text/css" href="/bower_components/angular-datepicker/dist/angular-datepicker.css">
    <link rel="stylesheet" type="text/css" href="/bower_components/angular-tooltips/dist/angular-tooltips.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/main.css">
    <link rel="stylesheet" type="text/css" href="/bower_components/angular-rangeslider/angular.rangeSlider.css">
    <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.5/select2.css">
    <link rel="stylesheet" type="text/css" href="/bower_components/angular-ui-select/dist/select.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans" rel="stylesheet">
    <!-- <script src="//static.intercomcdn.com/intercom.v1.js"></script> -->
    <script type="text/javascript">(function (d, n) {
            var s, a, p;
            s = document.createElement("script");
            s.type = "text/javascript";
            s.async = true;
            s.src = (document.location.protocol === "https:" ? "https:" : "http:") + "//cdn.nudgespot.com" + "/nudgespot.js";
            a = document.getElementsByTagName("script");
            p = a[a.length - 1];
            p.parentNode.insertBefore(s, p.nextSibling);
            window.nudgespot = n;
            n.init = function (t) {
                function f(n, m) {
                    var a = m.split('.');
                    2 == a.length && (n = n[a[0]], m = a[1]);
                    n[m] = function () {
                        n.push([m].concat(Array.prototype.slice.call(arguments, 0)))
                    }
                }

                n._version = 0.1;
                n._globals = [t];
                n.people = n.people || [];
                n.params = n.params || [];
                m = "track register unregister identify set_config people.delete people.create people.update people.create_property people.tag people.remove_Tag".split(" ");
                for (var i = 0; i < m.length; i++)f(n, m[i])
            }
        })(document, window.nudgespot || []);
        nudgespot.init("ac07d3cdd806ce9934fbd4763d3dc172");</script>
    <!-- Piwik -->
    <!--<script type="text/javascript">
      var _paq = _paq || [];
      _paq.push(['trackPageView']);
      _paq.push(['enableLinkTracking']);
      (function() {
        var u="//10.0.1.41/piwik/";
        _paq.push(['setTrackerUrl', u+'piwik.php']);
        _paq.push(['setSiteId', 1]);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <noscript><p><img src="//10.0.1.41/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>-->
    <!-- End Piwik Code -->

    <script type="text/javascript">
        function modalWindowPosition() {
            var window_height = window.outerHeight;
            var window_width = window.outerWidth;
            document.getElementById('modal_background_effect1').style.width = window_width;
            document.getElementById('modal_background_effect1').style.height = window_height;
            document.getElementById('modal_background_effect2').style.width = window_width;
            document.getElementById('modal_background_effect2').style.height = window_height;
            document.getElementById('modal_background_effect3').style.width = window_width;
            document.getElementById('modal_background_effect3').style.height = window_height;
            document.getElementById('modal_background_effect4').style.width = window_width;
            document.getElementById('modal_background_effect4').style.height = window_height;
            document.getElementById('modal_window').style.height = window_height;
        }


        function pageScroll() {
            window.scrollBy(0, 50);
            scrolldelay = setTimeout('pageScroll()', 200);
        }


    </script>

</head>
<body>
<div id="modal_background_effect1" class="modal_background_effect" ng-show="showLoginModal"></div>
<div id="modal_background_effect2" class="modal_background_effect" ng-show="showRegistrationModal"></div>
<div id="modal_background_effect3" class="modal_background_effect" ng-show="showConditionsModal"></div>
<div id="modal_background_effect4" class="modal_background_effect" ng-show="showPrivacyModal"></div>
<header>
    <div class="header">
        <div class="header_logo_container">
            <img src="assets/callburn/images/front_account/callburn_logo.png" class="header_logo"/>
        </div>
        <div class="header_choose_lang_container">
            <span class="header_choose_lang_sp header_choose_lang_sp1">{{trans('header_choose_language')}}:</span>
            <div class="header_lang_icon_container">
                <callburn-select ng-model="language"
                                 options="languages"
                                 show-attr="name"
                                 keep-attr='code'
                                 image-attr="flags"
                                 image-url="/assets/callburn/images/flags/{{currentLanguage}}.png"
                                 select-text="{{currentLanguageName | uppercase}}"
                                 ng-change="changeLanguage(language)"
                                 class="no-border-select">
                </callburn-select>
            </div>
        </div>
        <div class="header_right_container">
            <span ng-init="showRegistrationModal = false;" ng-click="showRegistrationModal = true;"
                  class="header_right_sp">{{trans('header_register')}}</span>
            <span ng-init="showLoginModal = false;" ng-click="showLoginModal = true;" class="header_right_sp">{{trans('header_login')}}</span>
        </div>
    </div>
    <div class="header_menu_container">
        <div class="phone_menu_container" ng-click="phoneMenu()">
            <span class="menu">Menu</span>
            <div class="menu_icon_line_container">
                <span class="menu_icon_line menu_icon_line1" id="menu_icon_line1"></span>
                <span class="menu_icon_line menu_icon_line2" id="menu_icon_line2"></span>
                <span class="menu_icon_line menu_icon_line3" id="menu_icon_line3"></span>
            </div>
        </div>
        <div class="phone_dropdown_menu" ng-show="showPhoneMenu">
            <span ui-sref="home-page" class="phone_menu_content">{{trans('header_menu_callburn')}}?</span>
            <span ui-sref="price" class="phone_menu_content">{{trans('menu_pricing')}}</span>
            <span class="phone_menu_content">{{trans('menu_docs')}}</span>
            <span ui-sref="contact-us" class="phone_menu_content">{{trans('menu_contact_us')}}</span>
        </div>
        <div class="header_menu_container_center">
						<span ui-sref="home-page" class="header_menu_content" id="header_menu_callburn">
							<span class="header_menu_content_child">{{trans('header_menu_callburn')}}?</span>
						</span>						
						<span ui-sref="price" class="header_menu_content" id="header_menu_picing">
							<span class="header_menu_content_child">{{trans('menu_pricing')}}</span>
						</span>						
						<span class="header_menu_content" id="header_menu_docs">
							<span class="header_menu_content_child">{{trans('menu_docs')}}</span>
						</span>
						<span ui-sref="contact-us" class="header_menu_content" id="header_menu_contact_us">
							<span class="header_menu_content_child">{{trans('menu_contact_us')}}</span>
						</span>
        </div>
    </div>
</header>


<!-- Login modal -->
<div class="modal_window modal_open modaleffect" id="modal_window" ng-show="showLoginModal">
    <div class="modal_container" id="modal_container">
        <img src="assets/callburn/images/front_account/x-icon.png" ng-click="showLoginModal = false;"
             class="close_modal">
        <span class="contact_us_sp1"><b>{{trans('login_modal_login_into_callburn1')}}</b> {{trans('login_modal_login_into_callburn2')}}</span>
        <span class="contact_us_sp5">({{trans('login_modal_text_we_are_happy')}})</span>
        <span class="check_email_sp" ng-show="checkEmail">Please check your email for activating your account</span>
        <div class="modal_input_container">
            <div class="modal_input_content">
                <img src="assets/callburn/images/front_account/postal-code-icon.png" class="modal_icons"/>
                <input type="email" placeholder="{{trans('login_modal_input_placeholder1')}}" ng-model="loginData.email"
                       class="modal_input"/>
            </div>
            <div class="modal_input_content">
                <img src="assets/callburn/images/front_account/key-icon.png" class="modal_icons"/>
                <input type="password" placeholder="{{trans('login_modal_input_placeholder2')}}"
                       ng-model="loginData.password" class="modal_input"/>
            </div>
        </div>
        <div class="invalid_login" ng-show="showInvalidLogin">
            <img src="assets/callburn/images/front_account/fill-1.png"/>
            <span>{{trans('login_modal_error')}}</span>
        </div>
        <div class="modal_container_sms" ng-click="login()">
            <img src="assets/callburn/images/front_account/postal-code-icon-copy.png" class="modal_icons">
            {{trans('login_modal_credentials')}}
        </div>
        <div class="modal_container_social">
            <div class="modal_container_social_fb" ng-click="loginFacebook()">
                <img src="assets/callburn/images/front_account/facebook.png" class="social_icons">
                {{trans('login_modal_fb')}}
            </div>
            <div class="modal_container_social_google" ng-click="loginGoogle()">
                <img src="assets/callburn/images/front_account/google.png" class="social_icons social_icons1">
                {{trans('login_modal_google')}}
            </div>
        </div>
        <span class="login_modal_sp1">{{trans('login_modal_cant_login1')}} <b><span class="login_modal_sp2">{{trans('login_modal_cant_login2')}}</span></b></span>
        <div class="modal_bottom">
            <img src="assets/callburn/images/front_account/arrow-icon.png" class="front_modal_arrow_icon"/>
            <span class="contact_us_sp1"><b>{{trans('login_modal_still_not_registered1')}}</b> {{trans('login_modal_still_not_registered2')}}</span>
            <span class="contact_us_sp5">(<b>{{trans('login_modal_register_now1')}}</b> {{trans('login_modal_register_now2')}})</span>
        </div>
    </div>
</div>


<!-- /Login modal -->


<!-- Registration modal -->
<div class="modal_window modaleffect" ng-show="showRegistrationModal">
    <div class="modal_container">
        <img src="assets/callburn/images/front_account/x-icon.png" ng-click="showRegistrationModal = false;"
             class="close_modal">
        <span class="contact_us_sp1">{{trans('reg_modal_still_not_registered1')}} <b>{{trans('reg_modal_still_not_registered2')}}</b></span>
        <span class="contact_us_sp5">({{trans('reg_modal_text_do_it_now')}})</span>
        <div class="modal_input_container">
            <div class="modal_input_content">
                <img src="assets/callburn/images/front_account/postal-code-icon.png" class="modal_icons"/>
                <input type="email" placeholder="{{trans('login_modal_input_placeholder1')}}"
                       ng-model="registrationData.email_address" class="modal_input"/>
            </div>
            <!-- <div class="modal_input_content">
                <img src="assets/callburn/images/front_account/key-icon.png" class="modal_icons" />
                <input type="password" placeholder="{{trans('login_modal_input_placeholder2')}}" ng-model="registrationData.password" class="modal_input" />
            </div>
            <div class="modal_input_content">
                <img src="assets/callburn/images/front_account/key-icon.png" class="modal_icons" />
                <input type="password" placeholder="{{trans('login_modal_input_placeholder3')}}" ng-model="registrationData.password_confirmation" class="modal_input" />
            </div> -->
        </div>
        <span class="reg_modal_sp1">{{trans('reg_modal_by_clicking_one1')}}</span>
        <span class="reg_modal_sp1"><b>{{trans('reg_modal_by_clicking_one2')}}</b> <b ng-click="PrivacyModal()"
                                                                                      style="cursor: pointer;">{{trans('reg_modal_by_clicking_one2_1')}}</b> {{trans('reg_modal_by_clicking_one3')}} <b
                ng-click="ConditionsModal()" style="cursor: pointer;">{{trans('reg_modal_by_clicking_one4')}}</b></span>
        <div class="modal_container_sms" ng-click="registration()">
            <img src="assets/callburn/images/front_account/postal-code-icon-copy.png" class="modal_icons">
            {{trans('login_modal_credentials1')}}
        </div>
        <div class="modal_container_social">
            <div class="modal_container_social_fb" ng-click="loginFacebook()">
                <img src="assets/callburn/images/front_account/facebook.png" class="social_icons">
                {{trans('login_modal_fb2')}}
            </div>
            <div class="modal_container_social_google" ng-click="loginGoogle()">
                <img src="assets/callburn/images/front_account/google.png" class="social_icons social_icons1">
                {{trans('login_modal_google3')}}
            </div>
        </div>
        <div class="modal_bottom">
            <img src="assets/callburn/images/front_account/arrow-icon.png" class="front_modal_arrow_icon"/>
            <span class="contact_us_sp1"><b>{{trans('reg_modal_already_registered1')}}</b> {{trans('reg_modal_already_registered2')}}</span>
            <span class="contact_us_sp5">({{trans('reg_modal_go_to_login1')}} <b>{{trans('reg_modal_go_to_login2')}}</b>)</span>
        </div>
    </div>
</div>
<!-- /Registration modal -->

<!-- Privacy policy modal -->
<div class="modal_window" ng-show="showPrivacyModal">
    <div class="modal_container1">
        <img src="assets/callburn/images/front_account/x-icon.png" ng-click="showPrivacyModal = false"
             class="close_modal">
        <span
            class="contact_us_sp1"><b>{{trans('privacy_modal_privacy1')}}</b> {{trans('privacy_modal_privacy2')}}</span>
        <span class="contact_us_sp5">({{trans('privacy_modal_privacy3')}})</span>
        <div class="privacy_modal_content">
            <p class="conditions_modal_sp">{{trans('privacy_modal_text_at_callburn')}}</p>
            <p class="conditions_modal_sp">1. {{trans('privacy_modal_text_collect_information')}}:</p>
            <ul>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_provide_information')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_providing_and_personalizing')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_manage_orders')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_registration_documents')}}</span>
                <p class="conditions_modal_sp">2. {{trans('privacy_modal_text_do_not_use_your_data')}}</p>
                <p class="conditions_modal_sp">3.
                    {{trans('privacy_modal_text_we_do_notrevealany_personal_information')}}</p>
                <p class="conditions_modal_sp">4. {{trans('privacy_modal_text_werespect_the_spanish_provisions')}}</p>
                <p class="conditions_modal_sp">5. {{trans('privacy_modal_text_the_information_withinourdatabases')}}</p>
                <p class="conditions_modal_sp">6. {{trans('privacy_modal_text_we_take_severalsteps')}}</p>
                <span
                    class="conditions_modal_sp">• {{trans('privacy_modal_text_internal_responsible_for_compliance')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_complianceMonitoring_manager')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_two_factorauthentication')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_limited_time')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_access_based')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_for_critical_business')}}</span>
                <span
                    class="conditions_modal_sp">• {{trans('privacy_modal_text_registration_and_detailedmonitoring')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_policy_outsourcing')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_policy_data_retention')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_employees_must')}}</span>
                <p class="conditions_modal_sp">7. {{trans('privacy_modal_text_we_use_cookies')}}</p>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_following_cookies')}}</p>
                <p class="conditions_modal_sp">1. • {{trans('Google Analytics')}}</p>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_with_google_analytics')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_data_collected')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_duration_of_data')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_data_controller')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_cookies_in_the_thirdgroup')}}</span>
                <p class="conditions_modal_sp">2. • {{trans('privacy_modal_text_optimizely')}}</p>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_optimizelyitallowsus')}}</p>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_data_collected_visits')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_ismonitored_by_optimizely')}}</span>
                <span
                    class="conditions_modal_sp">• {{trans('privacy_modal_text_data_controller_callburn_services')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_cookies_both')}}</span>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_ifyouwant_to_disable')}} <a
                        href="https://.optimizely.com/OPT_OUT">(https: // .optimizely.com / OPT_OUT)</a>.</p>
                <p class="conditions_modal_sp">3. • {{trans('privacy_modal_text_crazy_egg')}}</p>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_crazyEggallowsus')}}</p>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_if_users_are')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_ismonitored_by_CrazyEgg')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_data_controller')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_cookies_in_the_thirdgroup')}}</span>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_because_CrazyEgg')}} <a
                        href="https://.crazyegg.com/opt-out">(https://.crazyegg.com/opt-out)</a>.</p>
                <p class="conditions_modal_sp">3. • {{trans('privacy_modal_text_vero')}}</p>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_with_vero')}}</p>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_name_email_address')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_ismonitored_by_vero')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_data_controller')}}</span>
                <span class="conditions_modal_sp">• {{trans('privacy_modal_text_cookies_in_the_thirdgroup')}}</span>
                <p class="conditions_modal_sp">{{trans('privacy_modal_text_receive_emails')}}</p>
                <p class="conditions_modal_sp">8. {{trans('privacy_modal_text_you_have_the_right')}}</p>
                <p class="conditions_modal_sp">9. {{trans('privacy_modal_text_given_that_the_internet')}}</p>

        </div>
        <span class="contact_us_sp1">{{trans('privacy_modal_any_questions1')}} <b>{{trans('privacy_modal_any_questions2')}}?</b></span>
        <span class="contact_us_sp5">(<b>{{trans('privacy_modal_any_questions3')}}</b> {{trans('privacy_modal_any_questions4')}})</span>
        <span class="privacy_btn"
              ng-click="showPrivacyModal = false;">{{trans('button_check_close_this_window')}}</span>
    </div>
</div>
<!-- /Privacy policy modal -->


<!-- Terms and Conditions modal -->
<div class="modal_window" ng-show="showConditionsModal">
    <div class="modal_container1">
        <img src="assets/callburn/images/front_account/x-icon.png" ng-click="showConditionsModal = false"
             class="close_modal">
        <span class="contact_us_sp1"><b>{{trans('terms_conditions_modal_terms1')}}</b> {{trans('terms_conditions_modal_terms2')}}</span>
        <span class="contact_us_sp5">({{trans('terms_conditions_modal_terms3')}})</span>
        <div class="privacy_modal_content">
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_terms_and_conditions')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_the_following_terms')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_by_registering')}}</p>
            <p class="conditions_modal_sp">1. {{trans('terms_conditions_modal_text_general')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_callburn_services_reserves')}}</p>
            <p class="conditions_modal_sp">2. {{trans('terms_conditions_modal_text_services_costs')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_all_prices_mentioned')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_by_registering')}}</p>
            <p class="conditions_modal_sp">3. {{trans('terms_conditions_modal_text_user_obligations')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_the_user_must')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_by_registering')}}</p>
            <span class="conditions_modal_sp">•{{trans('terms_conditions_modal_text_the_user_is')}}</span>
            <span
                class="conditions_modal_sp">•{{trans('terms_conditions_modal_text_callburn_services_considers')}}</span>
            <span
                class="conditions_modal_sp">•{{trans('terms_conditions_modal_text_callburn_services_or_interests')}}</span>
            <span class="conditions_modal_sp">•{{trans('terms_conditions_modal_text_for_any_other_act')}}</span>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_complaints_regarding_services')}}</p>
            <span class="conditions_modal_sp">1. {{trans('terms_conditions_modal_text_spam')}}</span>
            <span class="conditions_modal_sp">2. {{trans('terms_conditions_modal_text_violation_of_copyright')}}</span>
            <span class="conditions_modal_sp">3. {{trans('terms_conditions_modal_text_deception_to_others')}}</span>
            <span class="conditions_modal_sp">4. {{trans('terms_conditions_modal_text_abuse_of_texts')}}</span>
            <span
                class="conditions_modal_sp">5. {{trans('terms_conditions_modal_text_offering_products_or_services')}}</span>
            <span class="conditions_modal_sp">6. {{trans('terms_conditions_modal_text_comply_with_spanish_law')}}</span>
            <p class="conditions_modal_sp">4. {{trans('terms_conditions_modal_text_payment_terms')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_the_services_provided')}}</p>
            <p class="conditions_modal_sp">5. {{trans('terms_conditions_modal_text_general_liability')}}</p>
            <p class="conditions_modal_sp">
                {{trans('terms_conditions_modal_text_callburn_services_is_never_liable')}}</p>
            <p class="conditions_modal_sp">6. {{trans('terms_conditions_modal_text_privacy')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_callburn_services_does_not')}}</p>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_limited_time')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_two_factor')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_access_based')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_the_principle')}}</span>
            <span
                class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_registration_and_detailed')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_policy_outsourcing')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_policy_data_retention')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_head_of_compliance')}}</span>
            <span class="conditions_modal_sp">• {{trans('terms_conditions_modal_text_required_a_certificate')}}</span>
            <p class="conditions_modal_sp">7. {{trans('terms_conditions_modal_text_miscellaneous')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_if_the_agreement')}}</p>
            <p class="conditions_modal_sp">8. {{trans('terms_conditions_modal_text_spanish_law')}}</p>
            <p class="conditions_modal_sp">{{trans('terms_conditions_modal_text_for_any_agreement')}}</p>
        </div>
        <span class="contact_us_sp1">{{trans('privacy_modal_any_questions1')}} <b>{{trans('privacy_modal_any_questions2')}}?</b></span>
        <span class="contact_us_sp5">(<b>{{trans('privacy_modal_any_questions3')}}</b> {{trans('privacy_modal_any_questions4')}})</span>
        <span class="privacy_btn"
              ng-click="showConditionsModal = false;">{{trans('button_check_close_this_window')}}</span>
    </div>
</div>
<!-- /Terms and Conditions modal -->


<div ui-view style="background: #fff;"></div>


<footer>
    <div class="footer_ios_android_icon_container">
        <img src="assets/callburn/images/front_account/app-store-icon.png" class="ios_android_icons">
        <img src="assets/callburn/images/front_account/android-icon.png" class="ios_android_icons">
    </div>
    <span class="footer_sp1">{{trans('footer_text_our')}} <b>{{trans('footer_text_customers_are_happy')}}</b></span>
    <span class="footer_sp2">({{trans('footer_text_and_you')}})</span>
    <div class="footer_fb_google_icon_container">
        <img src="assets/callburn/images/front_account/facebook-icon.png" class="fb_google_icons">
        <img src="assets/callburn/images/front_account/google-plus-icon.png" class="fb_google_icons">
    </div>
    <div class="footer_center_line"></div>
    <div class="footer_logo_container">
        <img src="assets/callburn/images/front_account/callburn_logo.png" class="footer_logo"/>
    </div>
    <span class="footer_sp3">{{trans('footer_logo_bottom_text_contact_support')}}</span>
    <span class="footer_sp3">&#169; {{trans('footer_logo_bottom_text_copyright')}}</span>
    <span class="footer_sp3">{{trans('footer_logo_bottom_text_reserved')}}</span>
    <span class="footer_sp4"><span ng-click="ConditionsModal()" style="cursor: pointer;">{{trans('reg_modal_by_clicking_one4')}}</span> <span
            ng-click="PrivacyModal()" style="margin-left: 15px; cursor: pointer;">{{trans('reg_modal_by_clicking_one2_1')}}</span></span>
    <div class="footer_menu_container">
        <span ui-sref="home-page" class="footer_menu_content">{{trans('header_menu_callburn')}}?</span>
        <span ui-sref="price" class="footer_menu_content">{{trans('menu_pricing')}}</span>
        <span class="footer_menu_content">{{trans('menu_docs')}}</span>
        <span ui-sref="contact-us" class="footer_menu_content">{{trans('menu_contact_us')}}</span>
    </div>
</footer>




<script src="/dist/assets/js/front.min.js" type="text/javascript"></script>
<script src="/dist/app.js" type="text/javascript"></script>
<script type="text/javascript">
    modalWindowPosition();
</script>
</body>
</html>