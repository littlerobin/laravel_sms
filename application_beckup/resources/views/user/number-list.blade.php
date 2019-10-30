@extends('app-home')

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
			<h1 class="account_title">{{trans('common.title_number_list')}}</h1>
			<div class="account_content">
				<table>
					<tr>
						<td class="where_send_table_titles"><b>{{trans('common.uniq_id')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.phonenumber')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.status')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.creting_time')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.dialed_time')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.duration')}}</b></td>
						<td class="where_send_table_titles"><b>{{trans('common.retries')}}</b></td>
					</tr>
					@foreach($numbers as $number)
					<tr class="table_rows">
						<td class="data">
							@if(isset($number['uniq_id']))
								{{$number['uniq_id']}}
							@else
								{{''}}
							@endif
						</td>
						<td class="data">
							@if(isset($number['phone_no']))
								{{$number['phone_no']}}
							@else
								{{null}}
							@endif
						</td>
						<td class="data">
							@if(isset($number['call_status']))
								@if($number['call_status'] == '1')
								{{trans('common.status1')}}
								@elseif($number['call_status'] == '2')
								{{trans('common.status2')}}
								@elseif($number['call_status'] == '3')
								{{trans('common.status3')}}
								@elseif($number['call_status'] == '4')
								{{trans('common.status4')}}
								@elseif($number['call_status'] == '5')
								{{trans('common.status5')}}
								@elseif($number['call_status'] == '6')
								{{trans('common.status6')}}
								@elseif($number['call_status'] == '7')
								{{trans('common.status7')}}
								@elseif($number['call_status'] == '8')
								{{trans('common.status8')}}
								@elseif($number['call_status'] == '9')
								{{trans('common.status9')}}
								@elseif($number['call_status'] == '10')
								{{trans('common.status10')}}
								@elseif($number['call_status'] == '11')
								{{trans('common.status11')}}
								@elseif($number['call_status'] == '12')
								{{trans('common.status12')}}
								@endif
							@else
								{{trans('common.status0')}}
							@endif
						</td>
						<td class="data">
							@if(isset($number['created_at']))
								{{$number['created_at']}}
							@else
								{{null}}
							@endif
						</td>
						<td class="data">
							@if(isset($number['dialled_datetime']))
								{{$number['dialled_datetime']}}
							@else
								{{null}}
							@endif
						</td>
						<td class="data">
							@if(isset($number['duration']))
								{{$number['duration']}}
							@else
								{{null}}
							@endif
						</td>
						<td class="data">
							@if(isset($number['retries']))
								{{$number['retries']}}
							@else
								{{0}}
							@endif
						</td>
					</tr>
					@endforeach
				</table>
			</div>
			<div class="pagination">
				<div class="pagination">
				@if($page != '1')
				<a href="{{action('UserController@getNumberList',[$id,($page-2)])}}"><img src="{{asset('assets/callburn/images/arrow_left.png')}}" class="page_arrow" id="page_arrow_left" /></a>
				@endif
				
				@if($next == 'true')
				<a href="{{action('UserController@getNumberList',[$id,($page)])}}"><img src="{{asset('assets/callburn/images/arrow_right.png')}}" class="page_arrow" id="page_arrow_right" /></a>
				@endif
				@if($endpage > 1)
				<div class="page_number_container">
					@if(($page-2)>0)
					<a href="{{action('UserController@getNumberList',[$id,($page-3)])}}"><span class="page_number">{{$page-2}}</span></a>
					@endif
					@if(($page-1)>0)
					<a href="{{action('UserController@getNumberList',[$id,($page-2)])}}"><span class="page_number">{{$page-1}}</span></a>
					@endif
					<span style='background:#4C4C4C;color:#fff' class="page_number">{{$page}}</span></a>
					@if(($page+1)< $endpage)
						@if(($page-3)<0)
						<a href="{{action('UserController@getNumberList',[$id,($page)])}}"><span class="page_number">{{$page+1}}</span></a>
						@endif
					@endif
					@if(($page+2)< $endpage)
						@if(($page-2)<0)
						<a href="{{action('UserController@getNumberList',[$id,($page+1)])}}"><span class="page_number">{{$page+2}}</span></a>
						@endif
					@endif
					@if(($page)< $endpage)
					<span class="page_number">...</span>
					<a href="{{action('UserController@getNumberList',[$id,($endpage-1)])}}"><span class="page_number">{{$endpage}}</span></a>
					@endif
				</div>
				@endif
			</div>
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
</div>	



@endsection	