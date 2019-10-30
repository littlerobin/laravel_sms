<div class="col-12">
	<div class="row d-flex flex-row justify-content-center align-items-center">
		<div class="section_title col-12 col-sm-4 col-md-2">
			<img src="{{asset('/laravel_assets/images/front/img/img3.svg')}}" alt="">
			<h3>ClickToCall</h3>
		</div>
		<div class="col-12 col-sm-8 col-md-7 slick_videos_holder">
			<slick infinite=true slides-to-show=1 settings="youtubeVideosConfigCTC" ng-if="CTCVideos.length" class="slider-forCTC">
				<div ng-repeat="video in CTCVideos">
					<iframe ng-src="@{{getVideoFromPlaylist(video)}}" frameborder="0"></iframe>
				</div>
			</slick>
		</div>
		<div class="col-12 col-sm-3 col-md-2 offset-md-0 async_preview">
			<slick infinite=true slides-to-show=1 settings="youtubeVideosNavConfigCTC" ng-if="CTCVideos.length" class="slider-nav slider-navCTC">
				<div ng-repeat="item in CTCVideos" class="" ng-click="slickFunc($event)">
					<div class="descr_holder">
						<img class="img-fluid" ng-src="@{{getThumbnail(item.videoId)}}">
					</div>
					<h5 class="youtube_title">@{{item.title}}</h5>
				</div>
			</slick>
		</div>
	</div>
</div>

