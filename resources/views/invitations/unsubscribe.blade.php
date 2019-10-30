@extends('front.layouts.app-login')

@section('content')
<div class="unsubscribe_page">
    <div class="container">
        <div class="row">
            <div class=" col-12 col-sm-6 text-center">
                <img src="{{ asset('/laravel_assets/images/unsub_icon.png') }}" alt="Logo">
            </div>

            <div class="text-center col-12 col-sm-6 content_section">
                <img src="{{ asset('/laravel_assets/images/OK.png') }}" alt="Logo">
                <h3 class="text-uppercase">{{ trans('main.welcome.unsubscribe_title') }}</h3>
                <p>{{ trans('main.welcome.successfully_unsubscribe') }}</p>
                <p class="question">{{ trans('main.welcome.question_to_unsub') }}</p>
                <a href="/invitations/{{ $token }}/subscribe">{{ trans('main.welcome.unsubscribe_button') }}</a>
                <p>{{ trans('main.welcome.sorry_otherwise') }}</p>
                <a class="text-uppercase" href="/">go back to callburn</a>
            </div>
        </div>
    </div>
</div>
@stop
