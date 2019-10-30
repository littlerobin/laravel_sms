<script>
                window.locale = '{!! \App::getLocale() !!}';
                window.isEUMember = Boolean('{!! $isEUMember !!}');
                window.currentUrl = '{!! json_encode(Request::segments()) !!}';
    </script>
    <script src="{{ asset('laravel_assets/front/js/select2.js') }}"></script>
    <script src="{{ elixir('laravel_assets/front/js/app.js') }}"></script>
    <script>window.ctc_token = String('{!! config('app.ctc_token') !!}')</script>
    {{-- @if($tab == 'click-to-call')
        <script src="{{ elixir('laravel_assets/front/js/snippet.js') }}"></script>
    @endif --}}
    <script>
                if (JSON.parse(window.currentUrl)[1] === "iframe-content-voice-messages" 
                   || JSON.parse(window.currentUrl)[1] === "iframe-content-clicktocall"
                   ) {
                    setInterval(function () {
                        if (document.getElementById("closedTypeDiv")) {
                            document.getElementById("closedTypeDiv").remove();
                        }
                    }, 200);
                } 
    </script>
    
    <!-- <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <script async src="https://cdn.ampproject.org/v0.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

    </body>
</html>
