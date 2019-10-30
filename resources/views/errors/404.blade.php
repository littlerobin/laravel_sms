@extends('front.layouts.app-login')


@section('content')

    <div class="page-404">
        <div id="main-content">
            <div class="container">
                <div class="col-sm-6 text-center">
                    <img class="img1" src="{{asset('laravel_assets/images/front/img/img_404.svg')}}" alt=""></div>
                <div class="col-sm-6">
                    <img class="img2" src="{{asset('laravel_assets/images/front/img/img_404_.svg')}}" alt="">
                    <h3 class="text-uppercase">Looks like you're lost</h3>
                    <p>The page you are looking for is not available</p>
                    <p><a class="text-uppercase" href="/">go back to callburn</a></p>
                </div>
            </div>
        </div>

    </div>


@stop