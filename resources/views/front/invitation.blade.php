@extends('front.layouts.app-login')

@section('content')
    <div class="login-page" ng-controller="InvitationController">
        <div id="main-content">
            <div class="container">
                <div class="well well-default col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12">
                    <div class="tab-content text-center">
                        <div role="tabpanel" class="tab-pane fade in active" id="create">
                            <h2 class="text-center"></h2>
                            <div class="logo"  ng-click="redirect()">
                                <img src="{{asset('/laravel_assets/images/front/img/logo.svg')}}" alt="">
                            </div>
                            <form>
                                @include('front.partials.lang_selector')
                                <br>
                                <br>
                                <div class="form-group">
                                    <div ng-show="userAlreadyRegistered" class="angular-hidden">
                                        <small class="angular-wrong">
                                            {{trans('main.crud.user_already_registered_you_may_want_to')}}
                                            <span class="user-already-registered" ng-click="redirect('login')">
                                                {{trans('main.crud.login_1')}}
                                            </span>
                                            {{trans('main.crud.instead')}}
                                        </small>
                                    </div>
                                    <div ng-show="somethingWentWrong" class="angular-hidden angular-wrong">
                                        <small class="angular-wrong">
                                            {{ trans('main.crud.something_went_wrong') }}
                                        </small>
                                    </div>
                                    <small ng-show="accountDeactivated" class="angular-hidden angular-wrong">
                                        {{trans('main.crud.we_are_sorry_your_account_was_blocked')}}
                                        <a href="{{url('click-to-call')}}">
                                            {{trans('main.crud.contact_us')}}
                                        </a>
                                        {{trans('main.crud.for_further_assistance')}}
                                    </small>
                                    <div class="edit-prof-holder form-group">
                                        <input
                                            class="inp-placeholder form-control input-lg"
                                            type="email"
                                            ng-value="email"
                                            ng-disabled="true"
                                        >
                                    </div>
                                    <div ng-show="wrongCredentials" class="angular-hidden angular-wrong"  style="margin-bottom: 10px;">
                                        @{{ errorMessage }}
                                    </div>
                                    <div class="form-group">
                                        <input
                                            class="inp-placeholder form-control input-lg"
                                            type="password"
                                            ng-change="resetCredentials()"
                                            ng-class="wrongBorder"
                                            placeholder="{{trans('main.crud.enter_a_password')}}"
                                            ng-model="registrationData.password"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <input
                                            type="password"
                                            ng-change="resetCredentials()"
                                            ng-class="wrongBorder"
                                            class="inp-placeholder form-control input-lg"
                                            placeholder="{{trans('main.crud.confirm_password')}}"
                                            ng-model="registrationData.password_confirmation"
                                        >
                                    </div>
                                </div>
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg col-xs-12 register-user"
                                    ng-click="registration()"
                                    data-style="expand-right"
                                >
                                    {{trans('main.crud.register_with_your_email_address')}}
                                </button>
                            </form>
                            <div class="form-group">
                                <p class="help-block">
                                    {{trans('main.crud.or_log_in_with')}}
                                    <span class="social">
                                        <a href="#" ng-click="loginFacebook($event)">
                                            <img src="{{asset('/laravel_assets/images/front/img/fb_ico.svg')}}" alt="">
                                        </a>
                                        <a href="#" ng-click="loginGoogle($event)">
                                            <img src="{{asset('/laravel_assets/images/front/img/g+_ico.svg')}}" alt="">
                                        </a>
                                        <a href="#" ng-click="loginGitHub($event)">
                                            <img class="github-icon" src="{{asset('/laravel_assets/images/front/img/github.svg')}}" alt="">
                                        </a>
                                    </span>
                                </p>
                            </div>
                            <p class="privacy_text">{{ trans('main.cu.finish_registration_step4_by_clicking') }} <strong><a href="/privacy/#/?tab=2">{{ trans('main.cu.finish_registration_step4_privacy_policy') }}</a></strong> {{ trans('main.cu.and') }} <strong><a href="/privacy/#/?tab=1">{{ trans('main.cu.finish_registration_step4_terms_and_conditions') }}</a></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
