<div id="main-content" class="vm_main affiliate">
	<header class="pl-2 pr-2">
		<h3>{{ trans('main.dev.already_earned') }}</h3>
		<h1 class="mt-3">$ 354.230,11</h1>
		<div class="coins d-flex flex-row justify-content-between align-items-center mt-4">
			<img src="{{asset('laravel_assets/images/mainStyleImages/l_money.png')}}" class="pl-3" alt="">
			<a href="mailto:info@callburn.com?Subject=I%20want%20to%20partecipate%20to%20your%20affiliation%20program." target="_top">
				<button>
					{{ trans('main.dev.become_partner_btn') }}
				</button>
			</a>
			<img src="{{asset('laravel_assets/images/mainStyleImages/r_money.png')}}" class="pr-3" alt="">
		</div>
	</header>
	<main class="text-center pt-5 pb-5">
		<h1>{{ trans('main.dev.extra_steady') }}</h1>
		<h2 class="w-75 m-auto">{{ trans('main.dev.forever_customer') }}</h2>
		<div class="boxes d-flex flex-column flex-md-row justify-content-center align-items-md-center align-items-stretch mt-5">
			<div class="features box text-left mr-3 mr-md-2 animated fadeInLeft">
				<h1>{{ trans('main.dev.features') }}</h1>
				<ul>
					<li>
						<i class="fas fa-check"></i>
						<p>{{ trans('main.dev.create_trackers') }}</p>
					</li>
					<li>
						<i class="fas fa-check"></i>
						<p>{{ trans('main.dev.monitor_amount') }}</p>
					</li>
					<li>
						<i class="fas fa-check"></i>
						<p>{{ trans('main.dev.choose_payout') }}</p>
					</li>
					<li>
						<i class="fas fa-check"></i>
						<p>{{ trans('main.dev.customer_referral') }}</p>
					</li>
					<li>
						<i class="fas fa-check"></i>
						<p>{{ trans('main.dev.technical_support') }}</p>
					</li>
				</ul>
			</div>
			<div class="revenues box text-left ml-3 ml-md-2 animated fadeInRight">
				<h1>{{ trans('main.dev.revenues') }}</h1>
				<ul class="circles d-flex flex-column flex-md-row justify-content-center align-items-center">
					<li class="circle mb-3 mb-md-0">
						<h1>12%</h1>
						<p>{{ trans('main.vm.voice_messages') }}</p>
						<div class="check"><i class="fas fa-check"></i></div>
					</li>
					<li class="circle ml-3 mr-3 mb-3 mb-md-0">
						<h1>5%</h1>
						<p>Click to call</p>
						<div class="check"><i class="fas fa-check"></i></div>
					</li>
					<li class="circle mb-3 mb-md-0">
						<h1>2%</h1>
						<p>{{ trans('main.vm.sms') }}</p>
						<div class="check"><i class="fas fa-check"></i></div>
					</li>
				</ul>
			</div>
		</div>
		<a href="mailto:info@callburn.com?Subject=I%20want%20to%20partecipate%20to%20your%20affiliation%20program." target="_top">
			<button class="mt-4">{{ trans('main.dev.become_partner_now') }}</button>
		</a>
	</main>
</div>