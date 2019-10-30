
@extends('app-user')

@section('content')

<div class="wrapper">
	<div class="wrapper_center">
		<div class="contact">
			<iframe src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d2965.0824050173574!2d-93.63905729999999!3d41.998507000000004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sWebFilings%2C+University+Boulevard%2C+Ames%2C+IA!5e0!3m2!1sen!2sus!4v1390839289319" width="100%" height="100%" frameborder="0" style="border:0"></iframe>
		</div>
		<div class="contact">
			<div class="contact_center">
				<h1>{{trans('common.contactanos')}}</h1>
				<div class="contact_content" id="chat">
					<img src="{{asset('assets/callburn/images/16.png')}}" />
					<h4>{{trans('common.chat')}}</h4>
				</div>
				<div class="contact_content" id="phone">
					<img src="{{asset('assets/callburn/images/17.png')}}" />
					<h4>{{trans('common.telefono')}}</h4>
				</div>
				<div class="contact_content" id="contact">
					<img src="{{asset('assets/callburn/images/18.png')}}" />
					<h4>{{trans('common.formolarode')}}</h4>
				</div>
			</div>	
		</div>	
	</div>	
</div>
<div id="contact_menu3">
	<h1>{{trans('common.title_formolarode')}}</h1>
	<div class="contact_form">
		<div class="input_container">
			<input type="text" placeholder="{{trans('common.solder_numbro')}}" class="contact_inputs" />
			<input type="text" placeholder="{{trans('common.solder_telefono')}}" class="contact_inputs" />
		</div>
		<div class="input_container">
			<input type="email" placeholder="{{trans('common.solder_correo_email')}}" class="contact_inputs" />	
			<select class="contact_inputs4">
			</select>
		</div>	
		<input type="text" placeholder="{{trans('common.solder_coment')}}" cols="60" class="contact_inputs5 " />
		<div class="checkbox_container">
			<input type="checkbox">{{trans('common.solder_checkbox')}}
		</div>	
		<button class="call_btn"> {{trans('common.contact_enviar')}} </button>
	</div>
	<div class="back">
		<img src="{{asset('assets/callburn/images/back_arrow.png')}}" class="back_arrow" />
		{{trans('common.back')}}
	</div>
</div>
<div id="contact_menu2">
	<h1>{{trans('common.telephone_solder')}}</h1>
	<div class="contact_menu2_container">
		<div class="first_container">
			<img src="{{asset('assets/callburn/images/uk.png')}}" class="contact_uk_flag" />
			<span>{{trans('common.contact_telefonio')}}<span>
		</div>
		<div class="second_container">
			<img src="{{asset('assets/callburn/images/phone_icon.png')}}" class="phone_icon" />
			<span>10 123 456 789<span>
		</div>
		<button class="call_btn1">{{trans('common.button_contact')}}</button>
	</div>
	<div class="back">
		<img src="{{asset('assets/callburn/images/back_arrow.png')}}" class="back_arrow" />
		{{trans('common.back')}}
	</div>
</div>

@endsection