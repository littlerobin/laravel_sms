@extends('front.layouts.app')

@section('title')
- ClickToCall
@endsection

@section('content')
<div class="toTop" ng-click="scrollToTop()">
    <div class="arrow-up"></div>
</div>
<div id="main-content" class="fixedMenuContentSections">
	@include('front.price_label')
	{{--@include('front.partials.register_click_to_call')--}}
	@include('front.partials.video_ctc')

	@include('front.partials.clicktocall-main')

</div>

@stop