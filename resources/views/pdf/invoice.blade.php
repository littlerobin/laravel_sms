<html>
<head>
    <title></title>
    <style type="text/css">
    body{
        padding: 40px;
        margin: 0px;
    }

    .callburn_logo_container{

    }
    .ios_android_icons {
        display: inline-block;
        background-size: contain;
        vertical-align: middle;
    }
    .youtube_icon {
        height: 100px;
        width: 100px;
        background: url("{{config('url')}}laravel_assets/images/footer_youtube.png") no-repeat left;
    }
    .facebook_icon {
        height: 100px;
        width: 100px;
        background: url("{{config('url')}}laravel_assets/images/footer_facebook.png") no-repeat right;
    }
    .ios_android_icons.app-store-icon {
        height: 50px;
        background: url("{{config('url')}}laravel_assets/images/app-store-icon.png") no-repeat left;
    }
    .ios_android_icons.android-icon {
        height: 50px;
        background: url("{{config('url')}}laravel_assets/images/android-icon.png") no-repeat right;
    }
    img.callburn_logo{
        max-width: 170px;
        height: 50px;
    }

    .invoice_h3{
        font-family: Arial, sans-serif;
        font-size: 18px;
        color: #23bdff;
        margin-top: 0;
        margin-bottom:0px;
        padding-bottom:0px;
    }

    .invoice_h4{
        margin-top: -10px;
        text-align: right;
        font-family: Arial, sans-serif;
        font-size: 18px;
        color: #262626;
    }

    .invoice_sp1{
        display: block;
        text-align: left;
        color: #262626;
        font-family: Arial, sans-serif;
        font-size: 16px;
    }

    .invoice_sp2{
        display: block;
        text-align: right;
        color: #262626;
        font-family: Arial, sans-serif;
        font-size: 16px;
    }

    .invoice_table_row_container{
        position: relative;
        padding: 6px 0px;
        width: 100%;
        border-bottom: 1px solid #979797;
    }

    .invoice_table_row_container::after{
        content: "";
        display: block;
        clear: both;
    }

    .invoice_table_row_container2{
        height: 30px;
    }

    .invoice_table_left_content{
        position: relative;
        padding: 0px 15px;
        width: 400px;
        float: left;
        text-align: center;
        color: #262626;
        font-family: Arial, sans-serif;
        font-size: 16px;
        border-bottom: 1px solid #979797;
        border-right: 1px solid #979797;
    }

    .invoice_table_left_content1{
        border-top: 1px dashed #979797;
        border-right: 1px dashed #979797;
        text-align: right;
        border-bottom: none;
    }

    .invoice_table_right_content{
        position: relative;
        width: 135px;
        float: left;
        text-align: center;
        color: #262626;
        font-family: Arial, sans-serif;
        font-size: 16px;
        border-bottom: 1px solid #979797;
    }

    footer{
        position: relative;
        margin-top: 100px;
        width: 100%;
    }

    .ios_android_icons{
        display: inline-block;
        width: 170px;
        float: left;
        cursor: pointer;
        margin-left:0px;
        margin-right:150px;
    }
    .footer_sp{
        display: block;
        text-align: center;
        font-size: 18px;
        font-family: Raleway-Regular,sans-serif;
        color: #3190E6;
    }
    .footer_sp small{
        font-size: 20px;
        font-weight: 600;
    }
    .footer_sp.happy_customer{
        font-size: 32px;
        font-weight: bold;
        line-height: 1;
    }
    .fb_google_icons{
        text-align: center;
        margin: 0 5px;
    }
    .fb_google_icons img{
        width: 55px;
        height: 55px;
        text-align: center;
    }
    .footer_icons{
        text-align: center;
        margin: 0 auto;
    }
    table tr td {
        line-height: 30px;
        height: 30px;   
    }
