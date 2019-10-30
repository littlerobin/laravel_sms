@include('emails.new.header')
<table class="row" style="border-collapse: collapse; border-spacing: 0; display: table; margin-left: -15px; margin-right: -15px; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;"><tbody><tr style="padding: 0; text-align: left; vertical-align: top;">
        <div class="logo" style="margin-top: 20px;">
            <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}call-burn-l-o-g-o@3x.png" class="img-responsive center-img" style="-ms-interpolation-mode: bicubic; clear: both; display: block; margin: 20px auto; max-width: 100%; outline: none; text-decoration: none; width: 30%;" alt="logo">
        </div>
        <div class="message-container" style="background-color: #F5FCFF; padding: 10px; padding-bottom: 30px; padding-top: 20px;">
                            <span class="message caller" style="clear: both; color: #777777; display: block; font-family: 'Montserrat', sans-serif; font-size: 16px; margin-top: 20px; text-align: center;">
                                {{trans('main.emails.a_request_to_change_your_email_address_with')}}

            {{trans('main.emails.please_click_on_the_following_button_to_verify')}}
                            </span>
            <a href="{{config('email_templates.website_url')}}auth/confirm-email-address/{{$token}}" class="btn btn-success full-width" style="-moz-user-select: none; -ms-user-select: none; -webkit-user-select: none; Margin: 0; background-color: #22cd78; background-image: none; border: 1px solid transparent; border-color: #22cd78; border-radius: 4px; color: #fff; cursor: pointer; display: block; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 1.42857; margin: 20px auto; margin-bottom: 0; padding: 6px 12px; text-align: center; text-decoration: none; touch-action: manipulation; user-select: none; vertical-align: middle; white-space: nowrap; width: 60%;">{{trans('main.emails.confirm_address_changing')}}</a>
            <span class="message caller" style="clear: both; color: #777777; display: block; font-family: 'Montserrat', sans-serif; font-size: 16px; margin-top: 40px; text-align: center;">{{trans('main.emails.after_the_confirmation_you_will_need_to_use_this')}}</span>
            <span class="message red" style="clear: both; color: #df635d; display: block; font-family: 'Montserrat', sans-serif; font-size: 12px; margin-top: 40px; text-align: center;">{{trans('main.emails.if_not_confirmed_this_request_will_expire')}}</span>
        </div>
    </tr></tbody></table>
{{-- @include('emails.new.footer') --}}