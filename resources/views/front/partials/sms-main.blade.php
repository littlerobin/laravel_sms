<div id="main-content" class="sms_main">
	<header class="pl-2 pr-2 pb-5 text-center" id="header">
		<img src="{{asset('laravel_assets/images/mainStyleImages/sms_icon.svg')}}" class="sms_icon mb-4" alt="">
		<h1>{{trans('main.sms.high_quality_sms')}}</h1>
		<h2 class="mt-4 mb-5">{{trans('main.sms.we_offer_sms')}}</h2>
		<button class="btn btn_outline pointer mt-2 mb-5">{{trans('main.sms.try_it_now_for_free')}}</button>
		<h3>{{trans('main.vm.are_you_dev')}} <a href="/developers">{{trans('main.vm.click_here_now')}}</a></h3>
	</header>
	<div class="how_works pt-5 pb-5" style="background-image: url({{asset('laravel_assets/images/mainStyleImages/how_works_bg.svg')}})">
		<h1 class="m-0">{{trans('main.sms.how_works')}}</h1>
		<h2 class="mt-4 mb-4">{{trans('main.sms.how_works_text')}}</h2>
		<div class="phone_holder">
			<p class="text">{{trans('main.sms.phone_text0')}}</p>
			<div class="hold">
				<h1 class="text">{{trans('main.sms.phone_text1')}}</h1>
				<h2 class="text">{{trans('main.sms.phone_text2')}}</h2>
				<h3 class="text">{{trans('main.sms.phone_text3')}}</h3>
			</div>
			<img src="{{asset('laravel_assets/images/mainStyleImages/how_works_phone.png')}}" alt="">
		</div>
	</div>
	<div class="two_service pt-5 pb-5 text-center">
		<h1 class="title">{{trans('main.sms.two_service_type')}}</h1>
		<h2>{{trans('main.sms.two_service_sms_subtitle')}}</h2>
		<div class="types">
			<div class="box">
				<div class="main">
					<div class="top_holder">
						<h1 class="main_title">{{trans('main.sms.hibrid')}}</h1>
						<div class="img_holder vm_sms_type">
							<h1>{{trans('main.sms.missed_call')}} (3)</h1>
							<h2>{{trans('main.sms.from_your_number')}}</h2>
							<h3>1 {{trans('main.sms.new_sms')}}</h3>
							<img src="{{asset('laravel_assets/images/front/vm_sms.svg')}}" alt="vm and sms">
						</div>
					</div>
					<div class="content">
						<h1>{{trans('main.sms.vm_and_sms_title')}}</h1>
						<h2>{{trans('main.sms.vm_and_sms_text')}}</h2>
					</div>
				</div>
			</div>
			<div class="box">
				<div class="main">
					<div class="top_holder">
						<h1 class="main_title">{{trans('main.sms.standard')}}</h1>
						<div class="img_holder sms">
							<h1>1 {{trans('main.sms.new_sms')}}</h1>
							<img src="/assets/images/compose/sms.svg" alt="sms">
						</div>
					</div>
					<div class="content">
						<h1>{{trans('main.sms.only_sms_title')}}</h1>
						<h2>{{trans('main.sms.only_sms_text')}}</h2>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="prices_holder pt-5 pb-5" id="prices">
		<h1 class="title">{{trans('main.sms.prices_title')}}</h1>
		<h2 class="sub_title">{{trans('main.sms.prices_sub_title')}}</h2>
		<div class="prices" ng-controller="AuthenticationController">
			<div class="handle">
				<div class="send_to">
					<p class="blue">{{trans('main.vm.i_want_to_send_messages_to')}}</p>
					<div class="send_to_inner">
						<button type="button" class="btn dropdown-toggle selected-phonenumber-image" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<div>
								<img ng-src="{{asset('/laravel_assets/callburn/images/lang-flags')}}/@{{callRoutes[0].code}}.svg" alt="" width="20">
								<span>
									<span class="country">@{{callRoutes[0].name}}</span><span class="prefix">(+@{{callRoutes[0].phonenumber_prefix}})</span>
								</span>
							</div>
						</button>
						<ul class="dropdown-menu notificationScrollStyle">
							<li ng-repeat="country in callRoutes" ng-click="choiceCountryPrice(country);">
								<a href="#">
									<img ng-src="{{asset('/laravel_assets/callburn/images/lang-flags')}}/@{{country.code}}.svg" alt="">
									<span>
										<span class="country">@{{country.name}}</span><span class="prefix">(+@{{country.phonenumber_prefix}})</span>
									</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="calc_section">
				<div class="vm">
					<div class="txt_price">
						<div>
							<div class="text">
								<h1>{{trans('main.vm.voice_messages')}}</h1>
							</div>
							<div class="price">
								<h2 ng-show="!price">----</h2>
								<h2 class="text-white" ng-show="price && (price * selectedMessageLength / 60 < 1.00)">€ @{{((prices ? prices : price) * selectedMessageLength / 60 | number:4).toString().replace('.', ',') }}*</h2>
								<h2 class="text-white" ng-show="price && (price * selectedMessageLength / 60 >= 1.00)">€ @{{((prices ? prices : price) * selectedMessageLength / 60 | number:2).toString().replace('.', ',') }}*</h2>
							</div>
						</div>
						<h2 class="small_txt text-white" ng-show="price && (price * selectedMessageLength / 60 * 100 < 100)">@{{((prices ? prices : price) * selectedMessageLength / 60 * 100 | number:2).toString().replace('.', ',')}} {{ trans('main.ctc.cents') }} {{trans('main.vm.single_message')}} {{trans('main.vm.delivered_and_fully_listened')}}</h2>
					</div>
					<div class="message_length">
						<div class="sub_box_inner">
							<p>{{trans('main.vm.message_lenght')}}</p>
						</div>
						<div class="calc_inp">
							<input class="inp-placeholder form_message_length has-type" type="number" ng-model="selectedMessageLength" ng-blur="messageValidator()" step="1" id="example-number-input" autocomplete="false">
							<label for="example-sms-input" class="lab-placeholder">
								{{ trans('main.vm.seconds') }}
							</label>
						</div>
					</div>
				</div>
				<div class="sms">
					<div class="txt_price">
						<div>
							<div class="text">
								<i class="sms_count">@{{num}}</i>
								<h1>SMS</h1>
								<!-- <h2>{{trans('main.sms.pay_also_for_undelivered')}}</h2> -->
							</div>
							<div class="price">
								<h2 class="text-white" ng-show="smsPrices && smsPrices * num < 1.00">€ @{{(smsPrices * num | number:4).toString().replace('.', ',') }}*</h2>
								<h2 class="text-white" ng-show="smsPrices && smsPrices * num >= 1.00">€ @{{(smsPrices * num | number:2).toString().replace('.', ',') }}*</h2>
								<h2 class="text-white" ng-show="!smsPrices">----</h2>
								<!-- <h2 class="text-white" ng-show="price * selectedMessageLength / 60 * 100 < 100">@{{(price * selectedMessageLength / 60 * 100 | number:2).toString().replace('.', ',')}} {{ trans('main.ctc.cents') }}</h2> -->
							</div>
						</div>
						<h2 class="small_txt text-white">{{trans('main.sms.instant_delivery_prices')}}</h2>
					</div>
					<div class="message_length">
						<div class="calc_inp">
							<input ng-change="calcSmsText()" class="inp-placeholder form_message_length has-type" type="number" ng-model="selectedSmsLength" ng-blur="smsValidator()" step="1" id="example-sms-input" autocomplete="false">
							<label for="example-sms-input" class="lab-placeholder">
								{{ trans('main.sms.characters') }}
							</label>
						</div>
					</div>
				</div>
				<div class="sub_box">
					<p>*{{trans('main.crud.vm_price_txt')}}</p>
				</div>
			</div>
		</div>
	</div>
	<div class="more_money pt-5 pb-5">
		<h1 class="title">{{trans('main.sms.more_money_title')}}</h1>
		<h2 class="sub_title">{{trans('main.sms.more_money_sub_title')}}</h2>
		<div class="box_holder">
			<div class="top">
				<h1>{{trans('main.sms.recomend')}}</h1>
			</div>
			<div class="body">
				<ul>
					<li>
						<h1>{{trans('main.sms.list_text1')}}</h1>
						<div>
							<i class="fa fa-check"></i>
						</div>
					</li>
					<li>
						<h1>{{trans('main.sms.list_text2')}}</h1>
						<div>
							<i class="fa fa-check"></i>
						</div>
					</li>
					<li>
						<h1>{{trans('main.sms.list_text3')}}</h1>
						<div>
							<i class="fa fa-check"></i>
						</div>
					</li>
					<li>
						<h1>{{trans('main.sms.list_text4')}}</h1>
						<div>
							<i class="fa fa-check"></i>
						</div>
					</li>
				</ul>
				<a href="/voice-message">
					<button class="btn_outline pointer mt-5">
						{{trans('main.sms.more_money_btn')}}
					</button>
				</a>
			</div>
		</div>
	</div>
	<div class="dev_holder text-center pt-5 pb-5 pl-2 pr-2" style="background-image: url('{{asset('laravel_assets/images/mainStyleImages/api_background.svg')}}');background-size: cover;">
		<h1 class="">{{trans('main.vm.developer_or_custom_app')}}</h1>
		<h2>{{trans('main.vm.easily_integrate')}}</h2>
		<a href="/developers">
			<button class="">
				{{trans('main.vm.dev_btn_text')}}
			</button>
		</a>
	</div>
	<div class="clients_holder pt-5 pb-5 pl-2 pr-2">
		<h1>{{trans('main.vm.client_title_1')}}</h1>
		<h2>{{trans('main.vm.client_title_2')}}</h2>
		<div class="companies d-flex flex-column flex-md-row justify-content-between align-items-center">
			<img class="mb-3 mb-md-0" src="{{asset('laravel_assets/images/mainStyleImages/amazon_logo.png')}}" alt="">
			<img class="mb-3 mb-md-0" src="{{asset('laravel_assets/images/mainStyleImages/facebook_logo.png')}}" alt="">
			<img class="mb-3 mb-md-0" src="{{asset('laravel_assets/images/mainStyleImages/linkedin_logo.png')}}" alt="">
			<img class="mb-3 mb-md-0" src="{{asset('laravel_assets/images/mainStyleImages/google_logo.png')}}" alt="">
			<img class="" src="{{asset('laravel_assets/images/mainStyleImages/netflix_logo.png')}}" alt="">
		</div>
	</div>
</div>