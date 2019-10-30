@extends('front.layouts.app')

@section('title')
- Contact Us
@endsection

@section('content')

<div id="main-content" class="">
    <div class="container">
        <div class="row">
            @include('front.partials.register_contact_us')
            @include('front.partials.map')
        </div>
        <div class="row text-center">
            <h2 class="col-12">{{trans('main.cu.how_can_we_help_you')}}</h2>
            <div class="col-12 col-md-6 col-lg-4 offset-lg-2">
                <div class="pt-2 pb-2 service" id="chat-launch-button">
                 <img src="{{asset('/laravel_assets/images/front/img/img42.svg')}}" alt="">
                 <h5>
                    <strong>{{trans('main.cu.live_chat_with_our_team')}}</strong>
                </h5>
                <p>{{trans('main.cu.we_are_online_in_various_different_timezone_and_our_team_will_be')}}</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="pt-2 pb-2 service">
                <div ng-click="redirect('documentation')">
                    <img src="{{asset('/laravel_assets/images/front/img/img43.svg')}}" alt="">
                    <h5>
                        <strong>{{trans('main.cu.look_our_documentation')}}</strong>
                    </h5>
                    <p>{{trans('main.cu.because_is_very_detailed_and_simple_created_for_both_users_and_developers')}} :-)</p>
                </div>
            </div>
        </div>
    </div>
    @include('front.partials.modals.contact_us_documentation_modal')
</div>
</div>

@stop
