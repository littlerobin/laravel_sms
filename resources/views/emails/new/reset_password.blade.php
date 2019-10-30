@include('emails.new.header')
<table class="row" style="border-collapse: collapse; 
                            border-spacing: 0; 
                            display: table; 
                            margin-left: -15px; 
                            margin-right: -15px; 
                            padding: 0; 
                            position: relative; 
                            text-align: left; 
                            vertical-align: top; 
                            width: 100%;">
    <tbody>
        <tr style="padding: 0; 
                    text-align: left; 
                    vertical-align: top;">
            <div class="logo" style="margin-top: 20px;">
                <span style="display: block;
                            text-align: center;">
                    <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}call-burn-l-o-g-o@3x.png" class="img-responsive center-img" alt="logo" 
                        style="-ms-interpolation-mode: bicubic; 
                                clear: both; 
                                display: block; 
                                margin: 20px auto; 
                                max-width: 100%; 
                                outline: none; 
                                text-decoration: none;
                                width: 30%;">
                </span>
            </div>
            <div class="message-container" style="background-color: #F5FCFF; 
                                                    padding: 10px; 
                                                    padding-bottom: 30px; 
                                                    padding-top: 20px; 
                                                    text-align: center;">
                <h1 style="margin: 0; 
                            margin-bottom: 10px; 
                            color: #777777; 
                            font-family: 'Nunito Sans', sans-serif; 
                            font-size: 25px; 
                            font-weight: normal; 
                            line-height: 1.3; 
                            margin: 0; 
                            margin-bottom: 10px; 
                            padding: 0; 
                            text-align: center; 
                            word-wrap: normal;">
                    {{trans('main.emails.did_you_lost_your_password')}}
                </h1>
                <h1 style="margin: 0; 
                            margin-bottom: 10px; 
                            color: #777777; 
                            font-family: 'Nunito Sans', sans-serif; 
                            font-size: 25px; 
                            font-weight: normal; 
                            line-height: 1.3; 
                            margin: 0; 
                            margin-bottom: 10px; 
                            padding: 0; 
                            text-align: center; 
                            word-wrap: normal;">
                    {{trans('main.emails.we_are_here_to')}} <span class="bold" style="font-weight: bold;">{{trans('main.emails.help_you')}}</span>
                </h1>
                <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}crypted-icon.png" class="img-responsive center-img mt-20" alt="crypted"
                    style="-ms-interpolation-mode: bicubic; 
                            clear: both; display: block; 
                            margin: 0 auto; margin-top: 20px; 
                            max-width: 100%; 
                            outline: none; 
                            text-decoration: none;
                            width: 20%;">
                <span class="message caller" style="clear: both; 
                                                    color: #777777; 
                                                    display: block; 
                                                    font-family: 'Montserrat', sans-serif; 
                                                    font-size: 16px; 
                                                    margin-top: 40px; 
                                                    text-align: center;">{{trans('main.emails.to_restore_your_password_just')}}.</span>
                <a href="{{config('email_templates.website_url')}}/password-reset/{{$token}}" class="btn btn-success full-width" 
                    style="-moz-user-select: none; 
                            -ms-user-select: none; 
                            -webkit-user-select: none; 
                            margin: 0; 
                            background-color: #22cd78; 
                            background-image: none; 
                            border: 1px solid transparent; 
                            border-color: #22cd78; 
                            border-radius: 4px; 
                            color: #fff; 
                            cursor: pointer; 
                            display: block; 
                            font-family: Helvetica, Arial, sans-serif; 
                            font-size: 14px; 
                            font-weight: bold; 
                            line-height: 1.42857; 
                            margin: 20px auto; 
                            margin-bottom: 0; 
                            padding: 6px 12px; 
                            text-align: center; 
                            text-decoration: none; 
                            touch-action: manipulation; 
                            user-select: none; 
                            vertical-align: middle;
                            width: 60%;">{{trans('main.emails.restore_my_password')}}</a>
            </div>
        </tr>
    </tbody>
</table>
{{-- @include('emails.new.footer') --}}
