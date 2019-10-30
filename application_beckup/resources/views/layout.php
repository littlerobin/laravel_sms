<html lang="en" ng-app='callburnApp' ng-controller='MainController'>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/dist/assets/css/app.css">
		<link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/style.css">
		<link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/font.css">
		<link rel="stylesheet" type="text/css" href="/assets/callburn/style/css/spinner.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-datepicker/dist/angular-datepicker.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-tooltips/dist/angular-tooltips.min.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-rangeslider/angular.rangeSlider.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-tooltips/dist/angular-tooltips.min.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-timezone-selector/dist/angular-timezone-selector.min.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/chosen/chosen.min.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-notify/dist/angular-notify.min.css">
		<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/select2/3.4.5/select2.css">
		<link rel="stylesheet" type="text/css" href="/bower_components/angular-ui-select/dist/select.css">
		<!--<script src="http://192.168.2.50:3000/socket.io/socket.io.js"></script>-->
		<!-- <script src="//static.intercomcdn.com/intercom.v1.js"></script> -->
		<script type="text/javascript">(function(d,n){var s,a,p;s=document.createElement("script");s.type="text/javascript";s.async=true;s.src=(document.location.protocol==="https:"?"https:":"http:")+"//cdn.nudgespot.com"+"/nudgespot.js";a=document.getElementsByTagName("script");p=a[a.length-1];p.parentNode.insertBefore(s,p.nextSibling);window.nudgespot=n;n.init=function(t){function f(n,m){var a=m.split('.');2==a.length&&(n=n[a[0]],m=a[1]);n[m]=function(){n.push([m].concat(Array.prototype.slice.call(arguments,0)))}}n._version=0.1;n._globals=[t];n.people=n.people||[];n.params=n.params||[];m="track register unregister identify set_config people.delete people.create people.update people.create_property people.tag people.remove_Tag".split(" ");for(var i=0;i<m.length;i++)f(n,m[i])}})(document,window.nudgespot||[]);nudgespot.init("ac07d3cdd806ce9934fbd4763d3dc172");</script>
		<!-- Piwik -->
		<!--<script type="text/javascript">
			var _paq = _paq || [];
			_paq.push(['trackPageView']);
			_paq.push(['enableLinkTracking']);
			(function() {
				var u="//10.0.1.41/piwik/";
				_paq.push(['setTrackerUrl', u+'piwik.php']);
				_paq.push(['setSiteId', 1]);
				var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
				g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
			})();
		</script>-->
		<!--<noscript><p><img src="//10.0.1.41/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>-->
		<!-- End Piwik Code -->

	</head>
	<body class="displayNoneBody">
		
		<!-- <center><img ng-show="showLoading" src='/assets/callburn/images/loading-dribble-inner.gif' style="position:absolute;z-index:999;padding-top:15%" /></center> -->
		<div class="cssload-main" ng-show="showLoading">
			<div class="cssload-heart">
				<span class="cssload-heartL"></span>
				<span class="cssload-heartR"></span>
				<span class="cssload-square"></span>
			</div>
			<div class="cssload-shadow"></div>
		</div>
		<div class="cssload-main" ng-show="requestShowLoading">
			<div class="cssload-heart">
				<span class="cssload-heartL"></span>
				<span class="cssload-heartR"></span>
				<span class="cssload-square"></span>
			</div>
			<div class="cssload-shadow"></div>
		</div>

		<!-- Scripts !-->
		<div ng-class="{showRealBlur: requestShowLoading || showBlurEffect}">
			<div class="container_sona">
				<div id="header">					
					<div class="header_top_container">
						<div class="logo_container">
							<img src="/assets/callburn/images/callburn_logo.png" class="logo" ui-sref="dashboard.dashboard" />
						</div>
						<div class="header_email_container">
							<div class="header_date_container">
								<img src="/assets/callburn/images/clock-icon.png" class="header_icons" />
								<span class="header_icons_content_span">{{currentTime}}</span>
							</div>
							<div class="header_email_address_information">{{currentUser.email}}</div>
						</div>
						<div class="profile_container" ng-click="topAccountShowHide()">
							<img ng-src="{{currentUser.grav_image}}" class="profile_icon" />
							<img src="/assets/callburn/images/dropdown-icon.png" class="drop_down_icon" />
							<div class="profile_open" ng-show="topAccountShow">
								<div class="arrow_box1"></div>
								<div class="profile_open_content">
									<span class="account_menu_icons_container" ui-sref="account.settings">
										<img src="assets/callburn/images/account_menu_icons/settings.png" ng-show="false" class="top_account_menu_icons">
										<img src="assets/callburn/images/account_menu_icons/settings_selected.png" ng-show="true" class="top_account_menu_icons">
										<span class="account_menu_span">{{trans('account_settings')}}</span>
									</span>	
								</div>
								<div class="profile_open_content"  ui-sref="account.invoices">
									<span class="account_menu_icons_container">
										<img src="assets/callburn/images/account_menu_icons/invoices_selected.png" ng-show="true" class="top_account_menu_icons" id="account_invoices_selected_icon">
										<span class="account_menu_span">{{trans('account_invoices')}}</span>
									</span>
								</div>
								<div class="logout_top_line"></div>
								<div class="profile_open_content"  ng-click="logOut()">
									<span class="account_menu_icons_container">
										<img src="assets/callburn/images/account_menu_icons/logout-icon.png" ng-show="true" class="top_account_menu_icons" id="logout_icon">
										<span class="account_menu_span">Logout</span>
									</span>
								</div>
							</div>
						</div>
						<div class="header_right_container">
							<div class="header_right_top_container">
								<span class="header_icons_content_container" ui-sref="account.financials">
									<img src="/assets/callburn/images/pig_icon.png" class="header_icons" ui-sref="account.financials"/>
									<span class="header_icons_content_span">{{currentUser.balance}}&#8364</span>
								</span>
								<span class="header_icons_content_container" ng-click="showHideNotifications()">
									<img src="/assets/callburn/images/esclamation_point_icon.png" class="header_icons" />
									<span class="header_icons_content_span">{{notSeenNotificationsCount}}</span>
									<div class="notification_open" ng-show="showNotifications">
										<div class="arrow_box2"></div>
										<div class="notification_dropdown">
											<a ng-repeat="notification in notifications track by $index" ng-click="goToNotification(notification)">
												<div class="notification_open_content">
													<span class="notifications">
														{{notification.text}}
													</span>
													<span class="notifications_date">
														{{notification.created_at}}
													</span>
													<img ng-show="notification.can_remove !== false" src="assets/callburn/images/close-icon.png" class="notification_delete_icon">	
												</div>
											</a>
										</div>	
									</div>
								</span>
							</div>
							<div class="quick_actions" ng-click="quickActionShowHide()">
								<img src='/assets/callburn/images/send-verification-code-icon-copy.png' class="quick_actions_img" />
								Quick Actions
								<div class="quick_action_open" ng-show="quickActionShow">
									<div class="arrow_box"></div>
									<div class="profile_open_content">
										<span class="account_menu_icons_container" ui-sref="campaign.compose">
											<img src="assets/callburn/images/compose_selected.png" ng-show="true" class="top_account_menu_icons">
											<span class="account_menu_span">Compose New</span>
										</span>	
									</div>
									<div class="profile_open_content"  ui-sref="addressbook.contacts">
										<span class="account_menu_icons_container">
											<img src="/assets/callburn/images/contacs-icon.png"  class="top_account_menu_icons">
											<span class="account_menu_span">Add Contact</span>
										</span>
									</div>
									<div class="profile_open_content"  ui-sref="account.settings">
										<span class="account_menu_icons_container">
											<img src="assets/callburn/images/caller-i-d-icon.png" ng-show="true" class="top_account_menu_icons">
											<span class="account_menu_span">Add New Caller ID</span>
										</span>
									</div>
								</div>
							</div>	
						</div>
					</div>
					<div class="header_menu_container">
						<div>
							<div ng-class="{header_menu_content: true, header_menu_content_selected: currentActiveRoute == 'dashboard'}" ui-sref="dashboard.dashboard">
								<img src='/assets/callburn/images/header_menu_icons/dashboard_icon.png' class="menu_icons" ng-hide="currentActiveRoute == 'dashboard'" />
								<img src='/assets/callburn/images/header_menu_icons/dashboard_selected.png' class="menu_icons" ng-show="currentActiveRoute == 'dashboard'" />
								<span class="header_menu_content_span">
									{{trans('sidebar_dashboard')}}
								</span>
							</div>
							<div id="header_menu_content_messages" ng-class="{header_menu_content: true, header_menu_content_selected: currentActiveRoute == 'campaign'}" ui-sref="campaign.overview">
								<span>
									<img src='/assets/callburn/images/header_menu_icons/messages_icon.png' class="menu_icons" id="header_menu_phone_icon" ng-hide="currentActiveRoute == 'campaign'" />
									<img src='/assets/callburn/images/header_menu_icons/messages_selected.png' class="menu_icons" id="header_menu_phone_icon" ng-show="currentActiveRoute == 'campaign'" />
									<span class="header_menu_content_span" id="header_menu_content_span_messages">
										{{trans('sidebar_message')}}
									</span>
								</span>
							</div>
							<div ng-class="{header_menu_content: true, header_menu_content_selected: currentActiveRoute == 'addressbook'}" ui-sref="addressbook.contacts">
								<span>
									<img src='/assets/callburn/images/header_menu_icons/phonebook_icon.png' class="menu_icons" ng-hide="currentActiveRoute == 'addressbook'"/>
									<img src='/assets/callburn/images/header_menu_icons/phonebook_selected.png' ng-show="currentActiveRoute == 'addressbook'" class="menu_icons" />
									<span class="header_menu_content_span">
										{{trans('sidebar_phonebook')}}
									</span>
								</span>
							</div>
							<div ng-class="{header_menu_content: true, header_menu_content_selected: currentActiveRoute == 'api'}" ui-sref="api.settings">
								<img src='/assets/callburn/images/header_menu_icons/api_icon.png' class="menu_icons" ng-hide="currentActiveRoute == 'api'" />
								<img src='/assets/callburn/images/header_menu_icons/api_selected.png' class="menu_icons" ng-show="currentActiveRoute == 'api'" />
								<span class="header_menu_content_span">{{trans('sidebar_api')}}</span>
							</div>
						</div>
					</div>	
				</div>
				<div class="container_right mainbodyfade"  ng-if="!showLoading" ui-view></div>
			</div>

			<div class="campaign_steps mainbodyfade" id="footer" ng-if="!showLoading && footerDataLoaded && footerData.third" >
				<div ng-class="{steps: true, footer_active: isFooter1Active}" class="steps" id="step1" ng-bind-html="make_trusted(footerData.first)"></div>
				<div ng-class="{steps: true, footer_active: isFooter2Active}" class="steps" id="step2" ng-bind-html="make_trusted(footerData.second)"></div>
				<div ng-class="{steps: true, footer_active: isFooter3Active}" class="steps" id="step3" ng-bind-html="make_trusted(footerData.third)"></div>
			</div>
			<div class="campaign_steps mainbodyfade" id="footer" ng-if="!showLoading && footerDataLoaded && !footerData.third" >
				<div ng-class="{steps: true, footer_active: isFooter1Active}" class="steps" id="step1" style="margin-left:22%;" ng-bind-html="make_trusted(footerData.first)"></div>
				<div ng-class="{steps: true, footer_active: isFooter2Active}" class="steps" id="step2" ng-bind-html="make_trusted(footerData.second)"></div>
			</div>

			<footer id="footer">
				<div class="footer_left_right_container">
					<span class="footer_menu">{{trans('footer_menu_what_is_callburn')}}?</span>
					<span class="footer_menu">{{trans('footer_menu_pricing')}}</span>
					<span class="footer_menu">{{trans('footer_menu_docs')}}</span>
					<span class="footer_menu">{{trans('footer_menu_contact_us')}}</span>
					<div class="lang_container">

						<div class="lang_content" ng-click="showHideLanguageBar()">
							<callburn-select ng-model="language"
											 options="languages"
											 show-attr="name"
											 keep-attr='code'
											 image-attr="flags"
											 image-url="/assets/callburn/images/flags/{{currentLanguage}}.png"
											 select-text="{{currentLanguageName | uppercase}}"
											 ng-change="changeLanguage(language)"
											 class="no-border-select">
							</callburn-select>
						</div>
					</div>
				</div> 
				<div class="footer_center_container">
					<div class="footer_icon_container">
						<img src="assets/callburn/images/facebook_icon.png" class="footer_icons">
						<img src="assets/callburn/images/google_icon.png" class="footer_icons">
					</div>
					<span class="footer_sp1"><!-- {{trasn('footer_text1')}} -->Our <b><!-- {{trasn('footer_text2')}} -->customers are happy</b></span>
					<span class="footer_sp3"><!-- {{trasn('footer_text3')}} -->(and you? let us know, chat with us)</span>
					<span class="footer_sp2"><!-- {{trasn('footer_text4')}} -->Contact Support support@callburn.com</span>
					<span class="footer_sp2"><!-- {{trasn('footer_text5')}} -->© Copyright 2015 Callburn</span>
					<span class="footer_sp2"><!-- {{trasn('footer_text6')}} -->All Rights Reserved</span>
				</div>
				<div class="footer_left_right_container">
					<div class="android_ios_img_container">
						<img src="assets/callburn/images/ios.png" class="android_ios_img">
						<img src="assets/callburn/images/android.png" class="android_ios_img">
					</div>
				</div>
			</footer>
		</div>

		<script src="/dist/assets/js/app.min.js" type="text/javascript"></script>
		<script src="/dist/app.js" type="text/javascript"></script>
	</body>
</html>