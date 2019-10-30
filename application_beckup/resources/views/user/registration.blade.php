@extends('app-user')

@section('scripts')
	{!! HTML::script( asset('assets/callburn/js/login/login_registrar.main.js') ) !!}
@endsection

@section('content')
	<div class="wrapper">
		<div class="wrapper_center">
			<div class="operator_container">
				<div class="menu">
					<a href="#" class="menu_content">
						<span class="menu_text" id="first">1. {{trans('common.sub_menu1')}}</span>
					</a>	
					<a href="#" class="menu_content">
						<span class="menu_text" id="second">2. {{trans('common.sub_menu2')}}</span>
					</a>
					<a href="#" class="menu_content">
						<span class="menu_text" id="third">3. {{trans('common.sub_menu3')}}</span> 
					</a>	
				</div>
				<div class="left_menu_container_mobile">
					<a href="#" class="left_menu_content_mobile">
						<span class="menu_text_mobile"  id="first">1.</span>
					</a>	
					<a href="#" class="left_menu_content_mobile">
						<span class="menu_text_mobile" id="second">2.</span>
					</a>
					<a href="#" class="left_menu_content_mobile">
						<span class="menu_text_mobile" id="third">3.</span> 
					</a>	
				</div>
				<div class="operator" id='operator_code'>
					<div class="operator_img">
						<img src="{{asset('assets/callburn/images/operator4.png')}}" />
					</div>
					<div class="operator_text">
						<img src="{{asset('assets/callburn/images/text_area.png')}}"  class="text_area" />
						<span class="text_left">
							<img src="{{asset('assets/callburn/images/phone.png')}}"  class="computer_img_right" />
						 	<span>You will receive a CALL with your code</span>
						</span>	
					</div>	
				</div>
				<div class="operator" id='operator_verification' style='display:none'>
					<div class="operator_img">
						<img src="{{asset('assets/callburn/images/operator3.png')}}" />
					</div>
					<div class="operator_text">
						<img src="{{asset('assets/callburn/images/text_area.png')}}"  class="text_area" />
						<span class="text_right">
							<span>We have sant MESSAGE to your mail</span>
							<img src="{{asset('assets/callburn/images/message.png')}}"  class="computer_img_left" />
						</span>	
					</div>	
				</div>
				<div class="operator" id='operator_password' style='display:none'>
					<div class="operator_img">
						<img src="{{asset('assets/callburn/images/operator2.png')}}" />
					</div>
					<div class="operator_text">
						<img src="{{asset('assets/callburn/images/text_area.png')}}"  class="text_area" />
						<span class="text_right">
							<span>{{trans('common.sub_menu_registr')}}</span>
							<img src="{{asset('assets/callburn/images/computer.png')}}"  class="computer_img_left" />
							<!-- Write a <font color="#FF5B36">PASSWORD</font> on the text box and you could start to use Callburn -->
						</span>	
					</div>	
				</div>
			</div>
			<div class="login_form" id='code_form'>
				<h1>
					{{trans('common.registr_title1')}}
				</h1>
				<image src="{{asset('assets/callburn/images/123456.png')}}" class="fire_logo" />
				<p>
					{{trans('common.registr_text1')}}
				</p>
				<div class="form">
					<div class='errors' id='errors_number' style='display:none'>
				       {{trans('coomon.errors_registr')}}   
				    </div>
				    @if (Session::has('message'))
						<div class='errors'>
					        *{{ Session::get('message') }}   
					    </div>
					@endif
					{!! Form::hidden('lenguage', $language,['id' => 'lenguage']) !!}
	{!! Form::open(array('action' => 'UserController@postRegistration', 'id' => 'admin_login_form')) !!}
					
					{!! Form::hidden('phonenumber', $number) !!}
					{!! Form::text('number1', null, ['class' => 'code', 'maxlength' => '1']) !!}
					{!! Form::text('number2', null, ['class' => 'code', 'maxlength' => '1']) !!}
					{!! Form::text('number3', null, ['class' => 'code', 'maxlength' => '1']) !!}
					{!! Form::text('number4', null, ['class' => 'code', 'maxlength' => '1']) !!}
					<span class="call_btn" id='next_code'> {{trans('common.sumbit')}} </span>
				</div>
				<span class='form_span'>{{trans('common.llamada')}}, <a href="{{action('UserController@getLogin')}}">{{trans('common.nuevo')}}</a></span>
			</div>
			<div class="login_form" id='verification_form' style='display:none'>
				<h1>
					{{trans('common.registr_title2')}}
				</h1>
				<image src="{{asset('assets/callburn/images/123456.png')}}" class="fire_logo" />
				<p>
					{{trans('common.registr_text2')}}
				</p>
				<div class="form">
					<div class='errors' id='errors_verification' style='display:none'>
				        {{trans('coomon.errors_registr')}} 
				    </div>
					{!! Form::text('email', null, ['placeholder' => trans('common.email_val'), 'id' => 'email_registr', 'class' => 'email']) !!}
					<span class="call_btn" id='next_verification'> {{trans('common.reenviar')}} </span>
				</div>
			</div>
			<div class="login_form" id='password_form' style='display:none'>
				<h1>
					{{trans('common.registr_title3')}}
				</h1>
				<image src="{{asset('assets/callburn/images/123456.png')}}" class="fire_logo" />
				<p>
					{{trans('common.registr_text2')}}
				</p>
				<div class="form">
					{!! Form::password('password', ['placeholder' => trans('common.choose_password'), 'class' => 'password']) !!}
					{!! Form::password('passwordConfirmation', ['placeholder' => trans('common.repeat_password'), 'class' => 'password']) !!}
					<div class="checkbox_container">
						{!! Form::checkbox('remember_me', 1, false, ['class' => 'checkbox']) !!}
						{{trans('common.checkbox_val')}}
					</div>	
					<button class="login_btn"> {{trans('common.login')}} </button>
	{!! Form::close() !!}

				</div>	
			</div>
			
		</div>	
	</div>

@endsection