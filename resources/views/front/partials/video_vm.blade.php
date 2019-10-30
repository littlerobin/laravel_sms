<section class="video_vm">
	<div class="container-fluid">
		<div class=""><!-- col-9 d-flex flex-row justify-content-center align-items-center p-0 -->
			<div class="row">
				<div class="logo_part text-left col-12 col-md-3 pl-0 d-flex flex-column justify-content-center">
					<!-- <div class="select_service">
						<div id="notificationsDropdownButton" class="service_selector pt-3 pb-3 pl-2 pr-2 pl-sm-3 pr-sm-3 pl-lg-4 pr-lg-4 d-flex flex-row align-items-center justify-content-between pointer">
							<p class="mb-0 hidden-sm-down">{{trans('main.crud.select_service')}}</p>
							<img class="hidden-md-up" src="{{asset('laravel_assets/images/front/img/img2.svg')}}" width="60" alt="">
							<p class="mb-0 hidden-md-up">
								{{trans('main.vm.voice_messages')}}
							</p>
							<div class="d-flex flex-column justify-content-center align-items-center">
								<span class="triangle"></span>
								<span class="triangle"></span>
							</div>
						</div>
						<ul class="service_menu" ng-class="{'opened':isOpened}">
							<li ng-click="redirect('voice-message')" class="pl-2 pl-sm-3 pl-lg-4 pr-lg-4">{{trans('main.vm.voice_messages')}}</li>
							<li ng-click="redirect('click-to-call')" class="pl-2 pl-sm-3 pl-lg-4 pr-lg-4">{{trans('main.crud.click_to_call')}}</li>
							<a href="https://callburn.com/developers" target="_blank">
								<li class="pl-2 pl-sm-3 pl-lg-4 pr-lg-4">{{trans('main.crud.callburn_api')}}</li>
							</a>
						</ul>
					</div> -->
					<div class="logo_holder pl-2 pl-lg-4 mt-3 mb-3 hidden-sm-down">
						<img src="{{asset('laravel_assets/images/front/img/img2.svg')}}" width="120" alt="">
						<h3 class="mt-sm-1 mb-sm-2 mt-lg-3 mb-lg-4">{{trans('main.vm.voice_messages')}}</h3>
						<p>{{trans('main.crud.ring_it_instead')}}</p>
					</div>
				</div>
				<div class="slick_videos_holder col-12 col-md-6 p-0">
					<slick slides-to-show=1 class="slick-for" settings="slickVideosConfig" ng-if="VMVideosObj.tutor.length && tutor">
						<div ng-repeat="video in VMVideosObj.tutor track by $index">
							<iframe width="100%" height="335" ng-src="@{{getVideoFromPlaylist(video)}}" id="vm-video-@{{$index}}" frameborder="0" allowfullscreen></iframe>
						</div>
					</slick>
					<slick slides-to-show=1 class="slick-for" settings="slickVideosConfig" ng-if="VMVideosObj.promo.length && promo">
						<div ng-repeat="video in VMVideosObj.promo track by $index">
							<iframe width="100%" height="335" ng-src="@{{getVideoFromPlaylist(video)}}" id="vm-video-@{{$index}}" frameborder="0" allowfullscreen></iframe>
						</div>
					</slick>
				</div>
				<div class="col-12 col-md-3 p-0">
					<div class="tabs">
						<div class="tab_header d-flex flex-row align-items-center justify-content-between" ng-init="tutor=false;promo=true">
							<div ng-click="tutor=true;promo=false" class="tab pl-2" ng-class="{'pressed':tutor}">
								<p>{{trans('main.crud.tutorials')}}</p>
							</div>
							<div class="line"></div>
							<div ng-click="promo=true;tutor=false" class="tab pl-2" ng-class="{'pressed':promo}">
								<p>{{trans('main.crud.promo')}}</p>
							</div>
						</div>
						<div class="tab_content hidden-sm-down">
							<div class="tutor" ng-if="tutor">
								<slick infinite=true slides-to-show=@{{VMVideosObj.tutor.length}} settings="slickVideosNav" ng-if="VMVideosObj.tutor" class="slick-nav">
									<div class="item pl-2 pr-2 d-flex flex-row justify-content-center align-items-center pointer" ng-repeat="item in VMVideosObj.tutor">
										<p>@{{item.title.substring(item.title.indexOf("-") + 1).slice(0, -6)}}</p>
										<div class="line"></div>
										<div class="triangle"></div>
									</div>
								</slick>
							</div>
							<div class="promo" ng-if="promo">
								<slick infinite=true slides-to-show=@{{VMVideosObj.promo.length}} settings="slickVideosNav" ng-if="VMVideosObj.promo" class="slick-nav">
									<div class="item pl-2 pr-2 d-flex flex-row justify-content-center align-items-center pointer" ng-repeat="item in VMVideosObj.promo">
										<p>@{{item.title.substring(item.title.indexOf("-") + 1).slice(0, -6)}}</p>
										<div class="line"></div>
										<div class="triangle"></div>
									</div>
								</slick>
							</div>
						</div>
					</div>
				</div> 
			</div>
		</div>
	</div>
</section>