</style>
</head>
<body>
    <div style="position: relative; margin: 0px auto; width: 600px; display: block;">

        <table style="margin-top:30px;">
            <tbody>
                <tr>
                    <td>
                        <div class="callburn_logo_container">
                            <img class="callburn_logo" src="{{config('url')}}laravel_assets/images/callburn_logo.png">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        @if (strtotime($invoice['invoice_date']) < strtotime('05/12/2019'))
        <h3 class="invoice_h3">Callburn Services SL</h3>
        @endif

        @if (strtotime($invoice['invoice_date']) > strtotime('05/12/2019'))
        <h3 class="invoice_h3">Callburn - Roberto Innaimi</h3>
        @endif

        <table style="margin-top:0px;padding-top:0px;">
            <tbody>
                <tr>
                    <td>
                        <span class="invoice_sp1">Calle Monseñor Antonio Hurtado de Mendoza, 8</span>
                        <span class="invoice_sp1">03923, Elx (Alicante),</span>
                        <span class="invoice_sp1">Spain</span>
                                @if (strtotime($invoice['invoice_date']) < strtotime('05/12/2019'))
                                <span class="invoice_sp1">ES-B54750716</span>
                                @endif
                                @if (strtotime($invoice['invoice_date']) > strtotime('05/12/2019'))
                                <span class="invoice_sp1">ES-Y2729986S</span>
                                @endif
                        <span class="invoice_sp1">(EUROPEAN VAT ID)</span>
                        <span class="invoice_sp1"><b>www.callburn.com  info@callburn.com</b></span>
                    </td>
                    <td><span style="width: 150px; opacity: 0; visibility: hidden;">gfqgqgqgq</span></td>
                    <td>
                        <h4 class="invoice_h4">{{$invoice['customer_name']}}</h4>
                        <span class="invoice_sp2">{{$user->company_name}}</span>
                        <span class="invoice_sp2">{{$invoice['customer_address']}}</span>
                        <span class="invoice_sp2">{{$invoice['customer_postal_code']}} {{$invoice['customer_city']}}</span>
                        <span class="invoice_sp2">{{$invoice['customer_country_code']}}</span>
                        @if($invoice['vat_id'])
                        <span class="invoice_sp2">{{$invoice['vat_id']}}</span>
                        @endif
                    </td>
                </tr>
            </tbody>           
        </table>
        <table style="margin-top:30px;">
            <tbody>
                <tr>
                    <td>
                        <span class="invoice_sp1">Order Number: {!! explode('-', $invoice['order_number'])[1] !!} of {{$invoice['order_date']}} </span>
                        <span class="invoice_sp1">Payment method: {{$invoice['method'] == 'stripe' ? 'Credit Card/ Apple Pay' : $invoice['method']}}</span>
                        <span class="invoice_sp1">Transaction date: {{$invoice['invoice_date']}}</span>
                        <span class="invoice_sp1">Purchased by: {{$invoice['customer_name']}}</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <h3 class="invoice_h3">Invoice N° {{$invoice['invoice_number']}} of {{$invoice['invoice_date']}}</h3>
        @if($invoice['type'] == 'REFUND')
        <h3 class="invoice_h3">Original Invoice number {{$invoice['original_invoice_number']}}</h3>
        @endif
        <div style="border: 1px solid #979797;">
            <table style="margin: 0px auto; border-radius: 11px;">
                <tbody>
                    <tr style="border-bottom: 1px solid #979797;">
                        <td>
                            <div class="invoice_table_left_content">Description</div>
                        </td>
                        <td>
                            <div class="invoice_table_right_content">Amount</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="invoice_table_left_content">
                                @if($invoice['type'] == 'MANUAL_SERVICE')
                                Professional audio message billing
                                @else
                                Credit Recharge
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="invoice_table_right_content">&#128; 
                                @if($invoice['type'] == 'MANUAL_SERVICE')
                                -
                                @endif
                                {{$invoice['purchased_amount'] + $invoice['discount_amount']}}
                            </div>
                        </td>
                    </tr>
                    @if($invoice['discount_amount'])
                    <tr>
                        <td>
                            <div class="invoice_table_left_content">Promotion</div>
                        </td>
                        <td>
                            <div class="invoice_table_right_content"> &#128; {{- $invoice['discount_amount']}}</div>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>
                            <div class="invoice_table_row_container invoice_table_row_container2" style="border-bottom: none;">
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="invoice_table_left_content invoice_table_left_content1">Total without VAT</div>
                            </td>
                            <td>
                                <div class="invoice_table_right_content" style="border-bottom: none; border-top: 1px dashed #979797;">€ 
                                    @if($invoice['type'] == 'MANUAL_SERVICE')
                                    -
                                    @endif
                                    {{$invoice['purchased_amount']}}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="invoice_table_left_content invoice_table_left_content1">VAT ({{$invoice['vat_percentage']}}%)</div>
                            </td>
                            <td>
                                <div class="invoice_table_right_content" style="border-bottom: none; border-top: 1px dashed #979797;">€ 
                                    @if($invoice['type'] == 'MANUAL_SERVICE')
                                    -
                                    @endif
                                    {{$invoice['vat_amount']}}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="invoice_table_left_content invoice_table_left_content1"><b>Grand Total</b></div>
                            </td>
                            <td>
                                <div class="invoice_table_right_content" style="border-bottom: none; border-top: 1px dashed #979797;"><b>€ 
                                    @if($invoice['type'] == 'MANUAL_SERVICE')
                                    -
                                    @endif
                                    {{$invoice['total_amount']}}
                                </b>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <p></p>
    <p></p>

    <span class="footer_sp happy_customer">Our customers are happy</span>
    <span class="footer_sp"><small>(and you? let us know, talk with us)</small></span>

    <table class="footer_icons">
        <tr>
            <td>
                <div class="fb_google_icons">
                    <img src="{{config('url')}}laravel_assets/images/footer_facebook.png" alt="">
                </div>
            </td>
            <td>
                <div class="fb_google_icons">
                    <img src="{{config('url')}}laravel_assets/images/footer_youtube.png" alt="">
                </div>
            </td>
        </tr>
    </table>
</body>
</html>