<script>

   var token = '{!! $jwtToken !!}';
   var error = parseInt('{!! $error !!}');
   debugger;
    if(error) {
        localStorage.removeItem('isAdmin');
        window.location.assign(window.location.origin);
    } else {

        localStorage.setItem("jwtToken", token);
        localStorage.setItem("isAdmin", true);
        window.location.assign ('/myaccount');
    }

</script>
