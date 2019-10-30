@extends('front.layouts.app')
@section('content')
@include('front.price_label')
<div class="toTop" ng-click="scrollToTop()">
    <div class="arrow-up"></div>
</div>
<div id="main-content" ng-controller="TermsAndConditionsPrivacyPolicyController" class="fixedMenuContentSections mt-4">
    <div class="container">
        <div class="row">
            @include('front.partials.register_contact_us')
            @include('front.partials.map')
        </div>
        <div class="row text-center">
            <ul class="nav w-100 nav-tabs d-flex justify-content-center align-items-center" role="tablist">
                <li role="presentation" class="nav-item">
                    <a href="#terms" ng-class="termsActiveNav" class="nav-link" data-toggle="tab">{{trans('main.snippet.terms__conditions')}}</a>
                </li>
                <li role="presentation" class="nav-item">
                    <a href="#privacy" ng-class="privacyActiveNav" class="nav-link" data-toggle="tab">{{trans('main.welcome.privacy_policy')}}</a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade" ng-class="termsActive" id="terms">
                    @include('front.partials.privacy.'. $locale . '.terms')
                </div>
                <div role="tabpanel" class="tab-pane fade" id="privacy" ng-class="privacyActive">
                    @include('front.partials.privacy.'. $locale . '.privacy')
                </div>
            </div>
        </div>
    </div>
</div>

@include('front.partials.modals.contact_us_documentation_modal')

@stop