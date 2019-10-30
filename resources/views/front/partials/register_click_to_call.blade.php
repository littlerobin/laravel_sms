<div class="col-12 col-md-5 col-lg-5" ng-controller="AuthenticationController">
    <p class="mb-0">{{trans('main.vm.would_you_like_to_get_customers_using_just_a')}}<strong> #{{trans('main.vm.click')}}</strong>?</p>
    <p class="mb-0">{{trans('main.vm.now_you_can_with_callburn_click_to_call')}}</p>
    <p class="mb-0">{{trans('main.vm.start_now_to_simplify_your_communications_with')}} <strong>{{trans('main.vm.click_to_call')}}</strong></p>
    <form>
        <div class="">
            <div class="text-center">
                <small ng-show="wrongEmail" class="angular-hidden angular-wrong">
                    {{trans('main.crud.wrong_email_address')}}
                </small>
                <p ng-show="userAlreadyRegistered " class="angular-hidden">
                    <small class="angular-wrong">
                        {{trans('main.crud.user_already_registered_you_may_want_to')}}
                        <span class="user-already-registered" ng-click="redirect('login')">{{trans('main.crud.login_1')}}</span>
                        {{trans('main.crud.instead')}}
                    </small>
                </p>
            </div>
            <div class="edit-prof-holder">
                <input data-provide="typeahead" type="text" name="email" autocomplete="on" ng-class="{'has-type':registrationData.email_address}" ng-model="registrationData.email_address" ng-change="resetCredentials()" class="inp-placeholder text-center" id="exampleInputEmail1" placeholder="{{trans('main.vm.register_with_your_email_address')}}" ng-disabled="disableCredentials">
                <label for="email" class="lab-placeholder">
                    {{trans('main.vm.register_with_your_email_address')}}
                </label>
            </div>
        </div>
        <p class="privacy_text mb-0">{{ trans('main.cu.finish_registration_step4_by_clicking') }} 
            <strong>
                <a href="/privacy/#/?tab=2">{{ trans('main.cu.finish_registration_step4_privacy_policy') }}</a>
            </strong> {{ trans('main.cu.and') }} 
            <strong>
                <a href="/privacy/#/?tab=1">{{ trans('main.cu.finish_registration_step4_terms_and_conditions') }}</a>
            </strong>
        </p>
        <div class="mt-2 mb-2">
            <button type="submit" class="btn btn-success btn-full-width pointer" ng-click="registration()" ladda="loginLoading" ng-disabled="disableCredentials">
                {{trans('main.vm.try_it_now')}}
            </button>
        </div>
        <div class="check-your-email hidden">
            <p class="text-center text-success">
                {{trans('main.crud.check_your_email_address')}}
            </p>
        </div>
        <div class="">
            <p class="help-block text-center">{{trans('main.vm.or_sing_in_with')}}
                <span class="social">
                    <img ng-click="loginFacebook($event)" src="{{asset('/laravel_assets/images/front/img/fb_ico.svg')}}" alt="">
                    <img ng-click="loginGoogle($event)" src="{{asset('/laravel_assets/images/front/img/g+_ico.svg')}}" alt="">
                    <img ng-click="loginGitHub($event)" class="github-icon" src="{{asset('/laravel_assets/images/front/img/github.svg')}}" alt="">
                </span>
            </p>
        </div>
    </form>
</div>
