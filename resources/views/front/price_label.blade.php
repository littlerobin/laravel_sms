<div ng-init="labelInit()" ng-class="{show: showPriceLabel}" class="price_label @{{labelOpened ? 'opened' : 'closed'}}" ng-controller="AuthenticationController">
	<div class="country_select">
		<button type="button" class="btn dropdown-toggle selected-phonenumber-image" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="txt">{{trans('main.sms.check_out_prices_for')}}</i>
			<img ng-src="{{asset('/laravel_assets/callburn/images/lang-flags')}}/@{{callRoutes[0].code}}.svg" alt="" width="15">
			<span>
				<span class="country">@{{callRoutes[0].name}}</span><span class="prefix">(+@{{callRoutes[0].phonenumber_prefix}})</span>
			</span>
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
	<div class="calc_section">
		<div class="sects">
			<div class="vm">
				<a ng-href="{{ $tab == 'voice-message' ? '#' : '/voice-message'}}">
					<div class="text">
						<h1>{{trans('main.vm.voice_messages')}}</h1>
						<h2>{{trans('main.sms.pay_for_delivered')}}</h2>
					</div>
				</a>
				<div class="price">
					<h2 class="text-white" ng-show="prices * selectedMessageLength / 60 < 1.00">€ @{{(prices * selectedMessageLength / 60 | number:4).toString().replace('.', ',') }}*</h2>
					<h2 class="text-white" ng-show="prices * selectedMessageLength / 60 >= 1.00">€ @{{(prices * selectedMessageLength / 60 | number:2).toString().replace('.', ',') }}*</h2>
					<h2 ng-show="!prices" class="text-white no_sms_price" ng-click="crispCountrySend(currentCountry)">{{trans('main.sms.percent_less_than_your_actual_tariff')}}</h2>
				</div>
			</div>
			<div class="sms">
				<a ng-href="{{ $tab == 'sms' ? '#' : '/sms'}}">
					<div class="text">
						<h1>SMS</h1>
						<h2>{{trans('main.sms.pay_also_for_undelivered')}}</h2>
					</div>
				</a>
				<div class="price">
					<h2 class="text-white" ng-show="smsPrices && smsPrices < 1.00">€ @{{(smsPrices | number:4).toString().replace('.', ',') }}*</h2>
					<h2 class="text-white" ng-show="smsPrices && smsPrices >= 1.00">€ @{{(smsPrices | number:2).toString().replace('.', ',') }}*</h2>
					<h2 ng-show="!smsPrices" class="text-white no_sms_price" ng-click="crispCountrySend(currentCountry)">{{trans('main.sms.percent_less_than_your_actual_tariff')}}</h2>
					<h2 class="sms_box_text">*{{trans('main.sms.sub_box_text')}}</h2>
				</div>
			</div>
		</div>
		<div class="closer" ng-class="{'rotate': labelOpened}" ng-click="toggleLabel()">
			<i class="fas fa-caret-right"></i>
		</div>
	</div>
</div>