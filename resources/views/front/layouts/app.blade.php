@include('front.partials.header')
{{--@include('front.partials.navbars.navbar-blue')--}}
@include('front.partials.navbars.navbar-index')
	    @yield('content')
@include('front.partials.footer')

@include('front.partials.javascripts')