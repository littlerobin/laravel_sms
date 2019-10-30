<!--  <html>
<head>
	<title></title>
	<style type="text/css">
		body{
			padding: 40px;
			margin: 0px;
		}

		.invoice_container{
			position: relative;
			width: 100%;
			background: #fff;
		}

		.callburn_logo_container{
			position: relative;
			width: 160px;
		}

		.callburn_logo{
			display: block;
			width: 100%;
		}

		.invoice_text_container{
			position: relative;
			margin-top: 50px;
			width: 100%;
		}

		.invoice_text_container::after{
			content: "";
			display: block;
			clear: both;
		}

		.invoice_text_left_container{
			position: relative;
			width: 420px;
			float: left;
		}

		.invoice_text_right_container{
			position: relative;
			/*margin-left: 50px;*/
			width: 300px;
			float: right;
		}

		.invoice_h3{
			font-family: HelveticaNeue-Bold;
			font-size: 18px;
			color: #23bdff;
		}

		.invoice_h4{
			margin-top: 0px;
			text-align: right;
			font-family: HelveticaNeue-Bold;
			font-size: 18px;
			color: #262626;
		}

		.invoice_sp1{
			display: block;
			text-align: left;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
		}

		.invoice_sp2{
			display: block;
			text-align: right;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
		}

		.invoice_table_container{
			position: relative;
			margin-top: 50px;
			width: 650px;
			border-radius: 11px;
			border: 1px solid #979797;
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

		/*.invoice_table_row_container1{
			padding: 10px 0px;
		}*/

		.invoice_table_row_container2{
			padding: 20px 0px;
		}

		.invoice_table_row_container3{
			border-bottom: none;
			border-left: none;
			border-right: none;
			border-top: 1px dashed #979797;
		}

		.invoice_table_left_content{
			position: relative;
			width: 500px;
			float: left;
			text-align: center;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
		}

		.invoice_table_left_content1{
			padding-right: 10px;
			border-right: 1px dashed #979797;
			text-align: right;
		}

		.invoice_table_right_content{
			position: relative;
			width: 135px;
			float: left;
			text-align: center;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
			border-left: 1px solid #979797;
		}

		footer{
			position: relative;
			margin-top: 100px;
			width: 100%;
		}

		.footer_center{
			position: relative;
			margin: 0px auto;
			width: 400px;
		}

		.footer_ios_android_icon_container{
			position: relative;
			width: 100%;
		}

		.footer_ios_android_icon_container::after{
			content: "";
			display: block;
			clear: both;
		}

		.ios_android_icons{
			display: block;
			margin-left: 20px;
			width: 170px;
			float: left;
			cursor: pointer;
		}

		.footer_sp{
			display: block;
			text-align: center;
			font-size: 18px;
			font-family: Raleway-Regular;
			color: #23bdff;
		}

		.footer_fb_google_icons_container{
			position: relative;
			margin: 0px auto;
			width: 150px;
		}

		.footer_fb_google_icons_container::after{
			content: "";
			display: block;
			clear: both;
		}

		.fb_google_icons{
			display: block;
			margin-left: 15px;
			width: 50px;
			float: left;
			cursor: pointer;
		}
	</style>
</head>
<body>
	<div class="invoice_container">
		<div class="callburn_logo_container">
			<img src="{{asset('assets/images/callburn_logo.png')}}" class="callburn_logo" />
		</div>
		<div class="invoice_text_container">
			<h3 class="invoice_h3">Callburn Services SL</h3>
			<div class="invoice_text_left_container">
				<span class="invoice_sp1">Calle Monseñor Antonio Hurtado de Mendoza, 8</span>
				<span class="invoice_sp1">03923, Elx (Alicante),</span>
				<span class="invoice_sp1">Spain</span>
				<span class="invoice_sp1">ES-B76146174</span>
				<span class="invoice_sp1">(EUROPEAN VAT ID)</span>
				<span class="invoice_sp1"><b>www.callburn.com - info@callburn.com</b></span>
			</div>
			<div class="invoice_text_right_container">
				<h4 class="invoice_h4">{{$invoice['customer_name']}}</h4>
				<span class="invoice_sp2">{{$invoice['customer_address']}}</span>
				<span class="invoice_sp2">{{$invoice['customer_postal_code']}} {{$invoice['customer_city']}}</span>
				<span class="invoice_sp2">{{$invoice['country_code']}}</span>
				@if($invoice['vat_id'])
				<span class="invoice_sp2">{{$invoice['vat_id']}}</span>
				@endif
			</div>
		</div>
		<div class="invoice_text_container">
			<div class="invoice_text_left_container">
				<span class="invoice_sp1">Order Number: {!! explode('-', $invoice['hash'])[1] !!} of {{$invoice['created_at']}} </span>
				<span class="invoice_sp1">Payment method: {{$invoice['method']}}</span>
				<span class="invoice_sp1">Transaction date: {{$invoice['invoice_date']}}</span>
				<span class="invoice_sp1">Purchased by: {{$invoice['customer_name']}}</span>
			</div>
		</div>
		<h3 class="invoice_h3">Invoice N° {{$invoice['unique_name']}} of {{$invoice['invoice_date']}}</h3>
		<div class="invoice_table_container">
			<div class="invoice_table_row_container invoice_table_row_container1">
				<div class="invoice_table_left_content">Description</div>
				<div class="invoice_table_right_content">Amount</div>
			</div>
			<div class="invoice_table_row_container">
				<div class="invoice_table_left_content">Credit Recharge</div>
				<div class="invoice_table_right_content">{{$invoice['total'] + $invoice['discount']}}</div>
			</div>
			<div class="invoice_table_row_container">
				<div class="invoice_table_left_content">Promotion</div>
				<div class="invoice_table_right_content">- {{$invoice['discount']}}</div>
			</div>
			<div class="invoice_table_row_container invoice_table_row_container2" style="border-bottom: none;">
	
			</div>
			<div class="invoice_table_row_container invoice_table_row_container3">
				<div class="invoice_table_left_content invoice_table_left_content1">Total without VAT</div>
				<div class="invoice_table_right_content">{{$invoice['amount']}}</div>
			</div>
			<div class="invoice_table_row_container invoice_table_row_container3">
				<div class="invoice_table_left_content invoice_table_left_content1">VAT ({{$invoice['vat_percentage']}}%)</div>
				<div class="invoice_table_right_content">{{$invoice['vat_amount']}}</div>
			</div>
			<div class="invoice_table_row_container invoice_table_row_container3">
				<div class="invoice_table_left_content invoice_table_left_content1">Grand Total	</div>
				<div class="invoice_table_right_content">{{$invoice['total']}}</div>
			</div>
		</div>
		<footer>
			<div class="footer_center">
				<div class="footer_ios_android_icon_container">
					<img src="{{asset('assets/images/app-store-icon.png')}}" class="ios_android_icons">
					<img src="{{asset('assets/images/android-icon.png')}}" class="ios_android_icons">
				</div>
				<span class="footer_sp">Our customers are happy </span>
				<span class="footer_sp">(and you? let us know, chat with us)</span>
				<div class="footer_fb_google_icons_container">
					<img src="{{asset('assets/images/facebook-icon.png')}}" class="fb_google_icons">
					<img src="{{asset('assets/images/google-plus-icon.png')}}" class="fb_google_icons">
				</div>
			</div>
		</footer>
	</div>
</body>
</html> -->

 <html>
<head>
	<title></title>
	<style type="text/css">
		body{
			padding: 40px;
			margin: 0px;
		}

		.invoice_container{
			position: relative;
			width: 100%;
			background: #fff;
		}

		.callburn_logo_container{
			position: relative;
			width: 160px;
		}

		.callburn_logo{
			display: block;
			width: 100%;
		}

		.invoice_text_container{
			position: relative;
			margin-top: 50px;
			width: 100%;
		}

		.invoice_text_container::after{
			content: "";
			display: block;
			clear: both;
		}

		.invoice_text_left_container{
			position: relative;
			width: 400px;
			float: left;
		}

		.invoice_text_right_container{
			position: relative;
			/*margin-left: 50px;*/
			width: 300px;
			float: right;
		}

		.invoice_h3{
			margin-top: 50px;
			font-family: HelveticaNeue-Bold;
			font-size: 18px;
			color: #23bdff;
		}

		.invoice_h4{
			margin-top: -10px;
			text-align: right;
			font-family: HelveticaNeue-Bold;
			font-size: 18px;
			color: #262626;
		}

		.invoice_sp1{
			display: block;
			text-align: left;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
		}

		.invoice_sp2{
			display: block;
			text-align: right;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
		}

		.invoice_table_container{
			position: relative;
			margin-top: 50px;
			width: 650px;
			border-radius: 11px;
			border: 1px solid #979797;
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

		/*.invoice_table_row_container1{
			padding: 10px 0px;
		}*/

		.invoice_table_row_container2{
			height: 30px;
		}

		.invoice_table_row_container3{
			border-bottom: none;
			border-left: none;
			border-right: none;
			border-top: 1px dashed #979797;
		}

		.invoice_table_left_content{
			position: relative;
			padding: 0px 15px;
			width: 400px;
			float: left;
			text-align: center;
			color: #262626;
			font-family: HelveticaNeue;
			font-size: 16px;
			border-bottom: 1px solid #979797;
			border-right: 1px solid #979797;
		}

		.invoice_table_left_content1{
			   border-top: 1px dashed #979797;
		    /* padding-right: 10px; */
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
			font-family: HelveticaNeue;
			font-size: 16px;
			border-bottom: 1px solid #979797;
		}

		footer{
			position: relative;
			margin-top: 100px;
			width: 100%;
		}

		.footer_center{
			position: relative;
			margin: 0px auto;
			width: 400px;
		}

		.footer_ios_android_icon_container{
			position: relative;
			width: 100%;
		}

		.footer_ios_android_icon_container::after{
			content: "";
			display: block;
			clear: both;
		}

		.ios_android_icons{
			display: block;
			width: 170px;
			float: left;
			cursor: pointer;
		}

		.footer_sp{
			display: block;
			text-align: center;
			font-size: 18px;
			font-family: Raleway-Regular;
			color: #23bdff;
		}

		.footer_fb_google_icons_container{
			position: relative;
			margin: 0px auto;
			width: 150px;
		}

		.footer_fb_google_icons_container::after{
			content: "";
			display: block;
			clear: both;
		}

		.fb_google_icons{
			display: block;
			width: 50px;
			float: left;
			cursor: pointer;
		}
	</style>
</head>
<body>
	<div style="position: relative; margin: 0px auto; width: 600px;"> 
	<h3 class="invoice_h3">Callburn Services SL</h3>
	<table style="margin: 0px auto;">
		<tr>
			<td>
				<span class="invoice_sp1">Calle Monseñor Antonio Hurtado de Mendoza, 8</span>
				<span class="invoice_sp1">03923, Elx (Alicante),</span>
				<span class="invoice_sp1">Spain</span>
				<span class="invoice_sp1">ES-B76146174</span>
				<span class="invoice_sp1">(EUROPEAN VAT ID)</span>
				<span class="invoice_sp1"><b>www.callburn.com - info@callburn.com</b></span>
			</td>
			<td><span style="display: block; width: 100px; opacity: 0;">Calle Monseñor Antonio</span></td>
			<td>
				<h4 class="invoice_h4">{{$invoice['customer_name']}}</h4>
				<span class="invoice_sp2">{{$invoice['customer_address']}}</span>
				<span class="invoice_sp2">{{$invoice['customer_postal_code']}} {{$invoice['customer_city']}}</span>
				<span class="invoice_sp2">{{$invoice['country_code']}}</span>
				@if($invoice['vat_id'])
				<span class="invoice_sp2">{{$invoice['vat_id']}}</span>
				@endif
			</td>
		</tr>
	</table>
	<table style="margin-top:30px;">
		<tr>
			<td>
				<span class="invoice_sp1">Order Number: {!! explode('-', $invoice['hash'])[1] !!} of {{$invoice['created_at']}} </span>
				<span class="invoice_sp1">Payment method: {{$invoice['method']}}</span>
				<span class="invoice_sp1">Transaction date: {{$invoice['invoice_date']}}</span>
				<span class="invoice_sp1">Purchased by: {{$invoice['customer_name']}}</span>
			</td>
		</tr>
	</table>

	<h3 class="invoice_h3">Invoice N° {{$invoice['unique_name']}} of {{$invoice['invoice_date']}}</h3>

	<table style="margin: 0px auto; margin-top: 30px; border: 1px solid #979797; border-radius: 11px;">
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
				<div class="invoice_table_left_content">Credit Recharge</div>
			</td>
			<td>
				<div class="invoice_table_right_content">&#128; {{$invoice['total'] + $invoice['discount']}}</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="invoice_table_left_content">Promotion</div>
			</td>
			<td>
				<div class="invoice_table_right_content">- &#128; {{$invoice['discount']}}</div>
			</td>
		</tr>
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
				<div class="invoice_table_right_content" style="border-bottom: none; border-top: 1px dashed #979797;">{{$invoice['amount']}}</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="invoice_table_left_content invoice_table_left_content1">VAT ({{$invoice['vat_percentage']}}%)</div>
			</td>
			<td>
				<div class="invoice_table_right_content" style="border-bottom: none; border-top: 1px dashed #979797;">{{$invoice['vat_amount']}}</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="invoice_table_left_content invoice_table_left_content1"><b>Grand Total</b></div>
			</td>
			<td>
				<div class="invoice_table_right_content" style="border-bottom: none; border-top: 1px dashed #979797;"><b>{{$invoice['total']}}</b></div>
			</td>
		</tr>
	</table>

	<table style="margin: 50px auto 15px;">
		<tr>
			<td><img src="{{asset('assets/images/app-store-icon.png')}}" class="ios_android_icons"></td>
			<td><img src="{{asset('assets/images/android-icon.png')}}" class="ios_android_icons"></td>
		</tr>
	</table>	
	<span class="footer_sp">Our customers are happy</span>
	<span class="footer_sp">(and you? let us know, chat with us)</span>
	<table style="margin: 20px auto 0px;">
		<tr>
			<td><img src="{{asset('assets/images/facebook-icon.png')}}" class="fb_google_icons"></td>
			<td><img src="{{asset('assets/images/google-plus-icon.png')}}" class="fb_google_icons"></td>
		</tr>
	</table>
	</div>
</body>
</html>