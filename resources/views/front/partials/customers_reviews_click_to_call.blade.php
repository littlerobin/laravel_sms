<div class="holder customers">
    <div ng-init="toggle3 = true" ng-click="toggle3 = !toggle3" data-toggle="collapse" href="#clients" aria-expanded="false" aria-controls="clients" class="toggler d-flex flex-row align-items-center pl-2 pr-2 pl-lg-4 pr-lg-4 pt-3 pb-3 pointer">
        <div class="triangle" ng-class="{toggled:toggle3}"></div>
        <h3 class="ml-2 mb-0">{{trans('main.crud.clients_feed')}}</h3>
    </div>
    <div class="collapse show pt-4 pb-4 pl-2 pr-2 pl-md-4 pr-md-4 slick_feature_area" id="clients">
        <slick autoplay="true" autoplay-speed="7000" ng-if="toggle3" slides-to-show=1 class="slick_services" settings="servicesSettings">
            <div class="customer text-center">
                <h2>{{trans('main.vm.judith_nguyen')}}</h2>
                <h3>{{trans('main.vm.estate_agency')}}</h3>
                <p>{{trans('main.vm.i_didnt_believe_it_could_be_so_easy_to_receive_calls_from_people_interested_to_our_advertisements')}}</p>
                <img src="{{asset('/laravel_assets/images/front/img/nguyen.svg')}}" width="99" height="99" alt="">
            </div>
            <div class="customer text-center">
                <h2>{{trans('main.vm.paul_ward')}}</h2>
                <h3>{{trans('main.vm.lawyer')}}</h3>
                <p>{{trans('main.vm.click_to_call_is_the_cheaper_and_efficient_service_to_convert_your_website_visitors_into_customers')}}</p>
                <img src="{{asset('/laravel_assets/images/front/img/paul.svg')}}" width="99" height="99" alt="">
            </div>
            <div class="customer text-center">
                <h2>{{trans('main.vm.tiffany_mendez')}}</h2>
                <h3>{{trans('main.vm.catering')}}</h3>
                <p>{{trans('main.vm.my_customers_are_happy_to_receive_a_free_support_whenever_they_need_i_don_t_know')}}</p>
                <img src="{{asset('/laravel_assets/images/front/img/tiffany.svg')}}" width="99" height="99" alt="">
            </div>
        </slick>
    </div>
</div>