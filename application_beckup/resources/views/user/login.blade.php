@extends('app-user')

@section('scripts')
	{!! HTML::script( asset('assets/callburn/js/login/login_registrar.main.js') ) !!}
@endsection

@section('content')
	{!! Form::hidden('lenguage', $language,['id' => 'lenguage']) !!}
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
				<div class="operator" id='operator_registr'>
					<div class="operator_img">
						<img src="{!!asset('assets/callburn/images/operator1.png')!!}" />
					</div>
					<div class="operator_text">
						<img src="{!!asset('assets/callburn/images/text_area.png') !!}" class="text_area" />
						<span class="text_right">
							<span>{{trans('common.sub_menu')}}</span>
							<img src="{!!asset('assets/callburn/images/computer.png')!!}"  class="computer_img_left" />
							<!-- Escribe tu numero de <font color="#FF5B36">TELEFONO</font> para ragistrarte  -->
						</span>	
					</div>	
				</div>
				<div class="operator" id='operator_login' style='display:none'>
					<div class="operator_img">
						<img src="{!!asset('assets/callburn/images/operator5.png')!!}" />
					</div>
					<div class="operator_text">
						<img src="{!!asset('assets/callburn/images/text_area.png') !!}"  class="text_area" />
						<span class="text_left">
						 	<span>Write your PHONE number on the text box to login</span>
						 	<img src="{!!asset('assets/callburn/images/phone1.png') !!}"  class="computer_img_right" />
						</span>	
					</div>	
				</div>	
			</div>
			<div class="login_form" style='display:none' id='registration_form'>
				<h1>
					{{trans('common.registr_title')}}
				</h1>
				<p>
					{{trans('common.registr_text')}}
				</p>
				<div class="form">
					@if(Session::has('message'))
						<div class='errors'>
					        *{{ Session::get('message') }}   
					    </div>
					@endif
					{!! Form::open(array('action' => 'UserController@postNumber', 'id' => 'admin_login_form')) !!}
						{!! Form::select('cod', $code, null, ['class' => 'form_select']) !!}
						{!! Form::text('number', null, ['placeholder' => trans('common.telephone_val'), 'class' => 'number']) !!}
						<button class="call_btn"> {{trans('common.call_now')}} </button>
					{!! Form::close() !!}
				</div>
				<span class='form_span'>{{trans('common.cuenta')}}, <a href="#" id='login'>{{trans('common.entrar')}}</a></span>
			</div>
			<div class="login_form" id='login_form'>
				<h1>
					{{trans('common.login_title')}}
				</h1>
				<image src="{{asset('assets/callburn/images/123456.png')}}" class="fire_logo" />
				<p>
					{{trans('common.login_text')}}
				</p>
				<div class="form">
					@if(Session::has('message'))
						<div class='errors'>
					        *{{ Session::get('message') }}   
					    </div>
					@endif
					@if(Session::has('message1'))
						<div class='errors' style='color:green'>
					        *{{ Session::get('message1') }}   
					    </div>
					@endif
				{!! Form::open(array('action' => 'UserController@postLogin')) !!}
					{!! Form::text('email', null, ['placeholder' => trans('common.email_val'), 'class' => 'number']) !!}
					{!! Form::password('password', ['placeholder' => trans('common.password_val'), 'class' => 'number']) !!}
					<button class="login_btn"> {{trans('common.login')}} </button>
				{!! Form::close() !!}
				</div>
				<span class='form_span'>{{trans('common.tango_cuenta')}} <a href="#" id='registr'>{{trans('common.registration')}}</a></span>
			</div>
		</div>	
	</div>

@endsection