@extends('app-home')

@section('scripts')
	{!! HTML::script( asset('assets/callburn/js/campaign/campaign.main.js') ) !!}
@endsection

@section('content')
<div class="background">
@include('header')
@include('message')
	<a href="{{action('UserController@getCompanas','false')}}">
		<div class="top_icon">
			<img src="{{asset('assets/callburn/images/9.png')}}" class="icon" />
			<h5>{{trans('common.title_campaigns')}}</h5>
		</div>
	</a>
	{!! Form::hidden('lenguage', $language,['id' => 'lenguage']) !!}
	<div class="create">
		<div class="create_center">
			<h1 class="account_title">{{trans('common.title_campaigns')}}</h1>
			<div class="account_content">
				<table>
					<tr>
						<td class="where_send_table_titles"><b>{{trans('common.campaign')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.fecha')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.contacts')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.estado')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.success')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.live')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.ans')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.dns')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.transfer')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.busy')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.error')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.retries')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.misc')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.acciones')}}</b></td>
					</tr>
					@foreach($campaigns as $campaign)
					<tr>
						<td  class="statistic_content">
							<img src="{{asset('assets/callburn/images/purple.png')}}" class="color_icon" />
							<span class="first_field">{{$campaign['campaign_name']}}</span>
						</td>
						<td class="statistic_content">{{$campaign['created_on']}}</td>
						<td class="statistic_content">
							@if(isset($campaign['total_phonenumbers_loaded']))
								<a href="{{action('UserController@getNumberList',[$campaign['_id'],'false'])}}">{{$campaign['total_phonenumbers_loaded']}}</a>
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							{{$campaign['status']}}
						</td>
						<td class="statistic_content">
							@if(isset($campaign['success']))
								{{$campaign['success']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['live']))
								{{$campaign['live']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['no_ans']))
								{{$campaign['no_ans']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['dnc']))
								{{$campaign['dnc']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['transfer']))
								{{$campaign['transfer']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['busy']))
								{{$campaign['busy']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['error']))
								{{$campaign['error']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['retries']))
								{{$campaign['retries']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if(isset($campaign['misc']))
								{{$campaign['misc']}}
							@else
								{{ 0 }}
							@endif
						</td>
						<td class="statistic_content">
							@if($campaign['status'] == 'start')
							<a href="{{action('UserController@getStatus',[$campaign['_id'],$campaign['status']])}}">
								<img src="{{asset('assets/callburn/images/window.png')}}" class="statistic_icons" />
							</a>
							@elseif($campaign['status'] == 'stop')
							<a href="{{action('UserController@getStatus',[$campaign['_id'],$campaign['status']])}}">
								<img src="{{asset('assets/callburn/images/clock.png')}}" class="statistic_icons" />
							</a>
							@elseif($campaign['status'] == 'saved')
							<a href="{{action('UserController@getStatus',[$campaign['_id'],$campaign['status']])}}">
								<img src="{{asset('assets/callburn/images/green_pen.png')}}" class="statistic_icons" />
							</a>
							@else
							<a href="#">
								<img src="{{asset('assets/callburn/images/blue_arrow.png')}}" class="statistic_icons" />
							</a>
							@endif
							<a href="#" data-href="{{action('UserController@getExportCampaign',$campaign['_id'])}}"  class='export_container'><img src="{{asset('assets/callburn/images/30.png')}}" class="statistic_icons" /></a>
							<a href="{{action('UserController@getEditCampaign',[$campaign['_id'],'false'])}}"><img src="{{asset('assets/callburn/images/28.png')}}" class="statistic_icons" /></a>
							<a href="#" class='remove_container' data-href="{{action('UserController@getRemoveCampaign',$campaign['_id'])}}"><img src="{{asset('assets/callburn/images/29.png')}}" class="statistic_icons" /></a>
						</td>
					</tr>
					@endforeach
				</table>
			</div>
			<div class="pagination">
				@if($page != '1')
				<a href="{{action('UserController@getCompanas',($page-2))}}"><img src="{{asset('assets/callburn/images/arrow_left.png')}}" class="page_arrow" id="page_arrow_left" /></a>
				@endif
				
				@if($next == 'true')
				<a href="{{action('UserController@getCompanas',($page))}}"><img src="{{asset('assets/callburn/images/arrow_right.png')}}" class="page_arrow" id="page_arrow_right" /></a>
				@endif
				@if($endpage > 1)
				<div class="page_number_container">
					@if(($page-2)>0)
					<a href="{{action('UserController@getCompanas',($page-3))}}"><span class="page_number">{{$page-2}}</span></a>
					@endif
					@if(($page-1)>0)
					<a href="{{action('UserController@getCompanas',($page-2))}}"><span class="page_number">{{$page-1}}</span></a>
					@endif
					<span style='background:#4C4C4C;color:#fff' class="page_number">{{$page}}</span></a>
					@if(($page+1)< $endpage)
						@if(($page-3)<0)
						<a href="{{action('UserController@getCompanas',($page))}}"><span class="page_number">{{$page+1}}</span></a>
						@endif
					@endif
					@if(($page+2)< $endpage)
						@if(($page-2)<0)
						<a href="{{action('UserController@getCompanas',($page+1))}}"><span class="page_number">{{$page+2}}</span></a>
						@endif
					@endif
					@if(($page)< $endpage)
					<span class="page_number">...</span>
					<a href="{{action('UserController@getCompanas',($endpage-1))}}"><span class="page_number">{{$endpage}}</span></a>
					@endif
				</div>
				@endif
			</div>
		</div>
	</div>
	<div class="icons_container">
		<a href="{{action('UserController@getCreateCompaign')}}">
			<img src="{{asset('assets/callburn/images/7.png')}}" class="icon" />
		</a>	
		<a href="{{action('UserController@getEditAccount')}}">
			<img src="{{asset('assets/callburn/images/8.png')}}" class="icon" />
		</a>			
	</div>
	<div class="window" id='myModal_campaign_content' style='display:none'>
        <div class="pop_up" id='myModal_campaign' style='display:none'>
            <div class="pop_up_center">
                <a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_campaign"></a>
                <div class="pop_up_text">
                    <h1>{{trans('common.title_remove_compaign')}}</h1>
                    <span class="pop_up2_span">{{trans('common.text_remove_compaign')}}</span>
	                <div class="account_popup">
	                    
	                    <a href="#" id='remove_campaign' class="account_popup_btns1" >{{trans('common.yes')}}</a>
	                    <a href="#" class="account_popup_btns1">{{trans('common.no')}}</a>
	                </div>
	            </div>
        	</div>
    	</div>
	</div>
	<div class="window" id='myModal_export_content' style='display:none'>
        <div class="pop_up" id='myModal_export' style='display:none'>
            <div class="pop_up_center">
                <a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_export"></a>
                <div class="pop_up_text">
                    <h1>{{trans('common.title_export_compaign')}}</h1>
                    <span class="pop_up2_span">{{trans('common.text_export_compaign')}}</span>
	                <div class="account_popup">
	                    
	                    <a href="#" data-type='csv' id='export_csv' class="account_popup_btns1">{{trans('common.csv')}}</a>
	                    <a href="#" data-type='pdf' id='export_pdf' class="account_popup_btns1">{{trans('common.pdf')}}</a>
	                </div>
	            </div>
        	</div>
    	</div>
	</div>
</div>	
@endsection