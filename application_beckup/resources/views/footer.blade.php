<div id="footer">
	<div class="logo_container">
		<a href="{{action('UsersController@getStartAngular')}}">
			<img src="{!! asset('assets/callburn/images/logo1.png')!!}" class="logo" />
		</a>	
		<div class="footer_select">
			<img src="{!! asset('assets/callburn/images/dropdown_arrow.png')!!}" class="dropdown_arrow">
			<div class="footer_select_div">
				<ul>
					<li>
						@if ($language == 'en')
						<img src="{!! asset('assets/callburn/images/uk.png')!!}" />
						<span>EN</span>
						@endif

						@if ($language == 'ru')
						<img src="{!! asset('assets/callburn/images/russia.png')!!}" />
						<span>RU</span>
						@endif

						@if ($language == 'it')
						<img src="{!! asset('assets/callburn/images/italy.png')!!}" />
						<span>IT</span>
						@endif

						@if ($language == 'es')
						<img src="{!! asset('assets/callburn/images/spain.png')!!}" />
						<span>ES</span>
						@endif
					</li>
				</ul>
			</div>

			<div class="language_open">
				<ul>
					<a href="{{URL::to('/en/'. $currentPathWithoutLocale) }}" class="active">
						<li>
							<img src="{!! asset('assets/callburn/images/uk.png')!!}" />
							<span>EN</span>
						</li>
					</a>
					<a href="{{URL::to('/es/'. $currentPathWithoutLocale) }}" class="active">
						<li>
							<img src="{!! asset('assets/callburn/images/spain.png')!!}" />
							<span>ES</span>
						</li>
					</a>
					<a href="{{URL::to('/ru/'. $currentPathWithoutLocale) }}" class="active">
						<li>
							<img src="{!! asset('assets/callburn/images/russia.png')!!}" />
							<span>RU</span>
						</li>
					</a>
					<a href="{{URL::to('/it/'. $currentPathWithoutLocale) }}" class="active">
						<li>
							<img src="{!! asset('assets/callburn/images/italy.png')!!}" />
							<span>IT</span>
						</li>
					</a>
				</ul>
			</div>
		</div>
	</div>
	<div id="footer_menu">
		<ul id="footer_menu_content">
			<li>
				<a href="#">{{trans('common.funciona')}}</a>
			</li>
			<li>
				<a href="{{action('UserController@getFaq')}}">{{trans('common.faq')}}</a>
			</li>
			<li>
				<a href="{{action('UserController@getPrivacy')}}">{{trans('common.politica')}}</a>
			</li>
			<li>
				<a href="{{action('UserController@getApi')}}">{{trans('common.how_is_works')}}</a>
			</li>
			<li>
				<a href="{{action('UserController@getContact')}}">{{trans('common.contacto')}}</a>
			</li>
		</ul>
	</div>
</div>