@extends('app-user')

@section('content')
	
	<div class="wrapper">
		<div class="wrapper_center">
			<div class="faq_container">
				<div class="accordion">
					<h3>{{trans('common.title_faq1')}}</h3>
					<div>
						<iframe width="400" height="300" src="https://www.youtube.com/embed/NJsa6-y4sDs" frameborder="0" allowfullscreen></iframe>
					 	<p>
						    {{trans('common.text_faq1')}}
					    </p>
					</div>
					<h3>{{trans('common.title_faq2')}}</h3>
					<div>
					    <p>
						    {{trans('common.text_faq2')}}
					    </p>
					  </div>
					  <h3>{{trans('common.title_faq3')}}</h3>
					  <div>
					    <p>
						    {{trans('common.text_faq3')}}
					    </p>
					  </div>
					  <h3>{{trans('common.title_faq4')}}</h3>
					  <div>
					    <p>
						    {{trans('common.text_faq4')}}
					    </p>
					  </div>
					  <h3>{{trans('common.title_faq5')}}</h3>
					  <div>
					    <p>
						    {{trans('common.text_faq5')}}
					    </p>
					  </div>
					  <h3>{{trans('common.title_faq6')}}</h3>
					  <div>
					    <p>
						    {{trans('common.text_faq6')}}
					    </p>
					  </div>
					  <h3>{{trans('common.title_faq7')}}</h3>
					  <div>
					    <p>
						    {{trans('common.text_faq7')}}
					    </p>
					  </div>
					  <h3>{{trans('common.title_faq8')}}</h3>
					  <div>
					    <p>
						    {{trans('common.text_faq8')}}
					    </p>
					  </div>
				</div>
			</div>	
		</div>
	</div>

@endsection