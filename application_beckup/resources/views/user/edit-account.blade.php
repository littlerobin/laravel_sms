@extends('app-home')

@section('scripts')
    {!! HTML::script( asset('assets/callburn/js/dashboard/account.js') ) !!}
@endsection

@section('content')
<div class="background">
@include('header')

@include('message')
    <a href="{{action('UserController@getEditAccount')}}">
        <div class="top_icon">
            <img src="{{asset('assets/callburn/images/8.png')}}" class="icon" />
            <h5>{{trans('common.title_edit_account')}}</h5>
        </div>
    </a>
    {!! Form::hidden('language', $language,['id' => 'language']) !!}
    <div class="create">
        <div class="create_center">
            <h1 class="account_title">{{trans('common.title_edit_account')}}</h1>
            <div class="account_content">
                <div class="account_content_right">
                    <div class="account_rows_title">
                        <span class="account_titles">{{trans('common.electronico')}}</span>
                        <span class="account_titles">{{trans('common.zona')}}</span>
                        <span class="account_titles">{{trans('common.facturacion')}}</span>
                        <span class="account_titles">{{trans('common.patos')}}</span>
                    </div>
                    <div class="account_rows">
                        <span class="account_titles">{{Session::get('userData')['email']}}</span>
                        {!! Form::select('name', $timezone, null, ['id' => 'timezone']) !!}
                        <div class="account_data_container">
                            <div class="account_data_titles">
                                <span class="account_titles">{{trans('common.numero')}}:</span>
                                <span class="account_titles"> {{trans('common.cif')}}:</span>
                                <span class="account_titles">{{trans('common.telephone_title')}}:</span>
                                <span class="account_titles">{{trans('common.email')}}:</span>
                                <span class="account_titles">{{trans('common.direction')}}:</span>
                            </div>
                            <div class="account_data_rows">
                                <span class="account_data"></span>
                                <span class="account_data"></span>
                                <span class="account_data"></span>
                                <span class="account_data"></span>
                                <span class="account_data"></span>
                            </div>
                        </div>
                        <div class="account_btns_container">
                            <span class="account_btns" id='show_compiar'>
                                <img src="{{asset('assets/callburn/images/33.png')}}">
                                <span class="account_btns_title">
                                    {{trans('common.change_password')}}
                                </span>
                            </span>
                            <span class="account_btns" id='show_compiar_email'>
                                <img src="{{asset('assets/callburn/images/34.png')}}">
                                <span class="account_btns_title">
                                    {{trans('common.change_email')}}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="account_content_left">
                    <div class="account_content_left_title_container">
                        <span class="account_titles">{{trans('common.credit')}}</span>
                        <span class="account_titles">{{trans('common.telephone_title')}}</span>
                    </div>
                    <div class="account_content_left_contact_list_container">
                        <div class="credite">
                            <span class="credit_value">{{Session::get('balance')}} $</span>
                            <span class="credit_btn" id='show_credit'>
                                <img src="{{asset('assets/callburn/images/36.png')}}">
                                <span class="account_btns_title">
                                    {{trans('common.resargar_credito')}}
                                </span>
                            </span>
                        </div>
                        <div class="account_contact_list">
                            <table id='caller_id_content'>
                                @foreach($numbers as $number)
                                <tr>
                                    <td class="account_titles_table">{{$number['phone_number']}}</td>
                                    <td class="account_titles_table"></td>
                                    <td>
                                        <img data-action="{{action('UserController@getRemoveNumber',$number['_id'])}}" src="{{asset('assets/callburn/images/basket.png')}}" class='remove_number_show'>
                                    </td>
                                </tr>
                                @endforeach
                            </table> 
                        </div>
                        <div class="phone_number_input" id='number'>
                            <span class='error' id="error_red" style='display:none'></span>
                            <span class='error' id="error_green" style='display:none'></span>
                            {!! Form::hidden('token', csrf_token(),['id' => 'tok']) !!}
                            <div class="account_phone_number">
                                <span class="account_titles">{{trans('common.phone_number')}}</span>
                                {!! Form::text('number', null, ['id'=> 'val_number','placeholder' => trans('common.telephone_val'), 'class' => 'account_input']) !!}
                            </div>
                            <span id='reg_numb' class="account_phone_btn">
                                <img src="{{asset('assets/callburn/images/35.png')}}">
                                <span class="account_btns_title">
                                    {{trans('common.save_telephone')}}
                                </span>
                            </span>
                        </div>
                        <div class="phone_number_input" id='success_number' style='display:none'>
                            <span class='error' id="error_red1" style='display:none'></span>
                            <span class='error' id="error_green1" style='display:none'></span>
                            <div class="account_phone_number">
                                <span class="account_titles">{{trans('common.voice_code')}}</span>
                                {!! Form::text('number', null, ['id'=>'number_val','placeholder' => trans('commonm.code'), 'class' => 'account_input']) !!}
                            </div>
                            <div class="account_contact_name">
                                <span class="account_titles">{{trans('common.phone_number')}}</span>
                                {!! Form::text('newNumber', null, ['id' => 'val_success','placeholder' => trans('common.telephone_val'), 'class' => 'account_input']) !!}
                            </div>
                            <span id='reg_success' class="account_phone_btn">
                                <img src="{{asset('assets/callburn/images/35.png')}}">
                                <span class="account_btns_title">
                                    {{trans('common.save_telephone')}}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>      
    <div class="icons_container">
        <a href="{{action('UserController@getCreateCompaign')}}">
            <img src="{{asset('assets/callburn/images/7.png')}}" class="icon" />
        </a>    
        <a href="{{action('UserController@getCompanas','false')}}">
            <img src="{{asset('assets/callburn/images/9.png')}}" class="icon" />
        </a>            
    </div>
    <div class="window" id='myModal_account_content' style='display:none'>
        <div class="pop_up" id='myModal_account' style='display:none'>
            <div class="pop_up_center">
                <a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_account"></a>
                <div class="pop_up_text">
                    <h1>{{trans('common.title_edit_password')}}</h1>
                    <span class="pop_up2_span">{{trans('common.text_edit_password')}}</span>
                </div>
                {!! Form::open(array('action' => 'UserController@postPasswordEdit')) !!}
                <div class="account_popup">
                    
                    {!! Form::password('oldPassword', ['id' => 'emailAddress', 'class' => 'account_popup_content', 'placeholder' => trans('common.solder_edit_password')]) !!}
                    {!! Form::password('newPassword', ['id' => 'newPassword', 'class' => 'account_popup_content', 'placeholder' => trans('common.solder_new_password')]) !!}
                    {!! Form::password('newPasswordConfirmation', ['id' => 'newPasswordConfirmation', 'class' => 'account_popup_content', 'placeholder' => trans('common.solder_password_confirmation')]) !!}
                    
                </div>
                <button type="submit" class="account_popup_btns">
                    <img src="{{asset('assets/callburn/images/refresh.png')}}">
                    <span class="account_btns_title exit_account">
                        {{trans('common.save_password')}}
                    </span>
                </button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="window" id='myModal_email_content' style='display:none'>
        <div class="pop_up" id='myModal_email' style='display:none'>
            <div class="pop_up_center">
                <a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_email"></a>
                <div class="pop_up_text">
                    <h1>{{trans('common.title_edit_email')}}</h1>
                    <span class="pop_up2_span">{{trans('common.text_edit_email')}}</span>
                </div>
                {!! Form::open(array('action' => 'UserController@postEmailEdit')) !!}
                <div class="account_popup">
                    
                    {!! Form::text('email', null, ['id' => 'email', 'class' => 'account_popup_content', 'placeholder' => trans('common.solder_edit_email')]) !!}
                    {!! Form::text('emailConfirmation', null, ['id' => 'emailConfirmation', 'class' => 'account_popup_content', 'placeholder' => trans('solder_email_confirmation')]) !!}
                    {!! Form::password('password', ['id' => 'password', 'class' => 'account_popup_content', 'placeholder' => trans('common.solder_password')]) !!}
                    
                </div>
                <button type="submit" class="account_popup_btns">
                    <img src="{{asset('assets/callburn/images/refresh.png')}}">
                    <span class="account_btns_title exit_email">
                        {{trans('common.save_password')}}
                    </span>
                </button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="window" id='myModal_credit_content' style='display:none'>
        <div class="pop_up" id='myModal_credit' style='display:none'>
            <div class="pop_up_center">
                <a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_credit"></a>
                <div class="pop_up_text">
                    <h1>{{trans('common.title_credit')}}</h1>
                    <span class="pop_up2_span">{{trans('common.text_credit')}}</span>
                </div>
                {!! Form::open(array('action' => 'UserController@postCredit')) !!}
                <div class="account_popup1">
                    {!! Form::text('first_name', null, ['id' => 'f_name', 'class' => 'account_popup_content', 'placeholder' => trans('common.first_name')]) !!}
                    {!! Form::text('last_name', null, ['id' => 'l_name', 'class' => 'account_popup_content', 'placeholder' => trans('common.last_name')]) !!}
                    {!! Form::text('number', null, ['id' => 'number', 'class' => 'account_popup_content', 'placeholder' => trans('common.number')]) !!}
                    {!! Form::text('expiry_month', null, ['id' => 'expiry_month', 'class' => 'account_popup_content', 'placeholder' => trans('common.expiry_month')]) !!}
                    {!! Form::text('expiry_year', null, ['id' => 'expiry_year', 'class' => 'account_popup_content', 'placeholder' => trans('common.expiry_year')]) !!}
                    {!! Form::text('cvv', null, ['id' => 'cvv', 'class' => 'account_popup_content', 'placeholder' => trans('common.cvv')]) !!}
                    {!! Form::text('billing_address1', null, ['id' => 'billing_address1', 'class' => 'account_popup_content', 'placeholder' => trans('common.billing_address1')]) !!}
                </div>
                <div class="account_popup1">   
                    {!! Form::text('billing_country', null, ['id' => 'Billing Country', 'class' => 'account_popup_content', 'placeholder' => trans('common.billing_country')]) !!}
                    {!!Form::text('billing_city', null, ['id' => 'billing_city', 'class' => 'account_popup_content', 'placeholder' => trans('common.billing_city')]) !!}
                    {!! Form::text('billing_postcode', null, ['id' => 'billing_postcode', 'class' => 'account_popup_content', 'placeholder' => trans('common.billing_postcode')]) !!}
                    {!! Form::text('billing_state', null, ['id' => 'billing_state', 'class' => 'account_popup_content', 'placeholder' => trans('common.billing_state')]) !!}
                    {!! Form::text('amount', null, ['id' => 'amount', 'class' => 'account_popup_content', 'placeholder' => trans('common.amount')]) !!}
                    {!! Form::text('currency', null, ['id' => 'currency', 'class' => 'account_popup_content', 'placeholder' => trans('common.currency')]) !!}
                </div>
                <button type="submit" class="account_popup_btns">
                    <img src="{{asset('assets/callburn/images/refresh.png')}}">
                    <span class="account_btns_title exit_credit">
                        {{trans('common.save_credtit')}}
                    </span>
                </button>
                {!! Form::close() !!}
            </div>
        </div>
    </div> 
    <div class="window" id='myModal_remove_number_content' style='display:none'>
        <div class="pop_up" id='myModal_remove_number' style='display:none'>
            <div class="pop_up_center">
                <a href="#"><img src="{{asset('assets/callburn/images/000.png')}}" class="exit exit_remove_number"></a>
                <div class="pop_up_text">
                    <h1>{{trans('common.title_remove_number')}}</h1>
                    <span class="pop_up2_span">{{trans('common.text_remove_number')}}</span>
                    <div class="account_popup">
                        <a href="#" id='add_link_remove_number' class="account_popup_btns" >{{trans('common.yes')}}</a>
                        <a href="#" class="account_popup_btns exit_remove_number">{{trans('common.no')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection