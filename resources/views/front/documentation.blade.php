
@extends('front.layouts.app')

@section('title')
- Video Documentations
@endsection

@section('content')
<div id="main-content" ng-controller="TermsAndConditionsPrivacyPolicyController" class="">
    <div class="container col-12" class="pd-0 mb-2">
        <div class="row documentation_holder">
            @include('front.partials.video_sidebar1')
            <div class="col-12">
                <div class="line"></div>
            </div>
            @include('front.partials.video_sidebar2')
        </div>
    </div>
</div>
@include('front.partials.modals.video_modal')
@stop