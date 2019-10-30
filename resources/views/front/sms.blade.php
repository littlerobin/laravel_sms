@extends('front.layouts.app')

@section('title')
- SMS
@endsection

@section('content')
<div class="toTop animated hidden" ng-class="{'fadeIn show':showTop}" ng-click="scrollToTop()" >
    <div class="arrow-up"></div>
</div>
<div id="main-content" class="fixedMenuContentSections">
	{{--@include('front.partials.register_voice_messages')--}}
	{{--@include('front.partials.video_vm')--}}
	@include('front.partials.sms-main')
	@include('front.price_label')

</div>
@stop