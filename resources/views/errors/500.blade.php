@extends('front.layouts.app-login')


@section('content')

    <div class="page-404">
        <div id="main-content">
            <div class="container">
                <div class="col-sm-6 text-center">
                    <img class="img1" src="{{asset('laravel_assets/images/front/img/img_404.svg')}}" alt=""></div>
                <div class="col-sm-6">
                    <img class="img2" src="{{asset('laravel_assets/images/front/img/500.svg')}}" alt="">
                    <p>Service is unavailable at the moment, but our team is working and everything will be fixed in some minutes.</p>
                    <br>
                    <p>Algo no ha funcionado correctamente y estamos trabajando para solucionar el problema. En poquitos minutos estaremos de nuevos operativos.</p>
                    <br>
                    <p>Il servizio non e’ disponibile al momento, ma il nostro team stá gia’ lavorando sulla problematica e tutto sará risolto nel giro di pochi minuti.</p>

            </div>
        </div>

    </div>


@stop