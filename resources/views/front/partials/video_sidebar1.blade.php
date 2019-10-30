<div class="col-12">
	<div class="row d-flex flex-row justify-content-center align-items-center">
		<div class="section_title col-12 col-sm-4 col-md-2">
			<img src="{{asset('/laravel_assets/images/front/img/img2.svg')}}" alt="">
			<h3>{{trans('main.vm.voice_messages')}}</h3>
		</div>
		<div class="col-12 col-sm-8 col-md-7 slick_videos_holder" id="ez">
			<slick infinite=true slides-to-show=1 settings="youtubeVideosConfig" ng-if="VMVideos.length" class="slider-for">
				<div ng-repeat="video in VMVideos">
					<iframe ng-src="@{{getVideoFromPlaylist(video)}}" frameborder="0"></iframe>
				</div>
			</slick>
		</div>
		<div class="col-12 col-sm-3 col-md-2 offset-md-0 async_preview">
			<div class="scroll_div">
				<slick infinite=true slides-to-show=1 settings="youtubeVideosNavConfig" ng-if="VMVideos.length" class="slider-nav">
					<div ng-repeat="item in VMVideos" class="" ng-click="slickFunc($event)">
						<div class="descr_holder">
							<img class="img-fluid" ng-src="@{{getThumbnail(item.videoId)}}">
						</div>
						<h5 class="youtube_title">@{{item.title}}</h5>
					</div>
				</slick>
			</div>
		</div>
	</div>
</div>	
