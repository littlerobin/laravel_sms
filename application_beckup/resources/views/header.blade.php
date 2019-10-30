<div id="header">
	<div id="header_menu">
		<ul>
			<li class="menu_content">
				{{Session::get('userData')['updated_at']}}
			</li>
			<li class="menu_content">{{Session::get('balance')}} $</li>
			<li class="menu_content">{{Session::get('userData')['email']}}</li>
			<li class="menu_content"><a href="{{action('UserController@getLogout')}}">{{trans('common.log_out')}}</a></li>
		</ul>	
	</div>
</div>
<!-- <div class="background">
</div> -->