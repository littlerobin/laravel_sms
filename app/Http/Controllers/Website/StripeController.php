<?php

namespace App\Http\Controllers\Website;

use App\Services\BillingService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\StripeService;
use App\Services\InvoiceService;
use App\Services\SlackNotificationService;
use Auth;
use Validator;
use DB;

class StripeController extends WebsiteController
{
	/**
	 * Create a new instance of StripeController class
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->stripeRepo = new StripeService();
	}

	/**
	 * Get all cards of the user .
	 * We will use our internal database for this, without making request to Stripe
	 *
	 * @return JSON
	 */
	public function getCards()
	{
		$user = Auth::user();
		// $user = \App\User::find(38);
		if($user->stripe_customer_id){
			//$cards = $this->stripeRepo->getAllCards($user->stripe_customer_id);
			$cards = $this->stripeRepo->getAllLocalCards($user);
		} else{
			$cards = [];
		}
		$response = [
            'error' => [
                'no' => 0,
                'text' => 'cards_list'
            ],
            'cards' => $cards
        ];
        return response()->json(['resource' => $response]);
	}

	/**
	 * Make request to Stripe to create a new Card and store data
	 *
	 * @param Request $request
	 * @return JSON
	 */
	public function postCreateCard(Request $request)
	{
		$user = Auth::user();
		$cardData = $request->only(['name', 'number', 'exp_year', 'exp_month', 'cvc']);
		$validator = Validator::make(
		    $cardData,
		    [
		        'name' => 'required',
		        'number' => 'required',
		        'exp_month' => 'required',
		        'exp_year' => 'required',
		        'cvc' => 'required'
		    ]
		);
		if ($validator->fails()) {
			$response = $this->createBasicResponse(-1, 'please_fill_required_fields');
        	return response()->json(['resource' => $response]);
		}
		if (!$user->stripe_customer_id) {
			$user->stripe_customer_id = $this->stripeRepo->createCustomer($user);
			$user->save();
		}
		try {
			$createdCard = $this->stripeRepo->createCard($user->stripe_customer_id, $cardData);
			$this->stripeRepo->syncStripeWithLocal($user);
		} catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {
			$response = $this->createBasicResponse(-2, 'card_details_are_wrong');
        	return response()->json(['resource' => $response]);
		} catch (\Exception $e) {
			\Log::info($e);
			$response = $this->createBasicResponse(-3, 'something_went_wrong');
        	return response()->json(['resource' => $response]);
		}
		$response = $this->createBasicResponse(0, 'card_added');
        return response()->json(['resource' => $response]);
 	}

 	/**
 	 * Remove credit card from the customer
 	 * Then check if there is no any other card , then disable auto billing
 	 *
 	 * @return JSON
 	 */
 	public function deleteRemoveCard($id)
 	{
 		$user = Auth::user();
 		if(!$user->stripe_customer_id) {
 			$response = $this->createBasicResponse(-1, 'you_dont_have_card');
        	return response()->json(['resource' => $response]);
 		}
 		try {
 			$removedCard = $this->stripeRepo->deleteCard($user->stripe_customer_id, $id);
			$this->stripeRepo->syncStripeWithLocal($user);
 		} catch (\Exception $e) {
 			\Log::error($e);
 			$response = $this->createBasicResponse(-3, 'something_went_wrong');
        	return response()->json(['resource' => $response]);
 		}
		$cards = $this->stripeRepo->getAllCards($user->stripe_customer_id);
		if(isset($cards['data']) && count($cards['data']) == 0){
			$user->is_autobilling_active = 0;
			$user->save();
		}
 		$response = $this->createBasicResponse(0, 'card_deleted_successfully');
        return response()->json(['resource' => $response]);
 	}

 	/**
 	 * Make card as default for the customer
 	 *
 	 * @param Request $request
 	 * @return JSON
 	 */
 	public function postMakeCardDefault(Request $request)
 	{
 		$user = Auth::user();
 		$cardId = $request->get('card_id');
 		if (!$user->stripe_customer_id) {
			$response = $this->createBasicResponse(-2, 'customer_not_created');
            return response()->json(['resource' => $response]);
		}
 		try {
 			$action = $this->stripeRepo->markCardAsDefault($user->stripe_customer_id, $cardId);
			$this->stripeRepo->markLocalCardAsDefault($user, $cardId);
	
 		} catch (\Exception $e) {
 			\Log::error($e);
 			$response = $this->createBasicResponse(-3, 'something_went_wrong');
        	return response()->json(['resource' => $response]);
 		}
		$response = $this->createBasicResponse(0, 'card_marked_as_default');
        return response()->json(['resource' => $response]);
 	}

 	/**
 	 * Make a purchase, using stripe
 	 *
 	 * @param Request $request
 	 * @return JSON
 	 */
 	public function postMakePayment(Request $request)
 	{
 		$cardId = $request->get('card_id');
 		$isApplePay = $request->get('is_apple_pay', false);
        $orderId = $request->get('order_id');
 		$invoiceRepo = new InvoiceService();
 		$user = Auth::user();

 		if (!$user->stripe_customer_id && !$isApplePay) {
			$response = $this->createBasicResponse(-2, 'customer_not_created');
            return response()->json(['resource' => $response]);
		}
        $amount = $request->get('amount');
        $vatId = $user->vat;
        $couponCode = $request->get('discount_code');

        if( !$user->country_code )
        {
            $response = $this->createBasicResponse(-1, 'name_address_postal_code_caller_id_and_city_are_mandatory');
            return response()->json(['resource' => $response]);
        }

        $ordersCount = $user->invoices()->where('is_paid', 0)->count();
        if($ordersCount > 0 && !$orderId) {
            $response = $this->createBasicResponse(-3, 'you_already_have_unpaid_order');
            return response()->json(['resource' => $response]);
        }
        if($orderId){
            $invoice = $user->invoices()->where('is_paid', 0)->where('_id', $orderId)->first();
            if(!$invoice) {
                $response = $this->createBasicResponse(-4, 'order_id_not_right');
                return response()->json(['resource' => $response]);
            }
        } else {
        	$invoice = $invoiceRepo->createOrder($user, $amount, $vatId, $couponCode);
        }

        $billingRepo = new BillingService();
        $response = $billingRepo->checkVatId($vatId,$user->country_code);

        try {
            if($response['error']['no'] != 0) {
                $chargeAmount = $invoice->purchased_amount + $invoice->vat_amount;
            } else {
                $chargeAmount = $invoice->purchased_amount;
            }
            $stripeResponse = $this->stripeRepo->makeRecharge($user, $chargeAmount, $cardId, $isApplePay);
        } catch (\Exception $e) {
        	$user->localCards()->where('stripe_id', $cardId)->increment('fails_count');
        	\Log::error($e);
        	$response = $this->createBasicResponse(-3, 'something__went__wrong');
        	return response()->json(['resource' => $response]);
        }
        $invoice = $invoiceRepo->finalizeOrder($user, $invoice, $stripeResponse, 'stripe');
        if(!$invoice) {
        	$response = $this->createBasicResponse(-4, 'partial_payment_contact_support');
        	return response()->json(['resource' => $response]);
        }
        try {
        	SlackNotificationService::notify('User with email - ' . $user->email . ' made payment with stripe in amount of - ' . $amount);
        } catch (\Exception $e) {
        	\Log::error($e);
        }

        $user->localCards()->where('stripe_id', $cardId)->update(['fails_count' => 0]);
        $user->is_campaign_stopped = 0;
        $user->save();
 		$response = $this->createBasicResponse(0, 'payment_completed');
        return response()->json(['resource' => $response]);
 	}

}