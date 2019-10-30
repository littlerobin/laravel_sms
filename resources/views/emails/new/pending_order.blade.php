@include('emails.new.header')
<table class="row" style="
                            border-collapse: collapse;
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
        <tr style="
                    padding: 0;
                    text-align: left;
                    vertical-align: top;">
            <div class="logo" style="margin-top: 20px;">
                <span style="text-align: center; display: block;">
                    <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}call-burn-l-o-g-o@3x.png" class="img-responsive center-img"
                        style="-ms-interpolation-mode: bicubic;
                                clear: both;
                                display: block;
                                margin: 20px auto;
                                max-width: 100%;
                                outline: none;
                                text-decoration: none;
                                width: 30%;"
                                alt="logo">
                </span>
            </div>
            <div class="message-container credit-card" style="background-color: #F5FCFF;
                                                                padding: 10px;
                                                                padding-bottom: 30px;
                                                                padding-top: 20px;">
                <h1 style="margin: 0;
                            margin-bottom: 10px;
                            color: #777777;
                            font-family: 'Montserrat',
                            sans-serif;
                            font-size: 15px;
                            font-weight: normal;
                            line-height: 1.3;
                            margin: 0;
                            margin-bottom: 10px;
                            padding: 0;
                            text-align: center;
                            word-wrap: normal;">
                    {{trans('main.emails.your_order')}} {{explode('-', $invoice->order_number)[1]}} {{trans('main.emails.of')}} <span class="bold" style="font-weight: bold;">&#x20AC;{{$invoice->total_amount}}</span> {{trans('main.emails.is_still_pending')}}
                </h1>
                <h1 style="margin: 0;
                            margin-bottom: 10px;
                            color: #777777;
                            font-family: 'Montserrat', sans-serif;
                            font-size: 15px;
                            font-weight: normal;
                            line-height: 1.3;
                            margin: 0;
                            margin-bottom: 10px;
                            padding: 0;
                            text-align: center;
                            word-wrap: normal;">
                    {{trans('main.emails.remember_to_pay_it')}} <span class="bold" style="font-weight: bold;">{{trans('main.emails.before_next')}} 30</span> {{trans('main.emails.days')}}
                </h1>
                <span style="text-align: center; display: block;">
                    <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}fill-113.png" class="img-responsive center-img mt-20" 
                            style="-ms-interpolation-mode: bicubic;
                                    clear: both;
                                    display: block;
                                    margin: 0 auto;
                                    margin-top: 20px;
                                    outline: none;
                                    text-decoration: none;
                                    width: 20%;"
                                    alt="fill">
                </span>
                <span class="message mt-20" style="clear: both;
                                                    color: #777777;
                                                    display: block;
                                                    font-family: 'Montserrat', sans-serif;
                                                    font-size: 12px;
                                                    margin-top: 20px;
                                                    text-align: center;">
                    {{trans('main.emails.you_can_pay_the_order_with_a')}} <span class="bold" style="font-weight: 700;">{{trans('main.emails.bank_transfer')}}</span> ({{trans('main.emails.remember_to_specify')}} &#x201C;{{trans('main.emails.order')}}<br> {{explode('-', $invoice->order_number)[1]}}&#x201D; {{trans('main.emails.in_your_transfer_details')}}), {{trans('main.emails.using_one_of_the_following_banks')}}
                </span>
                <div class="col-lg-10 white-background mt-20 col-lg-offset-1" 
                    style="background-color: #fefefe;
                            border-radius: 20px;
                            margin-bottom: 60px;
                            margin-top: 20px;
                            min-height: 1px;
                            min-width: 480px;
                            padding: 14px;
                            padding-left: 15px;
                            padding-right: 15px;
                            position: relative;
                            height: 83px;">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" style="padding-left: 15px;
                                                padding-right: 15px;
                                                width: 108px;
                                                float: left;
                                                font-style: italic;
                                                margin: 20px 40px;">
                        <span class="order-cauntry" 
                                style="color: #3190e6;
                                display: block;
                                font-size: 15px;
                                margin-top: 10px;
                                font-style: italic;
                                width: 105px;">
                            <span style="font-weight: bold;
                                        text-align: center;">
                                {{trans('main.emails.ing_direct')}}
                            </span>
                        </span>
                        <span class="order-cauntry" style="color: #3190e6;
                                                            display: block;
                                                            font-size: 15px;
                                                            margin-top: 10px;
                                                            font-style: italic;
                                                            position: relative;">
                            <span style="display: block;
                                        font-weight: bold;
                                        margin-top: 10px;
                                        text-align: center;
                                        width: 105px;">
                                {{trans('main.emails.spain')}}
                            </span>
                        </span>
                    </div>
                    <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 order-info" style="min-height: 1px;
                                                            padding-left: 15px;
                                                            padding-right: 15px;
                                                            width: 242px;
                                                            float: right;
                                                            margin-top: 12px;
                                                            padding-bottom: 60px;">
                        <span class="bank-title" style="color: #3190e6;
                                                        display: block;
                                                        font-size: 17px;">
                            {{trans('main.emails.bank_account_details')}}
                        </span>
                        <span class="order-info-data" style="color: #777777;
                                                            display: block;
                                                            font-size: 13px;">
                            <span class="bold" style="font-weight: bold;">{{trans('main.emails.iban')}}:</span> {{trans('main.emails.es')}}
                        </span>
                        <span class="order-info-data" style="color: #777777;
                                                            display: block;
                                                            font-size: 13px;">
                            <span class="bold" style="font-weight: bold;">{{trans('main.emails.swift_bic_code')}}:</span> {{trans('main.emails.INGDESMMXXX')}}
                        </span>
                    </div>
                </div>
                <div class="row mt-20" style="margin-left: -15px;
                                                margin-right: -15px;
                                                margin-top: 20px;">
                    <span class="message " style="clear: both;
                                                    color: #777777;
                                                    display: block;
                                                    font-family: 'Montserrat', sans-serif; font-size: 12px;
                                                    margin-top: 0px;
                                                    text-align: center;">
                        {{trans('main.emails.you_may_be_more_faster_and_see')}},
                        <br>
                        {{trans('main.emails.using_the')}}
                    <span class="bold" style="font-weight: 700;">
                        {{trans('main.emails.secure_paypal_circuit')}}</span>.{{trans('main.emails.click_on_the_following_button_to_pay_with_Paypal')}}
                    </span>
                </div>

                <a href="{{config('email_templates.website_url')}}myaccount#/account/financials?invoice_id={{$invoice->_id}}" class="btn btn-success full-width" 
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
                            width: 60%;">
                                {{trans('main.emails.pay_it_with_paypal')}}
                </a>
            </div>
        </tr>
    </tbody>
</table>
{{-- @include('emails.new.footer') --}}
