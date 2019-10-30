@extends('app-home')
	{!! HTML::style( asset('assets/callburn/jquery.datetimepicker.css') ) !!}
@section('scripts')
	{!! HTML::script( asset('assets/callburn/js/records/recorder.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/records/Fr.voice.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/records/jquery.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/records/record.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/records/record1.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/records/record2.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/dashboard/dashboard.main.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/dashboard/inline.main.js') ) !!}
	{!! HTML::script( asset('assets/callburn/jquery.js') ) !!}
	{!! HTML::script( asset('assets/callburn/jquery.datetimepicker.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/dropzone.min.js') ) !!}

@endsection

@section('content')
<div class="background">
	@include('header')
	@include('message')
	@if($action == 'add')
	<a href="{{action('UserController@getCreateCompaign')}}">
		<div class="top_icon">
			<img src="{{asset('assets/callburn/images/7.png')}}" class="icon" />
			<h5>{{trans('common.create_campaign')}}</h5>
		</div>
	</a>
	@else
	<a href="{{action('UserController@getEditCampaign',[$id,'false'])}}">
		<div class="top_icon">
			<img src="{{asset('assets/callburn/images/7.png')}}" class="icon" />
			<h5>{{trans('common.edit_campaign')}}</h5>
		</div>
	</a>
	@endif
	{!! Form::hidden('language', $language,['id' => 'language']) !!}
	@if($action == 'add')
	{!! Form::open(array('action' => 'UserController@postSendData','files' =>'true')) !!}
	@else
	{!! Form::model($campaign, ['action' => ['UserController@putEditCampaign',  $id], 'method' => 'PUT', 'role' => 'form', 'files' => 'true' ]) !!}
	@endif
	<div class="create">
		<div class="create_center">
			<div class="left_menu_container">
				<a href="#" class="left_menu_content">
					<span class="menu_text"  id="first">1. {{trans('common.sub_menu1')}}</span>
				</a>	
				<a href="#" class="left_menu_content">
					<span class="menu_text" id="second">2. {{trans('common.sub_menu2')}}</span>
				</a>
				<a href="#" class="left_menu_content">
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
			<!-- Where-send !-->
			@if(!isset($status))
			<div class="right_menu_container" id='where_send'>
			@else
			<div class="right_menu_container" id='where_send' style='display:none'>
			@endif
				<div class="title_icon">	
					<h1 class="title">{{trans('common.sub_menu1')}}</h1>
				</div>
				<div class="right_menu_send">
					<div class="distantions_upload_drag">
						<div class="select_number_file">
							<p class="send_title">{{trans('common.title_add_phonenumbers')}}</p>						
							{!! Form::select('name', $code, null, ['id' => 'phone_code', 'class' => 'send_select']) !!}
							<input type="text" id='phone_val' placeholder="{{trans('common.solder_phone')}}" class="phone_number" />
							<span class="send_btn" id='add_number'>{{trans('common.save')}}</span>
							{!! Form::text('number_list', null, ['id' => 'number_list', 'style' => 'display:none']) !!}
						</div>
						<div class="select_number_file">
							<p class="send_title">{{trans('common.title_upload_csv')}}</p>
							<input type="text" id='csv_value' placeholder="{{trans('common.solder_csv')}}" class="upload_sending_file" />
							{!! Form::file('file_csv', array('id' => 'csv_upload_file','style' => 'display:none')) !!}
							<label class="send_btn" type='button' for='csv_upload_file' id='upload_csv'>{{trans('common.upload')}}</label>
						</div>
						<div class="select_number_file">
							<p class="send_title">{{trans('common.title_drop')}}</p>
						
					        <div class="dropzone drag_drop_file" id="dropzoneFileUpload" style='overflow:hidden'>
					        </div>
 							{!! Form::text('drop_file', null, ['id' => 'drop_file', 'style' => 'display:none']) !!}
    
							<span class="send_btn">{{trans('common.save')}}</span>
						</div>
					</div>
					<div class="added_contacts">
						<div class="contact_left_right_container">
							<div class="added_contacts_left">
								<p class="send_title">{{trans('common.add_contact_content')}}</p>
								<div class="handwritten_contacts">
									<div class="chack_container">
										<div class="chack">
											<img src="{{asset('assets/callburn/images/4658.png')}}" class="chack_img" />
										</div>
										<img src="{{asset('assets/callburn/images/36845.png')}}" class="pen_icon" />
										<p class="handwritten_contacts_title">{{trans('common.title_phone_add')}}</p>
									</div>		
								</div>
								<div class="archive">
									<div class="chack2_container">
										<div class="chack2">
											<img src="{{asset('assets/callburn/images/4658.png')}}" class="chack_img2" />
										</div>
										<img src="{{asset('assets/callburn/images/841816.png')}}" class="pen_icon" />
										<p class="handwritten_contacts_title">{{trans('common.title_phone_files')}}</p>
									</div>
									<div class="archive_open">
										<ul>
											@foreach($phonenumberFiles as $files)
											<li class='numbers_of_file' data-id="{{$files['_id']}}">
												<span>{{$files['original_name']}}</span>
												<img src="{{asset('assets/callburn/images/basket.png')}}">
											</li>
											@endforeach
										</ul>
									</div>		
								</div>	
							</div>	
							<div class="added_contacts_right">
								<!-- <div class="contacts_count_container">
									<span class="contacts_count">1560 Contactos</span>
									<span class="contacts_count">5 Erróneos</span>
									<span class="contacts_count">3 Duplicados</span>
								</div> -->
								<div class="contacts_list">
									<ul id='number_container'>
									</ul>
								</div>
							</div>
							<div id="phone_no" style='display:none'>
								<div class="contacts_list">
									<ul id='phone_no_container'>
									</ul>
								</div>
							</div>
						</div>	
						<div class="bottom_next_btn">
							<span class="next_btn" id='next_where_send'>{{trans('common.next')}}</span>
						</div>	
					</div>	
				</div>		
			</div>

			<!-- where-send 2 !-->
			<div class="right_menu_container" style='display:none' id='where_send2'>
				<div class="right_menu_container_top">
					<div class="top_icon_div">
						<h2>{{trans('common.title_where_send2')}}</h2>
						<img src="{{asset('assets/callburn/images/123.png')}}">
					</div>
				</div>
				<div class="back" id='back_send2'>
					<span>{{trans('common.back')}}</span>
				</div>
				<div class="next">
					<span>{{trans('common.next')}}</span>
				</div>
				<div class="right_menu_container_center">
					<table>
						<tr>
							<td class="where_send_table_titles"><b>{{trans('common.campaign')}}</b></td>
							<td class="where_send_table_titles"><b>{{trans('common.fecha')}}</b></td>
							<td class="where_send_table_titles"><b>{{trans('common.contacts')}}</b></td>
							<td class="where_send_table_titles"><b>{{trans('common.telephone')}}</b></td>
							<td class="where_send_table_titles"><b>{{trans('common.message')}}</b></td>
						</tr>
						<tbody id='success_campaign'>
							@foreach($campaigns as $compaign)
								<tr class="table_rows">
									<td class="data">{{$compaign['campaign_name']}}</td>
									<td class="data">{{$compaign['created_on']}}</td>
									<td class="data">{{$compaign['total_phonenumbers_loaded']}}</td>
									<td class="data">{{$compaign['caller_id']}}</td>
									<td class="data">
										<audio class='audio_player' id="audio{{$compaign['_id']}}" controls src="//shakisha.synology.me:5099/play-file-data/?key={{Session::get('key')}}&file_id={{$compaign['campaign_voice_file_id']}}" style='display:none'></audio>
										<img data-play="{{$compaign['_id']}}" id="play{{$compaign['_id']}}" src="{{asset('assets/callburn/images/play_file.png')}}" data-file="{{$compaign['campaign_voice_file_id']}}" class="play play_icon1">
										<img data-play="{{$compaign['_id']}}" id="stop{{$compaign['_id']}}" style='display:none' src="{{asset('assets/callburn/images/stop.png')}}" data-file="{{$compaign['campaign_voice_file_id']}}" class="stop play_icon1">
										<span data-id="{{$compaign['campaign_voice_file_id']}}" class="use_file1">{{trans('common.use')}}</span>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					{!! Form::text('voiceId', null, ['id' => 'callerId', 'style' => 'display:none']) !!}
				</div>
				<div class="pagination">
				<div id='next_prev'>
					@if($page != '1')
					<img data-page="{{$page-2}}" src="{{asset('assets/callburn/images/arrow_left.png')}}" class="page_arrow pageination" id="page_arrow_left" />
					@endif
					
					@if($next == 'true')
					<img data-page="{{$page}}" src="{{asset('assets/callburn/images/arrow_right.png')}}" class="page_arrow pageination" id="page_arrow_right" />
					@endif
				</div>
				@if($endpage > 1)
				<div class="page_number_container" id='page_number_container'>
					@if(($page-2)>0)
					<span data-page="$page-3" class="page_number pageination">{{$page-2}}</span>
					@endif
					@if(($page-1)>0)
					<span data-page="{{$page-2}}" class="page_number pageination">{{$page-1}}</span>
					@endif
					<span style='background:#4C4C4C;color:#fff' class="page_number">{{$page}}</span>
					@if(($page+1) < $endpage)
						@if(($page-3)<0)
						<span data-page="{{$page}}" class="page_number pageination">{{$page+1}}</span>
						@endif
					@endif
					@if(($page+2)< $endpage)
						@if(($page-2)<0)
						<span data-page="{{$page+1}}" class="page_number pageination">{{$page+2}}</span>
						@endif
					@endif
					@if(($page)< $endpage)
					<span class="page_number">...</span>
					<span data-page="{{$endpage-1}}" class="page_number pageination">{{$endpage}}</span>
					@endif
				</div>
				@endif
			</div>
			</div>

			<!-- create !-->
			<div class="right_menu_container1" style='display:none' id='create'>
				<div class="back1" id='back_create'>
					<span>{{trans('common.back')}}<span>
				</div>	
				<h1 class="title">{{trans('common.title_message')}}</h1>
				<div class="right_menu_content">
					<a href="#" id='show_records'>
						<img src="{{asset('assets/callburn/images/4.png')}}" />
						<h4>{{trans('common.record_menu')}}</h4>
					</a>		
				</div>
				<div class="right_menu_content">
					<a href="#" id='show_upload_file'>
						<img src="{{asset('assets/callburn/images/5.png')}}" />
						<h4>{{trans('common.upload_menu')}}</h4>
					</a>		
				</div>
				<div class="right_menu_content">
					<a href="#" id='show_messages'>
						<img src="{{asset('assets/callburn/images/6.png')}}" />
						<h4>{{trans('common.message_menu')}}</h4>
					</a>		
				</div> 	
			</div>

			<!-- records !-->
			<div class="right_menu_container2" id='records' style='display:none'>
				<span class="final_btn">{{trans('common.next_page')}}</span>
				<div class="title_icon">	
					<h1 class="title">{{trans('common.title_records')}}</h1>
					<div class="sub_icon">
						<img src="{{asset('assets/callburn/images/10.png')}}" class="icon" />
						<h5 class="icon_title">{{trans('common.title_record_message')}}</h5>
					</div>
				</div>
				<div class="back2" id='back_records'>
					<span>{{trans('common.back')}}</span>
				</div>	
				<div class="right_menu_center">
					<div class="record_message">
						<div class="record_message_container">
							<img src="{{asset('assets/callburn/images/play.png')}}" id="play" class="record_imgs" >
							<!-- <img src="{{asset('assets/callburn/images/stop1.png')}}" id="play" class="record_imgs" style="display: none" > -->
							<img src="{{asset('assets/callburn/images/record.png')}}" id="record" class="record_imgs">
							<img src="{{asset('assets/callburn/images/minuts20.png')}}" id='timer_records' class="record_imgs">
							<div id='timer1' class="timer1"></div>
							<div id='totalTime1' class="total_time1"></div>
							<!-- <span class="record_count">{{trans('common.record')}} 4</span> -->
						</div>	
						<span class="call_btn" id="base64">{{trans('common.save_record')}}</span>
					</div>
					<audio controls src="" style='display:none' id="audio"></audio>
					{!! Form::hidden('token', csrf_token(),['class' => 'tok']) !!}
					{!! Form::hidden('record_create', null,['id' => 'record_create']) !!}
					<!-- <div class="record">
						<table>
							<tr>
								<td>
									<img src="{{asset('assets/callburn/images/play_file.png')}}" class="play_icon">
								</td>
								<td>Record1.mp3</td>
								<td>
									<span class="use_file">Use</span>
								</td>
								<td>
									<img src="{{asset('assets/callburn/images/basket.png')}}" class="delete_icon">
								</td>
							</tr>
						</table>
					</div> -->
				</div>	
			</div>

			<!-- upload-file !-->
			<div class="right_menu_container1" style='display:none' id='upload_file'>
				<span class="final_btn">{{trans('common.next_page')}}</span>
				<div class="title_icon">	
					<h1 class="title">{{trans('common.title_uplad_records')}}</h1>
					<div class="sub_icon">
						<img src="{{asset('assets/callburn/images/11.png')}}" class="icon" />
						<h5 class="icon_title">{{trans('common.title_upload_records_1')}}</h5>
					</div>	
				</div>
				<div class="back2" id='back_upload_file'>
					<span>{{trans('common.back')}}</span>
				</div>	
				<div class="right_menu_center">
					<div class="upload_drag">
						<div class="upload_file_form">
							<input type="text" id='value_upload_file_sound' placeholder="{{trans('common.solder_records')}}" class="uploding_file" />
							{!! Form::file('file_sound', array('id' => 'upload_file_sound','style' => 'display:none')) !!}
							<label class="uploading_btn" type='button' for='upload_file_sound'>{{trans('common.upload')}}</label>
						</div>
						<div class="dropzone drag_file" id="dropzoneFileUpload1" style='overflow:hidden'>
					    </div>
					    {!! Form::text('drop_records', null, ['id' => 'drop_file1', 'style' => 'display:none']) !!}
					</div>
					<div class="play_minuts">
						<audio controls style='display:none' src="" id="audio_succes"></audio>
						<div id='play_content' style="display: none">
							<img src="{{asset('assets/callburn/images/play.png')}}" id='play_succes' class="play_icon_upload" />
							<img src="{{asset('assets/callburn/images/stop1.png')}}" id='stop_succes' class="play_icon_upload" style='display:none' />
							<img src="{{asset('assets/callburn/images/minuts1000.png')}}" class="minuts_icon" />
							<div id='timer2' class="timer2"></div>
							<div id='totalTime2' class="total_time2"></div>
						</div>
					</div>		
				</div>		
			</div>

			<!--  messages  !-->

			<div class="right_menu_container2" style='display:none' id='messages'>
				<span class="final_btn">{{trans('common.next_page')}}</span>
				<span class="final_btn1" id='add_audio_text'>{{trans('common.save_records')}}</span>
				<div class="title_icon">	
					<h1 class="title">{{trans('common.title_message')}}</h1>
					<div class="sub_icon">
						<img src="{{asset('assets/callburn/images/12.png')}}" class="icon" />
						<h5 class="icon_title">{{trans('common.title_message_1')}}</h5>
					</div>	
				</div>
				<div class="back2" id='back_messages'>
					<span>{{trans('common.back')}}</span>
				</div>	
				<div class="right_menu_center">
					<div class="write_message">
						{!! Form::textarea('message', null, array('class' => 'text_area','id'=>'audio_text', 'placeholder' => trans('common.solder_message'))) !!}
					</div>
					<div class="write_message">
						{!! Form::select('lang', $lang, null, ['id' => 'lang', 'class' => 'write_message_country']) !!}
					</div>
					{!! Form::hidden('audio_id', null,['id' => 'audio_id']) !!}
					<div class="write_message_play_icon">
						<div id='hide_player' style="display: none">
							<audio id="ajax_audio_player"  controls src="//shakisha.synology.me:5099/play-file-data/" style="display: none" ></audio>
							<img src="{{asset('assets/callburn/images/play.png')}}" id='play_ajax_audio' class="play_icon_write" />
							<img style='display:none' src="{{asset('assets/callburn/images/stop1.png')}}" id='stop_ajax_audio' class="play_icon_write" />
							<img src="{{asset('assets/callburn/images/minuts0.png')}}" class="minuts_icon_write">
							<div id='timer' class="timer"></div>
							<div id='totalTime' class="total_time"></div>
						</div>
					</div>		
				</div>		
			</div>

			<!-- send !-->
			@if(!isset($status))
			<div class="right_menu_container" style='display:none' id='send'>
			@else
			<div class="right_menu_container" style='display:block' id='send'>
			@endif
				<div class="title_icon">	
					<h1 class="title">{{trans('common.title_send')}}</h1>
				</div>
				@if(!isset($status))
				<div class="back" id='back_send'>
				@else
				<div class="back" data-id="messages" id='back_send'>
				@endif
					<span>{{trans('common.back')}}</span>
				</div>	
				<div class="right_menu_center">
					<div class="send_message_left">
						<div class="send_message_content">
							<h3 class="send_message_content_title">{{trans('common.name_campaign')}}</h3>
							{!! Form::text('campaignName', null, array('class' => 'send_message_content_span', 'placeholder' => trans('common.solder_campaign'))) !!}
						</div>
						<div class="send_message_content">
							<h3 class="send_message_content_title">{{trans('common.caller_id')}}</h3>
							{!! Form::select('callerId',$numbers , null, ['class' => 'send_message_content_span']) !!}
							<p><a href="#">{{trans('common.elegir')}}</a> {{trans('common.link_text')}}</p>
						</div>
						<div class="contact_location">
							<div class="amount_and_location">
								<span class="first_span">{{trans('common.contacts')}}</span>
								<span class="second_span">
									<img src="{{asset('assets/callburn/images/eye.png')}}">
									<span class="viewed_contacts" id='show_contacts'>
									 	{{trans('common.contacts_list')}}
									</span>
								</span>
							</div>
							<div class="amount_and_location">
								<span class="first_span">{{trans('common.zona')}}</span>
								{!! Form::select('timezone',$timezone , null, ['class' => 'second_span']) !!}
							</div>
						</div>
						<div class="price">
							<span>{{trans('common.text_send_1')}}</span>
							<span>{{trans('common.text_send_2')}}</span>
							<span>* {{trans('common.text_send_3')}}</span>
						</div>	
					</div> 
					<div class="send_message_rihgt">
						<div class="send_message_rihgt_title_container">
							<span class="send_message_rihgt_title"><b>{{trans('common.title_voice')}}</b></span>
							<span class="send_message_rihgt_title">* {{trans('common.title_voice_1')}}</span>
						</div>
						<div class="send_message_rihgt_icons_container">
							<div class="send_message_rihgt_icons_content">
								<img src="{{asset('assets/callburn/images/23.png')}}" class="send_message_rihgt_icons">
								<h3 class="send_message_rihgt_icons_titles">{{trans('common.replay')}}</h3>
							</div>
							<div class="send_message_rihgt_icons_content">
								<img src="{{asset('assets/callburn/images/24.png')}}" id='show_call' class="send_message_rihgt_icons">
								<h3 class="send_message_rihgt_icons_titles">{{trans('common.transfer')}}</h3>
							</div>
							<div class="send_message_rihgt_icons_content">
								<img src="{{asset('assets/callburn/images/21_2.png')}}" id='show_callback' class="send_message_rihgt_icons">
								<h3 class="send_message_rihgt_icons_titles">{{trans('common.colback')}}</h3>
							</div>
							<div class="send_message_rihgt_icons_content">
								<img src="{{asset('assets/callburn/images/22.png')}}" id='show_noCall' class="send_message_rihgt_icons">
								<h3 class="send_message_rihgt_icons_titles">{{trans('common.dontcolback')}}</h3>
							</div>
						</div>
						<div class="press_container">
							<div class="press_content">
								<span>{{trans('common.pulse')}}</span>
								{!! Form::select('replayDigit', [''=>'', '0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'], null, ['class' => 'press_container_select']) !!}
							</div>
							<div class="press_content">
								<span>{{trans('common.pulse')}}</span>
								{!! Form::select('transferDigit', [''=>'', '0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'], null, ['class' => 'press_container_select']) !!}
							</div>
							<div class="press_content">
								<span>{{trans('common.pulse')}}</span>
								{!! Form::select('callbackDigit', [''=>'', '0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'], null, ['class' => 'press_container_select']) !!}
							</div>
							<div class="press_content">
								<span>{{trans('common.pulse')}}</span>
								{!! Form::select('doNotCallDigit', [''=>'', '0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'], null, ['class' => 'press_container_select']) !!}
							</div>
						</div>
						<div class="send_test_btn">
							<span>{{trans('common.enviar')}}</span>
						</div>
						{!! Form::hidden('campaign_status', null,['id' => 'campaign_status']) !!}
						<div class="send_message_rihgt_btns_container">
							<input class="send_message_rihgt_btns submit" id='save_status' type='submit' value="{{trans('common.guardar')}}">
							<input class="send_message_rihgt_btns submit" type='submit' value="{{trans('common.show_new')}}">
							<div class="send_message_rihgt_btns" id='show_later'>
								<span>{{trans('common.later')}}</span>
							</div>
							<div class="send_message_rihgt_btns" id='show_timeslots'>
								<span>{{trans('common.schedule')}}</span>
							</div>
						</div>
					</div>
				</div>	
			</div>

		</div>
	</div>
	<div class="icons_container">
		<a href="{{action('UserController@getEditAccount')}}">
			<img src="{{asset('assets/callburn/images/8.png')}}" class="icon" />
		</a>	
		<a href="{{action('UserController@getCompanas','false')}}">
			<img src="{{asset('assets/callburn/images/9.png')}}" class="icon" />
		</a>			
	</div>

	<!-- modal-contact !-->
	<div class="window" id='myModal_contact_content' style='display:none'>
		<div class="pop_up" id='myModal_contact' style='display:none'>
			<div class="pop_up_center">
				<a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_contact"></a>
				<div class="pop_up_text">
					<h1>{{trans('common.contacts_modal')}}</h1>
				</div>
				<div class="contact_popup">
					<div class="added_contacts_right_popup_container">
						<div class="added_contacts_right_popup">
							<div class="contacts_list_popup">
								<ul id='modal_contact_numbers'>
									@if($action == 'edit')
										@foreach($numbers_list as $number)
											<li><span>{{$number['phone_no']}}</span></li>
										@endforeach
									@endif
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="total_contacts_popup">
				</div>							
				<div class="accepting_btns">
					<span class="ok exit_contact">{{trans('common.ok')}}</span>
				</div>
			</div>
		</div>
	</div>

	<!-- modal call !-->

	<div class="window" id='myModal_call_content' style='display:none'>
		<div class="pop_up" id='myModal_call' style='display:none'>
			<div class="pop_up_center">
				<a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_call"></a>
				<div class="pop_up_text">
					<h1>{{trans('common.title_modal_transfer')}}</h1>
				</div>
				<div class="icon_div_popup">
					<div class="icon_and_title">
						<img src="{{asset('assets/callburn/images/24.png')}}" class="popup_icons" />
						<span>{{trans('common.transfer')}}</span>
					</div>
					<div class="icon_div_popup_right">
						<span>{{trans('common.pulse')}}</span>
						<select class="popup_select">
							<option selected>1</option>
							<option>2</option>
						</select>
					</div>		
				</div>
				<div class="bottom_div">

					<h4 class="popup_h4">{{trans('common.telephone_list_transfer')}}</h4>
					<a id='add_input' href='#'>
						<img src="{{asset('assets/callburn/images/iconaddgrey.png')}}" class="exit exit_callback">
					</a>
					<div id='input_container'>
						{!! Form::text('drop_option', null, ['class' => 'drop_option popup_second_select']) !!}
					</div>
				</div>
				{!! Form::hidden('transferOptions', null,['id' => 'transferOptions']) !!}	
				<div class="accepting_btns">
					<span id='add_option_val' class="ok exit_call">{{trans('common.ok')}}</span>
				</div>
			</div>
		</div>
	</div>

	<!-- modal-callback !-->
	<div class="window" id='myModal_callback_content' style='display:none'>
		<div class="pop_up" id='myModal_callback' style='display:none'>
			<div class="pop_up_center">
				<a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_callback"></a>
				<div class="pop_up_text">
					<h1>{{trans('common.title_modal_colback')}}</h1>
				</div>
				<div class="icon_div_popup">
					<div class="icon_and_title">
						<img src="{{asset('assets/callburn/images/22.png')}}" class="popup_icons" />
						<span>{{trans('common.colback')}}</span>
					</div>
					<div class="icon_div_popup_right">
						<span>{{trans('common.pulse')}}</span>
						<select class="popup_select">
							<option selected>1</option>
							<option>2</option>
						</select>
					</div>		
				</div>
				<h4 class="message_title">{{trans('common.colback_menu')}}</h4>
				<span id='grabar1'>
					<div class="popup_message">
						<div class="message_icons">
							<img class='grabar_menu1' src="{{asset('assets/callburn/images/10.png')}}">
							<span>{{trans('common.grabar')}}</span>
						</div>
						<div class="message_icons">
							<img class='subir_menu1' src="{{asset('assets/callburn/images/20.png')}}">
							<span>{{trans('common.subir')}}</span>
						</div>
						<div class="message_icons">
							<img class='escribir_menu1' src="{{asset('assets/callburn/images/21.png')}}">
							<span>{{trans('common.escribir')}}</span>
						</div>
					</div>
					<div class="bottom_div">
						<div class="record_message_popup">
							<img src="{{asset('assets/callburn/images/play.png')}}" id="play2" class="record_imgs" style="display: none">
							<img src="{{asset('assets/callburn/images/record.png')}}" id="record2" class="record_imgs">
							<img src="{{asset('assets/callburn/images/minuts20.png')}}" id='totalModal1' class="record_imgs" style="display: none" >
							<div id='timerModal1' class="timer_modal1"></div>
							<div id='totalTimeModal1' class="total_time_modal1"></div>
						</div>
						<audio controls src="" style='display:none' id="audio2"></audio>
						{!! Form::hidden('record_callback', null,['id' => 'record_callback']) !!}
					<!-- <div style="margin:10px;">
							<a class="button" id="record">Record</a>
							<a class="button disabled one" id="stop">Reset</a>
							<a class="button disabled one" id="play">Play</a>
							<a class="button disabled one" id="download">Download</a>
				      		<a class="button disabled one" id="base64">Base64 URL</a>
				      		<a class="button disabled one" id="mp3">MP3 URL</a>
						</div>
						<input class="button" type="checkbox" id="live"/>
						<label for="live">Live Output</label> -->
					</div>	
				</span>
				<span id='subir1' style='display:none'>
					<div class="popup_message">
						<div class="message_icons">
							<img class='grabar_menu1' src="{{asset('assets/callburn/images/19.png')}}">
							<span>{{trans('common.grabar')}}</span>
						</div>
						<div class="message_icons">
							<img class='subir_menu1' src="{{asset('assets/callburn/images/11.png')}}">
							<span>{{trans('common.subir')}}</span>
						</div>
						<div class="message_icons">
							<img class='escribir_menu1' src="{{asset('assets/callburn/images/21.png')}}">
							<span>{{trans('common.escribir')}}</span>
						</div>
					</div>
					<div class="bottom_div">
						<div class="popup_upload">
							<div class="upload_file_form">
								<input type="text" id='value_callbacke_sound' placeholder="Voice recording file..." class="uploding_file" />
								{!! Form::file('callback_sound', array('id' => 'callback_sound','style' => 'display:none')) !!}
								<label for='callback_sound' class="uploading_btn">{{trans('common.upload')}}</label>
							</div>
							<div class="dropzone drag_file" id="dropzoneFileUpload3" style='overflow:hidden'>
					        </div>
					        {!! Form::text('callbackFile', null, ['id' => 'drop_file3', 'style' => 'display:none']) !!}
						</div>
					</div>	
				</span>
				<span id='escribir1' style='display:none'>
					<div class="popup_message">
						<div class="message_icons">
							<img class='grabar_menu1' src="{{asset('assets/callburn/images/19.png')}}">
							<span>{{trans('common.grabar')}}</span>
						</div>
						<div class="message_icons">
							<img class='subir_menu1' src="{{asset('assets/callburn/images/20.png')}}">
							<span>{{trans('common.subir')}}</span>
						</div>
						<div class="message_icons">
							<img class='escribir_menu1' src="{{asset('assets/callburn/images/12.png')}}">
							<span>{{trans('common.escribir')}}</span>
						</div>
					</div>
					<div class="bottom_div">
						<div class="write_message_popup">
							<div class="write_message">
								{!! Form::textarea('callbackMessage', null, array('id' => 'callback_audion_text','class' => 'text_area', 'placeholder' => trans('common.message'))) !!}
							</div>
							<div class="write_message">
								{!! Form::select('callbackLang', $lang, null, ['id' => 'callbackLang', 'class' => 'write_message_country']) !!}
								<span id = 'add_audio_callback'  class="ok1">{{trans('common.save')}}</span>
							</div>
							{!! Form::hidden('callback_audio_id', null,['id' => 'callback_audio_id']) !!}
						</div>	
					</div>	
				</span>
				<div class="accepting_btns">
					<span id="base642" class="ok exit_callback">{{trans('common.ok')}}</span>
				</div>
			</div>
		</div>
	</div>	

	<!-- modal-noCall !-->
	<div class="window" id='myModal_noCall_content' style='display:none'>
		<div class="pop_up" id='myModal_noCall' style='display:none'>
			<div class="pop_up_center">
				<a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_noCall"></a>
				<div class="pop_up_text">
					<h1>{{trans('common.title_modal_dontcolback')}}</h1>
				</div>
				<div class="icon_div_popup">
					<div class="icon_and_title">
						<img src="{{asset('assets/callburn/images/22.png')}}" class="popup_icons" />
						<span>{{trans('common.dontcolback')}}</span>
					</div>
					<div class="icon_div_popup_right">
						<span>{{trans('common.pulse')}}</span>
						<select class="popup_select">
							<option selected>1</option>
							<option>2</option>
						</select>
					</div>		
				</div>
				<h4 class="message_title">{{trans('common.dontcolback_menu')}}</h4>
				<span id='grabar'>
					<div class="popup_message">
						<div class="message_icons">
							<img class='grabar_menu' src="{{asset('assets/callburn/images/10.png')}}">
							<span>{{trans('common.grabar')}}</span>
						</div>
						<div class="message_icons">
							<img class='subir_menu' src="{{asset('assets/callburn/images/20.png')}}">
							<span>{{trans('common.subir')}}</span>
						</div>
						<div class="message_icons">
							<img class='escribir_menu' src="{{asset('assets/callburn/images/21.png')}}">
							<span>{{trans('common.escribir')}}</span>
						</div>
					</div>
					<div class="bottom_div">
						<div class="record_message_popup">
							<img src="{{asset('assets/callburn/images/play.png')}}" id="play1" class="record_imgs" style='display:none'>
							<img src="{{asset('assets/callburn/images/record.png')}}" id="record1" class="record_imgs">
							<img src="{{asset('assets/callburn/images/minuts20.png')}}" id='totalModal2' class="record_imgs" style='display:none'>
							<div id='timerModal2' class="timer_modal2"></div>
							<div id='totalTimeModal2' class="total_time_modal2"></div>
						</div>
						<audio controls src="" style='display:none' id="audio1"></audio>
						{!! Form::hidden('record_donot', null,['id' => 'record_donot']) !!}
					<!-- <div style="margin:10px;">
							<a class="button" id="record">Record</a>
							<a class="button disabled one" id="stop">Reset</a>
							<a class="button disabled one" id="play">Play</a>
							<a class="button disabled one" id="download">Download</a>
				      		<a class="button disabled one" id="base64">Base64 URL</a>
				      		<a class="button disabled one" id="mp3">MP3 URL</a>
						</div>
						<input class="button" type="checkbox" id="live"/>
						<label for="live">Live Output</label> -->
					</div>	
				</span>
				<span id='subir' style='display:none'>
					<div class="popup_message">
						<div class="message_icons">
							<img class='grabar_menu' src="{{asset('assets/callburn/images/19.png')}}">
							<span>{{trans('common.grabar')}}</span>
						</div>
						<div class="message_icons">
							<img class='subir_menu' src="{{asset('assets/callburn/images/11.png')}}">
							<span>{{trans('common.subir')}}</span>
						</div>
						<div class="message_icons">
							<img class='escribir_menu' src="{{asset('assets/callburn/images/21.png')}}">
							<span>{{trans('common.escribir')}}</span>
						</div>
					</div>
					<div class="bottom_div">
						<div class="popup_upload">
							<div class="upload_file_form">
								<input type="text" id='value_upload_voicefile_sound' placeholder="Voice recording file..." class="uploding_file" />
								{!! Form::file('donotVoicefile_sound', array('id' => 'upload_voicefile_sound','style' => 'display:none')) !!}
								<label for='upload_voicefile_sound' class="uploading_btn">{{trans('common.upload')}}</label>
							</div>
							<div class="dropzone drag_file" id="dropzoneFileUpload2" style='overflow:hidden'>
					        </div>
					        {!! Form::text('donotFile', null, ['id' => 'drop_file2', 'style' => 'display:none']) !!}
						</div>
					</div>	
				</span>
				<span id='escribir' style='display:none'>
					<div class="popup_message">
						<div class="message_icons">
							<img class='grabar_menu' src="{{asset('assets/callburn/images/19.png')}}">
							<span>{{trans('common.grabar')}}</span>
						</div>
						<div class="message_icons">
							<img class='subir_menu' src="{{asset('assets/callburn/images/20.png')}}">
							<span>{{trans('common.subir')}}</span>
						</div>
						<div class="message_icons">
							<img class='escribir_menu' src="{{asset('assets/callburn/images/12.png')}}">
							<span>{{trans('common.escribir')}}</span>
						</div>
					</div>
					<div class="bottom_div">
						<div class="write_message_popup">
							<div class="write_message">
								{!! Form::textarea('voiceMessage', null, array('id' => 'donot_audion_text','class' => 'text_area', 'placeholder' => trans('common.message'))) !!}
							</div>
							<div class="write_message">
								{!! Form::select('voiceLang', $lang, null, ['id' => 'voiceLang', 'class' => 'write_message_country']) !!}
								<span id = 'add_audio_donot'  class="ok1">Save</span>
							</div>
							{!! Form::hidden('donot_audio_id', null,['id' => 'donot_audio_id']) !!}
						</div>	
					</div>	
				</span>
				<div class="accepting_btns">
					<span id="base641" class="ok exit_noCall">{{trans('common.ok')}}</span>
				</div>
			</div>
		</div>
	</div>

	<!--later !-->
	<div class="window" id='myModal_later_content' style='display:none'>
		<div class="pop_up" id='myModal_later' style='display:none'>
			<div class="pop_up_center">
				<a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_later"></a>
				<div class="pop_up_text">
					<h1>{{trans('common.title_modal_later')}}</h1>
				</div>
				<div class="popup_date">
					<div class="popup_date_content1">
						<span>{{trans('common.from_dey')}}</span>
						    {!! Form::text('postponeFrom', null, ['id' => 'some_class_1', 'class' => 'some_class']) !!}
					</div>
					<div class="popup_date_content2">
						<span>{{trans('common.to_dey')}}</span>
							{!! Form::text('postponeTo', null, ['id' => 'some_class_2', 'class' => 'some_class']) !!}
					</div>
				</div>
				<div class="accepting_btns">
					<input type='submit' value="{{trans('common.ok')}}" class="ok exit_later submit">
				</div>
			</div>
		</div>
	</div>

	<!-- timeslots !-->
	<div class="window" id='myModal_timeslots_content' style='display:none'>
		<div class="pop_up" id='myModal_timeslots' style='display:none'>
			<div class="pop_up_center">
				<a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_timeslots"></a>
				<div class="pop_up_text">
					<h1>{{trans('common.title_modal_schedule')}}</h1>
				</div>
				<div class="time_slots_container">
					<div class="time_slots">
						<div class="timeslots_select">
							<span>{{trans('common.from_schedule')}}:</span>
							<select id='in_hour'>
								@for($i=0;$i<=23;$i++)
								<?php
								if($i<=9)
								{
									$i = '0'.$i;
								}
								?>
								<option>{{$i}}</option>
								@endfor
							</select>
							<select id='in_minute'>
								@for($j=0;$j<=59;$j++)
								<?php
								if($j<=9)
								{
									$j = '0'.$j;
								}
								?>
								<option>{{$j}}</option>
								@endfor
							</select>
						</div>
						<div class="timeslots_select">
							<span>{{trans('common.to_schedule')}}:</span>
							<select id='to_hour'>
								@for($i=0;$i<=23;$i++)
								<?php
								if($i<=9)
								{
									$i = '0'.$i;
								}
								?>
								<option>{{$i}}</option>
								@endfor
							</select>
							<select id='to_minute'>
								@for($j=0;$j<=59;$j++)
								<?php
								if($j<=9)
								{
									$j = '0'.$j;
								}
								?>
								<option>{{$j}}</option>
								@endfor
							</select>
						</div>
					</div>
					<div class="days_container">
						<span>{{trans('common.day')}}:</span>
						<div class="days">
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='sun' data-day='0' name="0" />
								</div>
								<h4>{{trans('common.sun')}}</h4>
							</div>
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='mon' data-day='1' name="0" />
								</div>
								<h4>{{trans('common.mon')}}</h4>
							</div>
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='tue' data-day='2' name="0" />
								</div>
								<h4>{{trans('common.tue')}}</h4>
							</div>
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='wed' data-day='3' name="0" />
								</div>
								<h4>{{trans('common.wed')}}</h4>
							</div>
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='thu' data-day='4' name="0" />
								</div>
								<h4>{{trans('common.thu')}}</h4>
							</div>
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='fri' data-day='5' name="0" />
								</div>
								<h4>{{trans('common.fri')}}</h4>
							</div>
							<div class="day_chack">
								<div class="slot_day">
									<img src="{{asset('assets/callburn/images/2015.png')}}" class="slot_chack_img" data-array='sat' data-day='6' name="0" />
								</div>
								<h4>{{trans('common.sat')}}</h4>
							</div>
						</div>
					</div>
					<div class="slot_btns_container">
						<span class="sloat_btn" id='add_schedule'>{{trans('common.add_time')}}</span>
						<span class="sloat_btn" id='remove_schedule'>{{trans('common.cleare_time')}}</span>
					</div>
				</div>
				<div class="chacked_list_container">
					<div class="week_days_list">
						<span class="week_days">{{trans('common.sunday')}}</span>
						<span class="week_days">{{trans('common.monday')}}</span>
						<span class="week_days">{{trans('common.tuesday')}}</span>
						<span class="week_days">{{trans('common.wednesday')}}</span>
						<span class="week_days">{{trans('common.thursday')}}</span>
						<span class="week_days">{{trans('common.friday')}}</span>
						<span class="week_days">{{trans('common.saturday')}}</span>
					</div>
					<span id='schedule_container'>
						@if($action == 'edit')
							@for($i=0;$i<$lenght;$i++)
								<div class='chacked_list_container_right'>
									@if(isset($schedule->sun[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->sun[$i]}}</span><img data-array='{{$schedule->sun[$i]}}' data-day = 'sun' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
									@if(isset($schedule->mon[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->mon[$i]}}</span><img data-array='{{$schedule->mon[$i]}}' data-day = 'mon' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
									@if(isset($schedule->tue[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->tue[$i]}}</span><img data-array='{{$schedule->tue[$i]}}' data-day = 'tue' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
									@if(isset($schedule->wed[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->wed[$i]}}</span><img data-array='{{$schedule->wed[$i]}}' data-day = 'wed' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
									@if(isset($schedule->thu[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->thu[$i]}}</span><img data-array='{{$schedule->thu[$i]}}' data-day = 'thu' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
									@if(isset($schedule->fri[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->fri[$i]}}</span><img data-array='{{$schedule->fri[$i]}}' data-day = 'fri' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
									@if(isset($schedule->sat[$i]))
										<div class='selected_hours_container'><span><span class="time">{{$schedule->sat[$i]}}</span><img data-array='{{$schedule->sat[$i]}}' data-day = 'sat' class='remove_one_schedule' src='{{asset('assets/callburn/images/basket.png')}}'></span></div>
									@else
										<div class='selected_hours_container'></div>
									@endif
								</div>
							@endfor
						@endif
					</span>
					<div class="slot_accepting_btns">
						<span class="sloat_btn exit_timeslots" id='save_schedule_data'>{{trans('common.save')}}</span>
						<span class="sloat_btn exit_timeslots">{{trans('common.cancel')}}</span>
					</div>
					{!! Form::text('schedule', null, ['id' => 'schedule', 'style' => 'display:none']) !!}
				</div>
			</div>
		</div>
	</div>
	{!! Form::close() !!}
</div>	

@endsection