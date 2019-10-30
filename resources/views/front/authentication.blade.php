@extends('front.layouts.app-login')
@section('content')
<div class="login-page" ng-controller="AuthenticationController">
    <div id="main-content">
        <div class="container">
            <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-4 offset-lg-4 form-item mt-5 register_form_holder">
                <div class="tab-content text-center">
                    @switch($authPart)
                    @case('login')
                    <div ng-init="goToReset()" role="tabpanel" class="tab-pane fade show active" id="login">
                        <h2 class="text-center"></h2>
                        <div class="logo" ng-click="redirect()">
                            <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                        </div>
                        <form>
                            @include('front.partials.lang_selector')
                            <br>
                            <br>
                            <div class="">
                                <div class="edit-prof-holder">
                                    <input ng-change="resetCredentials()" ng-model="loginData.email" ng-class="{'has-type':loginData.email}" class="inp-placeholder text-center" id="exampleInputEmail1" placeholder="{{trans('main.crud.email_address')}}" name="main_mail">
                                    <label for="main_mail" class="lab-placeholder">
                                        {{trans('main.crud.email_address')}}
                                    </label>
                                </div>

                            </div>
                            <div class="">
                                <div class="edit-prof-holder">
                                    <input type="Password"  ng-class="{'has-type':loginData.password}" ng-change="resetCredentials()" ng-model="loginData.password" class="inp-placeholder text-center" id="exampleInputPassword1" placeholder="{{trans('main.crud.password')}}" name="main_pass">
                                    <label for="main_pass" class="lab-placeholder">
                                        {{trans('main.crud.password')}}
                                    </label>
                                </div>

                            </div>
                            <div class="">
                                <p>
                                    <!-- <small ng-show="wrongCredentials" class="angular-hidden angular-wrong">
                                        {{trans('main.crud.wrong_credentials')}}
                                    </small> -->

                                    <!-- <small ng-show="accountDeactivated" class="angular-hidden angular-wrong">
                                        {{trans('main.crud.we_are_sorry_your_account_was_blocked')}}
                                        <a href="{{url('click-to-call')}}">
                                            {{trans('main.crud.contact_us')}}
                                        </a>
                                        {{trans('main.crud.for_further_assistance')}}
                                    </small> -->
                                </p>
                                <p class="help-block">{{trans('main.crud.or_log_in_with')}}
                                    <span class="social">
                                        <img class="pointer" ng-click="loginFacebook($event)" src="{{asset('/laravel_assets/images/front/img/fb_ico.svg')}}" alt="">
                                        <img class="pointer" ng-click="loginGoogle($event)" src="{{asset('/laravel_assets/images/front/img/g+_ico.svg')}}" alt="">
                                        <img ng-click="loginGitHub($event)" class="github-icon pointer" src="{{asset('/laravel_assets/images/front/img/github.svg')}}" alt="">
                                    </span>
                                </p>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full-width pointer" ng-click="login()" ladda="loginLoading" data-style="expand-right">
                                {{trans('main.crud.login')}}
                            </button>
                            <div class="" style="margin-top: 30px;" ng-click="resetCredentials()">
                                <a id="recover_link" href="#recover" aria-controls="recover" role="tab" data-toggle="tab">{{trans('main.crud.have_you_got_troubles_')}}</a>
                            </div>
                            <hr>
                            <div class="">
                                <button ng-click="redirect('register')" class="btn btn-secondary pointer" aria-controls="create" role="tab" data-toggle="tab">{{trans('main.crud.create_a_callburn_account')}}</button>
                            </div>
                        </form>
                        <div class="register_modal animated" id="register_modal" ng-class="{'fadeIn show': showRegModal || showRegErrModal}">
                            <div class="body" ng-if="showRegModal">
                                <div class="check">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p>{{trans('main.crud.check_your_email_address')}}</p>
                                <button class="btn" ng-click="closeRegModal()">OK</button>
                            </div>
                            <div class="body err" ng-if="showRegErrModal">
                                <div class="err_x">
                                    <i class="fas fa-times"></i>
                                </div>
                                <p ng-show="wrongCredentials">
                                    {{trans('main.crud.wrong_credentials')}}
                                </p>
                                <p ng-show="accountDeactivated">
                                    {{trans('main.crud.we_are_sorry_your_account_was_blocked')}}
                                    <a href="{{url('click-to-call')}}">
                                        {{trans('main.crud.contact_us')}}
                                    </a>
                                    {{trans('main.crud.for_further_assistance')}}
                                </p>
                                <button class="btn" ng-click="closeLoginModal()">OK</button>
                            </div>
                        </div>
                    </div>
                    @break
                    @case('registration')
                    <div role="tabpanel" class="tab-pane fade show active" id="create">
                        <h2 class="text-center"></h2>
                        <div class="logo"  ng-click="redirect()">
                            <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                        </div>
                        <form>
                            @include('front.partials.lang_selector')
                            <br>
                            <br>
                            <div class="register-mobile" ng-disabled="emailSent" ng-class="{disabled: emailSent}">

                                <div class="">
                                    <div class="edit-prof-holder">
                                        <input ng-disabled="emailSent" ng-change="resetCredentials()" ng-model="registrationData.email_address" ng-class="{'has-type': registrationData.email_address, 'input-err': inputErr}" class="inp-placeholder text-center" id="exampleInputEmail1" placeholder="{{trans('main.crud.register_with_your_email_address')}}" name="reg_mail" ng-keypress="checkEnter($event, 'register', '.check-your-email')">
                                        <label for="reg_mail" class="lab-placeholder">
                                            {{trans('main.crud.register_with_your_email_address')}}
                                        </label>
                                    </div>
                                </div>

                                <div class="">
                                    <p class="help-block">{{trans('main.crud.or_log_in_with')}}
                                        <span class="social">
                                            <img class="pointer" ng-click="!emailSent ? loginFacebook($event) : null" src="{{asset('/laravel_assets/images/front/img/fb_ico.svg')}}" alt="">
                                            <img class="pointer" ng-click="!emailSent ? loginGoogle($event) : null" src="{{asset('/laravel_assets/images/front/img/g+_ico.svg')}}" alt="">
                                            <img ng-click="!emailSent ? loginGitHub($event) : null" class="github-icon pointer" src="{{asset('/laravel_assets/images/front/img/github.svg')}}" alt="">
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="">
                                    <div class="callburn-checkbox-white" ng-class="{'error':checkboxErr}">
                                        <input type="checkbox" id="agreeTerms" ng-disabled="emailSent" ng-checked="agreedTerms" ng-model="agreedTerms" ng-true-value="1" ng-false-value="0" ng-click="enableRegisterMail()" class="">
                                        <label for="agreeTerms" class="pt-0 ">
                                            <span class="small">
                                                {{trans('main.snippet.by_clicking_above_button_you_agree_to_the')}}
                                                <a href="/privacy/#/?tab=1" class="">{{trans('main.snippet.terms__conditions')}}</a>
                                                {{trans('main.cu.and')}}
                                                <a href="/privacy/#/?tab=2" class="">
                                                    {{trans('main.welcome.privacy_policy')}}
                                                </a>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="">
                                    <!-- <small ng-show="wrongEmail" class="angular-hidden angular-wrong font-weight-bold font-italic ">
                                        {{trans('main.crud.wrong_email_address')}}
                                    </small> -->
                                    <!-- <p ng-show="userAlreadyRegistered " class="angular-hidden mb-0">
                                        <small class="angular-wrong ">
                                            <strong class="">{{trans('main.crud.user_already_registered_you_may_want_to')}}
                                                <span class="user-already-registered " ng-click="redirect('login')">
                                                    {{trans('main.crud.login_1')}}
                                                </span>
                                                {{trans('main.crud.instead')}}
                                            </strong>
                                        </small>
                                    </p> -->
                                    <button type="submit" ng-disabled="emailSent || loginLoading" class="btn btn-primary btn-full-width pointer" ng-click="registration('.check-your-email')">
                                        {{trans('main.vm.register_with_your_email_address_button')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <!-- <div class="check-your-email hidden">
                            <p class="text-center" style="color: #00cf76;">
                               {{trans('main.crud.check_your_email_address')}}
                           </p>
                       </div> -->   
                       <div class="" style="margin-top: 30px;" ng-click="resetCredentials()">
                        <a id="recover_link" href="#recover" aria-controls="recover" role="tab" data-toggle="tab">{{trans('main.crud.have_you_got_troubles_')}}</a>
                    </div>
                    <hr>
                    <div class="">
                        <button class="btn btn-secondary pointer in_blue" ng-click="redirect('login')" aria-controls="login" role="tab" data-toggle="tab">{{trans('main.crud.go_back_to_login_page')}}</button>
                    </div>
                    <div class="register_modal animated" id="register_modal" ng-class="{'fadeIn show': showRegModal || showRegErrModal}">
                        <div class="body" ng-if="showRegModal">
                            <div class="check">
                                <i class="fas fa-check"></i>
                            </div>
                            <p>{{trans('main.crud.check_your_email_address')}}</p>
                            <button class="btn" ng-click="closeRegModal()">OK</button>
                        </div>
                        <div class="body err" ng-if="showRegErrModal">
                            <div class="err_x">
                                <i class="fas fa-times"></i>
                            </div>
                            <p ng-show="userAlreadyRegistered">
                                {{trans('main.crud.user_already_registered_you_may_want_to')}}
                                <span class="user-already-registered" ng-click="redirect('login')">
                                    {{trans('main.crud.login_1')}}
                                </span>
                                {{trans('main.crud.instead')}}
                            </p>
                            <p ng-show="wrongEmail" class="">
                                {{trans('main.crud.wrong_email_address')}}
                            </p>
                            <button class="btn" ng-click="closeRegModal('err')">OK</button>
                        </div>
                    </div>
                    <br>
                </div>
                @break

                @case('phoneNumberVerification')
                <div role="tabpanel" class="registration-tabs tab-pane fade @{{regVerificationStep != 4?'show active':''}}" id="verification_call">
                  <h2 class="text-center"></h2>
                  <div class="logo" ng-click="redirect()">
                    <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                </div>
                @include('front.partials.lang_selector')
                <br>
                <br>
                <p class="mb-0">{{trans('main.crud.verify_robot')}}</p>
                <p class="mb-2">{{trans('main.crud.callburn_require')}}</p>
                <p class="below">{{trans('main.crud.write_phone')}}</p>
                <small ng-show="wrongEmail" class="angular-hidden angular-wrong">
                    {{trans('main.crud.wrong_phonenumber')}}
                </small>
                <small ng-show="wrongEmailExpired" class="angular-hidden angular-wrong">
                    {{trans('main.sms.daily_max_limit_expired')}}
                </small>
                <form>
                    <input id="intel-input" class="@{{isValidNumberClass}} inp-placeholder" type="text" ng-keyup="validator()" ng-intl-tel-input ng-change="resetCredentials()" ng-model="finishRegistrationData.phonenumber" ng-keydown="addPrefix()" ng-class="wrongBorder" aria-label="...">
                    <div class="socket_verification">
                        <p ng-show="verification_status">
                            {{trans('main.sms.verification_call_status')}}:
                            <span class="status" ng-class="{'failed': verification_status == 'FAILED' ,'calling': verification_status == 'CALLING', 'succeed': verification_status == 'SUCCEED' }">
                                <span ng-if="verification_status == 'CALLING'">{{trans('main.sms.verification_calling_in_progress')}}</span>
                                <span ng-if="verification_status == 'FAILED'">{{trans('main.sms.failed')}}</span>
                                <span ng-if="verification_status == 'SUCCEED'">{{trans('main.sms.succeed')}}</span>
                            </span>
                        </p>
                    </div>
                    <div class="">
                        <p class="mb-2 only_tel">{{ trans('main.crud.we_use_only_telephone_call') }}</p>
                        <button type="submit" ng-disabled="finishUsernameReset?disableButton:true" class="btn btn-primary pointer" ng-click="sendVerificationCallReg()" ladda="loginLoading" data-style="expand-right">
                            {{trans('main.crud.send_a_verification_call')}}
                            <span ng-show="disableButton && finishUsernameReset">&nbsp;(@{{counter}})</span>
                        </button>
                    </div>
                </form>

                <div role="tabpanel" class="registration-tabs tab-pane hidden"  ng-show="regVerificationStep == 3" id="verification_code">
                    <form>
                        <div class="verification-code-step-2 center-block" >
                            <input ng-class="wrongVerificationCodeBorder" ng-change="resetCredentials();" ng-model="finishRegistrationData.voice_code" type="text" class="inp-placeholder" maxlength="4" aria-label="..." placeholder="{{trans('main.crud.verification_code')}}">

                            <small ng-show="wrongVerificationCode" class="angular-hidden angular-wrong">
                                {{trans('main.crud.wrong_verification_code')}}
                            </small>
                        </div>
                        <div class="">
                            <button type="submit" ng-disabled="disableVerificationCodeButton" ladda="verificationCodeLoading" data-style="expand-right" class="btn btn-success pointer" ng-click="validateVoiceCodeReg()">{{trans('main.crud.confirm_code')}}</button>
                        </div>
                    </form>
                </div>
                <div class="mb-2">
                    <!-- <a href="#" ng-show="regVerificationStep != 3" aria-controls="" role="" data-toggle="" ng-click="changeStep(4)" class="skip">{{trans('main.crud.skip')}}</a>
                        | -->
                        <a href="" id="chat-launch-button-contact-page" class="in_blue" aria-controls="" role="" data-toggle="">{{trans('main.crud.still_in_trouble_contact_us')}}</a>
                    </div>
                </div>
                <div role="tabpanel" class="registration-tabs tab-pane fade @{{regVerificationStep == 4?'show active':''}} hidden" id="make_password">
                    <h2 class="text-center"></h2>
                    <div class="logo" ng-click="redirect()">
                        <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                    </div>
                    <form>
                        <div class="edit-prof-holder">
                            <input type="text" name="myName" placeholder="Name" ng-class="{'has-type':myName}" ng-model="finishRegistrationData.myName" class="inp-placeholder">
                            <label class="lab-placeholder" for="myName">{{trans('main.sms.name')}}</label>
                        </div>
                        <div class="edit-prof-holder">
                            <input type="text" name="companyName" placeholder="Company name" ng-class="{'has-type':companyName}" ng-model="finishRegistrationData.companyName" class="inp-placeholder">
                            <label class="lab-placeholder" for="companyName">{{trans('main.sms.company_name')}}</label>
                        </div>
                        <div class="">
                            <input type="email" class="inp-placeholder disabled_mail" disabled class="inp-placeholder" value="@{{ finishRegistrationData.email }}">
                        </div>
                        <div class="confirmPass edit-prof-holder">
                            <input type="password" ng-change="resetCredentials()" ng-class="{'has-type':finishRegistrationData.password}" class="inp-placeholder" aria-label="..." placeholder="{{trans('main.crud.enter_a_password')}}" ng-model="finishRegistrationData.password" name="pass">
                            <label for="pass" class="lab-placeholder">{{trans('main.crud.enter_a_password')}}</label>
                        </div>
                        <div class="confirmPass edit-prof-holder">
                            <input type="password" ng-change="resetCredentials()" ng-class="{'has-type':finishRegistrationData.password_confirmation}" class="inp-placeholder" aria-label="..." placeholder="{{trans('main.crud.confirm_password')}}" ng-model="finishRegistrationData.password_confirmation" name="confpass">
                            <label for="confpass" class="lab-placeholder">{{trans('main.crud.confirm_password')}}</label>
                        </div>
                        <p>
                            <small ng-if="wrongCredentials" class="angular-wrong">
                                @{{ errorMessage }}
                            </small>
                        </p>
                        <div class="">
                            <button type="submit" ng-click="submitSaving()" ladda="loginLoading" class="btn btn-success">{{trans('main.crud.create_my_callburn_account')}}</button>
                        </div>
                    </form>
                    {{-- <div class="">
                        <p class="help-block">{{trans('main.crud.we_will_call_you_at_specified_phonenumber_to_verify_your_account_ownership')}}</p>
                    </div> --}}
                    {{-- <div class="">
                        <a href="" id="chat-launch-button-contact-page" class="in_blue" aria-controls="" role="" data-toggle="">{{trans('main.crud.still_in_trouble_contact_us')}}</a>
                    </div>
                    <hr>
                    <div class="">
                        <button ng-click="resetCredentials();changeStep(1)" class="btn btn-secondary pointer" aria-controls="recover" role="tab" data-toggle="tab">{{trans('main.crud.go_back')}}</button>
                    </div> --}}
                </div>
                @break

                @case('passwordReset')
                <div role="tabpanel" class="registration-tabs tab-pane fade show active" id="password-reset">
                    <h2 class="text-center"></h2>
                    <div class="logo"  ng-click="redirect()">
                        <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                    </div>

                    <div class="">
                        <small ng-show="wrongBorder" class="angular-hidden angular-wrong">
                            @{{resetPasswordErrorMessage}}
                        </small>
                        <input type="password" ng-class="wrongBorder" ng-change="resetCredentials()" class="inp-placeholder" aria-label="..." placeholder="{{trans('main.crud.enter_a_password')}}" ng-model="finishRegistrationData.password">
                    </div>
                    <div class="">
                        <input type="password" ng-class="wrongBorder" ng-change="resetCredentials()" class="inp-placeholder" aria-label="..." placeholder="{{trans('main.crud.confirm_password')}}" ng-model="finishRegistrationData.password_confirmation">
                    </div>

                    <div class="">
                        <button type="submit" ladda="loginLoading" data-style="expand-right" ng-click="resetPassword()" class="btn btn-success">{{trans('main.crud.set_password')}}</button>
                    </div>
                    <div class="">
                        <a href="" id="chat-launch-button-contact-page" class="in_blue" aria-controls="" role="" data-toggle="">{{trans('main.crud.still_in_trouble_contact_us')}}</a>
                    </div>
                    <hr>
                    <div class="" ng-click="resetCredentials()">
                        <a href="#recover" class="btn btn-secondary" aria-controls="recover" role="tab" data-toggle="tab">{{trans('main.crud.go_back')}}</a>
                    </div>

                </div>
                @break
                @case('usernameReset')
                @break
                @endswitch

                <div role="tabpanel" class="tab-pane fade" id="recover">
                    <h2 class="text-center"></h2>
                    <div class="logo"  ng-click="redirect()">
                        <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                    </div>
                    <form>
                        @include('front.partials.lang_selector')
                        <br>
                        <br>
                        <div class="">
                            <a href="#login_r" class="btn btn-primary btn-full-width pointer" aria-controls="login_r" role="tab" data-toggle="tab">{{trans('main.crud.recover_your_login')}}</a>
                        </div>
                        <div class="">
                            <a href="#password" class="btn btn-primary btn-full-width pointer" aria-controls="password" role="tab" data-toggle="tab">{{trans('main.crud.recover_your_password')}}</a>
                        </div>
                        <div class="">
                            <p class="help-block">
                                <small>
                                    <strong>
                                        {{trans('main.crud.note_login_should_be_an_email_address')}}
                                    </strong>
                                </small>
                            </p>
                        </div>
                        <div class="">
                            <a href="" id="chat-launch-button-contact-page" class="in_blue" aria-controls="" role="" data-toggle="">{{trans('main.crud.still_in_trouble_contact_us')}}</a>
                        </div>
                        <hr>
                        <div class="" >
                            <a href="#login" ng-click="resetCredentials()" class="btn btn-secondary pointer" aria-controls="login" role="tab" data-toggle="tab">{{trans('main.crud.go_back_to_login_page')}}</a>
                        </div>
                    </form>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="password">
                    <h2 class="text-center"></h2>
                    <div class="logo" ng-click="redirect()">
                        <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                    </div>
                    <form>
                        @include('front.partials.lang_selector')
                        <br>
                        <br>
                        <div class="">
                            <small ng-show="wrongEmail" class="angular-hidden angular-wrong">
                                {{trans('main.crud.wrong_email_address')}}
                            </small>
                            <input type="text" ng-model="resetPaswordEmail" ng-change="resetCredentials()" ng-class="wrongBorder" class="inp-placeholder text-center" id="exampleInputEmail3" placeholder="{{trans('main.crud.email_address')}}">
                        </div>
                        <div class=" clearfix" >
                            <button type="submit" class="btn btn-primary btn-full-width pointer" ng-click="sendPasswordResetLink()" ng-disabled="disableSendPasswordResetLink" ladda="loginLoading" data-style="expand-right">
                                {{trans('main.crud.recover_your_password')}}
                            </button>
                        </div>
                        <div class="">
                            <p class="angular-hidden" ng-show="emailMessage" ng-class="successOrWrong">
                             {{ trans('main.crud.a_reset_password_link_was_sent_to_the_specified_email_address_please_check_it') }}
                         </p>
                     </div>
                     <div ng-show="emailMessage">
                        <br>
                    </div>
                    <div class="">
                        <a href="" id="chat-launch-button-contact-page" class="in_blue" aria-controls="" role="" data-toggle="">{{trans('main.crud.still_in_trouble_contact_us')}}</a>
                    </div>
                    <hr>
                    <div class="">
                        <a href="#recover" ng-click="resetCredentials()" class="btn btn-secondary pointer" aria-controls="recover" role="tab" data-toggle="tab">{{trans('main.crud.go_back')}}</a>
                    </div>
                </form>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="login_r">
                <h2 class="text-center"></h2>
                <div class="logo" ng-click="redirect()">
                    <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                </div>
                <form>
                    @include('front.partials.lang_selector')
                    <br>
                    <br>
                    <small ng-show="wrongEmail" class="angular-hidden angular-wrong">
                        {{trans('main.crud.wrong_phonenumber')}}
                    </small>
                    <small ng-show="wrongNumberNotExist" class="angular-hidden angular-wrong">
                        {{trans('main.crud.phone_number_never_verified_in_callburn')}}
                    </small>
                    <small ng-show="dailyLimit" class="angular-hidden angular-wrong">
                        {{trans('main.crud.daily_maximum_retries_limit_reached')}}
                    </small>

                    <input id="intel-input" class="@{{isValidNumberClass}} inp-placeholder" type="text" ng-keyup="validator()" ng-intl-tel-input ng-change="resetRecoveryPhonenumber(verificationCall.phonenumber)" ng-model="verificationCall.phonenumber" ng-class="wrongBorder" aria-label="...">

                    <div class="">
                        <button ng-disabled="finishUsernameReset?disableButton:true" ng-click="makeResetcall()" type="submit" class="btn btn-success pointer" ladda="loginLoading" data-style="expand-right">
                            {{trans('main.crud.make_verification_call')}}
                            <span ng-show="disableButton && finishUsernameReset">&nbsp;(@{{counter}})</span>
                        </button>
                    </div>

                    <div class="verification-code-step-2 center-block angular-hidden" ng-show="recoverUsernameStep == 2">
                        <br>
                        <input ng-model="verificationCall.code" ng-class="wrongVerificationCodeBorder" ng-change="resetCredentials()" type="text" class="inp-placeholder ng-pristine ng-valid ng-valid-maxlength ng-touched" maxlength="4" aria-label="..." placeholder="{{trans('main.crud.verification_code')}}">
                        <small ng-show="wrongVerificationCode" class="angular-hidden angular-wrong">
                            {{trans('main.crud.wrong_verification_code')}}
                        </small>
                        <br><br>
                    </div>

                    <p ng-show="username" class="help-block">

                        {{trans('main.crud.your_username_is')}} <span class="angular-success"> @{{username}}</span>
                    </p>
                    <div class=" clearfix angular-hidden" ng-show="recoverUsernameStep == 2"  >
                        <button type="submit" class="btn btn-success " ng-click="checkVerification()" ng-disabled="disableVerificationCodeButton" ladda="verificationCodeLoading" data-style="expand-right">{{trans('main.crud.recover_my_login_now')}}</button>
                    </div>
                    <div class="">
                        <p class="help-block">{{trans('main.crud.we_will_call_you_at_specified_phonenumber_to_verify_your_account_ownership')}}</p>
                    </div>
                    <div class="">
                        <a href="" aria-controls="" role="" data-toggle="" id="chat-launch-button-contact-page" class="in_blue">{{trans('main.crud.still_in_trouble_contact_us')}}</a>
                    </div>
                    <hr>
                    <div class="" >
                        <a href="#recover" ng-click="resetCredentials()" class="btn btn-secondary pointer" aria-controls="recover" role="tab" data-toggle="tab">{{trans('main.crud.go_back')}}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>

@stop