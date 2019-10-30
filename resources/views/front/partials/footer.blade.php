<footer class="pl-md-5 pr-md-5" ng-controller="AuthenticationController">
    <div class="reserved pt-4 pb-3 text-center">
        <a href="/privacy/#/?tab=1">
            <strong class="font-weight-bold">
                {{trans('main.welcome.terms_and_conditions')}}
            </strong>
        </a>
        <strong>|</strong>
        <a href="/privacy/#/?tab=2">
            <strong class="font-weight-bold">
                {{trans('main.welcome.privacy_policy')}}
            </strong>
        </a>
        <strong>|</strong>
        <a href="/affiliation"><strong>{{trans('main.crud.affiliate_program')}}</strong></a>
        <h2 class="m-0 p-0 text-center">
            callburn.com - {{trans('main.crud.all_right_reserved')}}
        </h2>
    </div>
        <!-- <div class="col-12 text-center mt-2 mb-4">
                <div class="social" ng-controller="AuthenticationController">
                    <a href="https://www.youtube.com/channel/UCurYfwUt5Lhnp-yZFJR8FRQ/videos" target="_blank">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/youtube.png')}}" alt="">
                    </a>
                    <a href="https://www.facebook.com/callburn.services/?fref=ts" target="_blank">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/facebook.png')}}" alt="">
                    </a>
                    <a href="#" id="chat-launch-button-contact-page" target="_blank">
                        <img src="{{asset('/laravel_assets/images/mainStyleImages/mailto.png')}}" alt="">
                    </a>
                </div>
                <div class="policy">
                    <ul class="nav d-flex flex-row justify-content-center align-items-center">
                        <li role="presentation" class="mr-2">
                            <a href="/privacy/#/?tab=1">
                                <strong class="font-italic">
                                    {{trans('main.welcome.terms_and_conditions')}}
                                </strong>
                            </a>
                        </li>
                        <li role="presentation" class="ml-2">
                            <a href="/privacy/#/?tab=2">
                                <strong class="font-italic">
                                    {{trans('main.welcome.privacy_policy')}}
                                </strong>
                            </a>
                        </li>
                    </ul>
                </div>
            </div> -->
        <!-- <div class="col-12">
                <h6 class="text-center text-white">
                    © Copyright 2018 - callburn.com
                    {{trans('main.welcome.all_rights_reserved')}}
                </h6>
            </div> -->
        </div>
    </footer>