@extends('app-home')

@section('scripts')

@endsection

@section('content')
<div class="background">
@include('header')
@include('message')
{!! Form::hidden('lenguage', $language,['id' => 'lenguage']) !!}
<div class="opportunities">
		<div class="opportunities_center">
			<h2>
				{{trans('common.title_success')}}		
			</h2>
			<h2>
				{{trans('common.text_success')}}		
			</h2>
			<div class="interval_div_container">
				<h5>{{trans('common.text_success_page')}}</h5>
				<div class="interval_div">
					<span>{{trans('common.date_add')}}</span>
					<span>{{trans('common.count_number')}}</span>
				</div>
				<div class="interval_div">
					<span>{{trans('common.date_add1')}}</span>
					<span>{{trans('common.count_number1')}}</span>
				</div>
			</div>
			<div class="opportunities_container_success">
				<div class="opportunities_content_success">
					<a href="{{action('UserController@getCreateCompaign')}}">
						<img src="{{asset('assets/callburn/images/1.png')}}" class="opportunities_success_img" />
						<h4>{{trans('common.create_campaign')}}</h4>
					</a>		
				</div>
				<div class="opportunities_content_success">
					<a href="{{action('UserController@getEditAccount')}}">
						<img src="{{asset('assets/callburn/images/2.png')}}" class="opportunities_success_img" />
						<h4>{{trans('common.edit_account')}}</h4>
					</a>		
				</div>
				<div class="opportunities_content_success">
					<a href="{{action('UserController@getCompanas','false')}}">
						<img src="{{asset('assets/callburn/images/3.png')}}" class="opportunities_success_img" />
						<h4>{{trans('common.companas')}}</h4>
					</a>		
				</div>
			</div>
		</div>
	</div>
</div>
@endsection