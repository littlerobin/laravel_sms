@extends('app-user')

@section('content')

<div class="wrapper">
	<div class="privacy">
		<div class="privacy_content">
			<h1>{{trans('common.title_privacy')}}</h1>
			<div class="privacy_container">
				<p>
					{{trans('common.text_privacy')}}
				</p>
				<h3>{{trans('common.title_privacy_submenu1')}}</h3>
				<p>
					{{trans('common.text_privacy_submenu1')}}
				</p>
				<h3>{{trans('common.title_privacy_submenu2')}}</h3>
				<ul>
					<li>{{trans('common.text_privacy_submenu_li1')}}</li>
					<li>{{trans('common.text_privacy_submenu_li2')}}</li>
					<li>{{trans('common.text_privacy_submenu_li3')}}</li>
					<li>{{trans('common.text_privacy_submenu_li4')}}</li>
					<li>{{trans('common.text_privacy_submenu_li5')}}</li>
					<li>{{trans('common.text_privacy_submenu_li6')}}</li>
					<li>{{trans('common.text_privacy_submenu_li7')}}</li>
					<li>{{trans('common.text_privacy_submenu_li8')}}</li>
				</ul>	
			</div>	
		</div>		
	</div>	
</div>

@endsection