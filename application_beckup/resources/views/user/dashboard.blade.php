@extends('app-home')

@section('content')
<div class="background">
	@include('header')
	@include('message')
	{!! Form::hidden('lenguage', $language,['id' => 'lenguage']) !!}
	<div class="opportunities">
		<div class="opportunities_center">
			<h2>
				{{trans('common.welcome_callburn')}}		
			</h2>
			<h2>
				{{trans('common.text_callburn')}}		
			</h2>
			<h2>
				{{trans('common.text_callburn_page')}}		
			</h2>
			<div class="opportunities_container">
				<div class="opportunities_content">
					<a href="{{action('UserController@getCreateCompaign')}}">
						<img src="{{asset('assets/callburn/images/1.png')}}" class="opportunities_img" />
						<img src="{{asset('assets/callburn/images/1_hover.png')}}" class="opportunities_hover_img" />
						<h4>{{trans('common.create_campaign')}}</h4>
					</a>		
				</div>
				<div class="opportunities_content">
					<a href="{{action('UserController@getEditAccount')}}">
						<img src="{{asset('assets/callburn/images/2.png')}}" class="opportunities_img" />
						<img src="{{asset('assets/callburn/images/2_hover.png')}}" class="opportunities_hover_img" />
						<h4>{{trans('common.edit_account')}}</h4>
					</a>		
				</div>
				<div class="opportunities_content">
					<a href="{{action('UserController@getCompanas','false')}}">
						<img src="{{asset('assets/callburn/images/3.png')}}" class="opportunities_img" />
						<img src="{{asset('assets/callburn/images/3_hover.png')}}" class="opportunities_hover_img" />
						<h4>{{trans('common.companas')}}</h4>
					</a>		
				</div>
			</div>
		</div>
	</div>
</div>
@endsection