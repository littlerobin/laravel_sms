@if(Session::has('message'))
    <div class="error_container" id="red_error">
        *{{ Session::get('message') }}   
    </div>
@endif
@if(Session::has('message_success'))
    <div class="error_container" id="green_error">
        *{{ Session::get('message_success') }}   
    </div>
@endif