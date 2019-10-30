<div id="main-content" class="vm_main developers">
  <header class="pl-2 pr-2 text-center">
    <h1>{{trans('main.dev.welcome_to')}}</h1>
    <h1>{{trans('main.dev.our_dev_program')}}</h1>
    <h3 class="mt-3">{{trans('main.dev.start_do_things')}}</h3>
    <div class="choose_box d-flex flex-column flex-md-row align-items-stretch  justify-content-center ">
      <div class="box animated fadeInLeft">
        <img src="{{ asset('laravel_assets/images/mainStyleImages/affiliate_logo.png') }}" alt="">
        <h1>{{trans('main.dev.affiliate')}}</h1>
        <h2>{{trans('main.dev.become_partner_and_earn')}}</h2>
        <a href="/affiliation">
          <button class="btn">{{trans('main.dev.become_partner_btn')}}</button>
        </a>
      </div>
      <div class="box animated fadeInRight mt-2 mt-md-0">
        <img src="{{ asset('laravel_assets/images/mainStyleImages/api_logo.png') }}" alt="">
        <h1>Callburn API</h1>
        <h2>{{trans('main.dev.develop_and_personalize')}}</h2>
        <a href="/api">
          <button class="btn">{{trans('main.dev.go_to_api')}}</button>
        </a>
      </div>
    </div>
  </header>
</div>