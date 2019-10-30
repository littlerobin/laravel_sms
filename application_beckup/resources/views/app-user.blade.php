<!DOCTYPE html>
<html lang="en">
<head>
	<script type="text/javascript">
window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
$.src="//v2.zopim.com/?3FwfSWGxaCDafJ0PKIxgtdudHuiiQZmK";z.t=+new Date;$.
type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
</script>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	{!! HTML::style( asset('assets/callburn/style/css/style.css') ) !!}
	<!-- {!! HTML::style( asset('assets/callburn/style/jquery-ui.css') ) !!} -->
	{!! HTML::style( asset('http://fonts.googleapis.com/css?family=Lato:300') ) !!}
	{!! HTML::style( asset('http://fonts.googleapis.com/css?family=Open+Sans:300') ) !!}
	{!! HTML::style( asset('http://fonts.googleapis.com/css?family=Source+Sans+Pro') ) !!}
	{!! HTML::style( asset('http://fonts.googleapis.com/css?family=Ubuntu:300') ) !!}
	{!! HTML::style( asset('http://fonts.googleapis.com/css?family=Muli') ) !!}

	@yield('styles')
</head>
<body>
	@yield('content')

	<!-- Scripts !-->
	{!! HTML::script( asset('assets/callburn/js/jquery-1.11.1 (1).js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/jquery_transition.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/jquery-ui.js') ) !!}
	{!! HTML::script( asset('assets/callburn/js/init.js') ) !!}
	@yield('scripts')

	@include('footer')
</body>
</html>