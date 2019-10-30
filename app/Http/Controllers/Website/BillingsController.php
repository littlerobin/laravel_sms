<?php

namespace App\Http\Controllers\Website;

use App\Models\Coupon;
use App\Services\BillingService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SendEmailService;
use App\Services\SlackNotificationService;
use App\Services\ActivityLogService;
use App\Services\InvoiceService;
use App\Models\Download;
use Carbon\Carbon;
use Auth;
use DB;

class BillingsController extends WebsiteController
{

	/**
	 * Create a new instance of InvoicesController class.
	 *
	 * @return void
	 */
	public function __construct()
	{

        $this->middleware('jwt.headers');
        $this->middleware('jwt.auth',['except' =>[
                'getDownloadInvoice',
                'getPaypalSuccess'
            ]
        ]);
        $this->sendEmailRepo = new SendEmailService();
        $this->invoiceRepo = new InvoiceService();
        $this->activityLogRepo = new ActivityLogService();

	}

    /**
     * Send request for getting users billings grouped by days
     * GET /billings/billings
     * 
     * @param Request $request
     * @return JSON
     */
    public function getBillings(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 0);

        //Prepare invoice query to UNION with billings
        $invoicesQuery = 
            \App\Models\Invoice::selectRaw( 'is_paid, user_id, DATE(invoice_date) as date, discount_amount as giftSum, purchased_amount as purchasedSum, invoice_date as created_at, type as billingType, description as billingDescription, vat_amount as taxAmount, current_balance_after_billing' )
                ->whereRaw('is_paid = 1 AND user_id = ' . $user->_id)
                ->whereRaw('(purchased_amount != 0 OR discount_amount != 0)');

        //Get billings, and union with invoices
        $billings = \App\Models\Billing::selectRaw( 'NULL as is_paid, user_id, DATE(created_at) as date, SUM(billed_from_gift) as giftSum, SUM(billed_from_purchased) as purchasedSum, created_at, "BILLING" as billingType, "" as billingDescription, "" as taxAmount, MIN(current_balance_after_billing) as current_balance_after_billing')
            ->whereRaw('user_id = ' . $user->_id)
            ->whereRaw('(billed_from_purchased != 0 OR billed_from_gift != 0)')
            ->orderBy('created_at', 'DESC')
            ->groupBy('billingType', 'date')
            ->unionAll($invoicesQuery)
            ->orderBy('created_at', 'DESC');
        //Get total count of unioned data
        $billingsCount =  \DB::select('SELECT COUNT(*) as totalCount FROM (' . $billings->toSql() . ') tempUnionTable');

        $billings = $billings
            ->skip($page * 10)
            ->take(10)
            ->get();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'billings'
            ],
            'billings' => $billings,
            'count' => isset( $billingsCount[0] ) ? $billingsCount[0]->totalCount: 0,
            'page' => $page + 1
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get billing details of the day
     * GET /billings/billing-details
     *
     * @param Request $request
     * @return JSON
     */
    public function getBillingDetails(Request $request)
    {
        $date = $request->get('date');
        $user = Auth::user();
        $snippetInfo = $user->billings()->leftJoin('phonenumbers', 'billings.phonenumber_id', '=', 'phonenumbers._id')
            ->leftJoin('snippets', 'snippets._id', '=', 'phonenumbers.snippet_id')
            ->selectRaw('DATE(billings.created_at), billings.created_at, billings._id as billingId, phonenumbers._id as phonenumberId, snippets._id as snippetId, SUM(billed_from_gift) as giftSum, SUM(billed_from_purchased) as purchasedSum, snippets.name')
            ->whereNotNull('phonenumbers.snippet_id')
            ->whereRaw('(billed_from_purchased != 0 OR billed_from_gift != 0)')
            ->whereRaw('DATE(billings.created_at) = "' . $date . '"')
            ->groupBy('snippetId')
            ->get();

        $messageInfo = $user->billings()->leftJoin('phonenumbers', 'billings.phonenumber_id', '=', 'phonenumbers._id')
            ->leftJoin('campaigns', 'phonenumbers.campaign_id', '=', 'campaigns._id')
            ->selectRaw('billings.created_at, billings._id as billingId, phonenumbers._id as phonenumberId, campaigns._id as campaignId, SUM(billed_from_gift) as giftSum, SUM(billed_from_purchased) as purchasedSum, campaigns.campaign_name')
            ->whereRaw('(billed_from_purchased != 0 OR billed_from_gift != 0)')
            ->whereNotNull('phonenumbers.campaign_id')
            ->whereRaw('DATE(billings.created_at) = "' . $date . '"')
            ->groupBy('campaignId')
            ->get();
        
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'billing_details'
            ],
            'billings' => [
                'snippets' => $snippetInfo,
                'messages' => $messageInfo
            ]
        ];
        return response()->json(['resource' => $response]);
    }


	/**
     * Send request for getting users invoices .
     * GET /billings/invoices
     * 
     * @param Request $request
     * @return JSON
     */
    public function getInvoices(Request $request)
    {   
        $user = Auth::user();
        $page = $request->get('invoices_page', 0);
        $perPage = $request->get('invoices_per_page', 5);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $invoices = $user->invoices()->where('is_paid', 1)->where('is_manual',0);
        if ($fromDate) {
            $invoices = $invoices->where('created_at', '>', $fromDate);
        }
        if ($toDate) {
            $invoices = $invoices->where('created_at', '<', $toDate);
        }
        $count = $invoices->count();
        //dd($invoices);

        $invoices = $invoices->skip($page * $perPage)->take($perPage)
            ->orderBy('_id', 'DESC')->get();

        // $localDateFormat = $user->local_date_format;

        // foreach ($invoices as $invoice) {
        //     $invoiceDate = $invoice->invoice_date;
        //     $localDateUnformated = Carbon::parse($invoiceDate);
        //     $localDate = $localDateUnformated->format($localDateFormat);
        //     $invoice->invoice_date = $localDate;
        // }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'invoices__1'
            ],
            'invoices' => $invoices,
            'count' => $count,
            'page' => $page + 1
        ];

        return response()->json(['resource' => $response]);
    }

    /**
	 * Send request for getting users invoices .
	 * GET /billings/orders
	 * 
	 * @param Request $request
	 * @return JSON
	 */
	public function getOrders(Request $request)
	{
		$user = Auth::user();
        $page = $request->get('invoices_page', 0);
        $perPage = $request->get('invoices_per_page', 5);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $invoices = $user->invoices()->where('is_paid', 0)->where('is_manual',0);
		if($fromDate){
			$invoices = $invoices->where('created_at', '>', $fromDate);
		}
		if($toDate){
			$invoices = $invoices->where('created_at', '<', $toDate);
		}
		$count = $invoices->count();

		$invoices = $invoices->skip($page * $perPage)->take($perPage)
            ->orderBy('_id', 'DESC')->get();

        // $localDateFormat = $user->local_date_format;

        // foreach ($invoices as $invoice) {
        //     $invoice->created_at_formated = $invoice->created_at->format($localDateFormat);
        // }

		$response = [
			'error' => [
				'no' => 0,
				'text' => 'invoices__1'
			],
			'invoices' => $invoices,
			'count' => $count,
			'page' => $page + 1
		];
		return response()->json(['resource' => $response]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteRemoveOrder($id)
    {
        $user = Auth::user();
        $order = $user->invoices()->where('is_paid', 0)->where('_id', $id)->first();
        
        if(!$order){
            $response = $this->createBasicResponse(-1, 'order_does_not_exists_or_not_belongs_to_you.');
            return response()->json(['resource' => $response]);
        }

        $order->delete();
        $response = $this->createBasicResponse(0, 'order_removed');
        return response()->json(['resource' => $response]);
    }


    /**
     * Send request for getting invoice by primary key
     * GET /billings/invoice/{id}
     *
     * @param integer $id
     * @return JSON
     */
    public function getInvoice($id, Request $request)
    {
        $user = Auth::user();
        $invoice = $user->invoices()->find($id);
		if(!$invoice){
			$response = $this->createBasicResponse(-1, 'invoice_does_not_exist_or_not_belongs__to_you');
			return response()->json(['resource' => $response]);
		}

        $invoiceDate = $invoice->invoice_date;
        $localDateFormat = $user->local_date_format ? $user->local_date_format: 'Y-m-d';
        $localDateUnformated = Carbon::parse($invoiceDate);
        $localDate = $localDateUnformated->format($localDateFormat);

        //$localOrderUnformatted = Carbon::parse($invoice->created_at);
        $localOrderDate = $invoice->created_at->format($localDateFormat);

        $invoice->invoice_date = $localDate;
        $invoice->order_date = $localOrderDate;

		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Invoice__data_1'
			],
			'invoice' => $invoice
		];
		return response()->json(['resource' => $response]);
    }


    /**
     * Download pdf of invoice file.
     * GET /billings/download-invoice
     * 
     * @param integer $id
     * @return JSON
     */
    public function getDownloadInvoice(Request $request)
    {

        $token = $request->get('token');
        $id = $request->get('id');
        $user = \App\Services\DownloadsService::user($token);
        if(!$user){
            return 'User missing';
        }

       	$invoice = $user->invoices()->find($id);

		if(!$invoice){
			$response = $this->createBasicResponse(-1, 'invoice_does_not_exist_or_not_belongs__to_you');
			return response()->json(['resource' => $response]);
		}

        $invoiceDate = $invoice->invoice_date;
        $localDateFormat = $user->local_date_format ? $user->local_date_format: 'Y-m-d';

        $localDateUnformated = Carbon::parse($invoiceDate);
        $localDate = $localDateUnformated->format($localDateFormat);

        //$localOrderUnformatted = Carbon::parse($invoice->created_at);
        $localOrderDate = $invoice->created_at->format($localDateFormat);

        $invoice->invoice_date = $localDate;
        $invoice->order_date = $localOrderDate;

        $pdf = \PDF::setPaper('a4')->loadView('pdf.invoice', ['invoice' => $invoice,'user' => $user]);
        return $pdf->download($invoice['invoice_number'] . '.pdf');
    }

    /**
     * Send request for checking vat id
     * POST /data/check-vat-id
     *
     * @param Request $request
     * @return JSON
     */
    public function postCheckVatId(Request $request)
    {
        $vatId = $request->get('vat_id');
        $user = \Auth::user();

        $billingRepo = new BillingService();
        $response = $billingRepo->checkVatId($vatId,$user->country_code);

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for checking if coupon code is valid.
     * POST /billings/check-coupon-code
     *
     * @param Request $request
     * @return JSON
     */
    public function postCheckCouponCode(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $couponCode = $request->get('discount_code');
        $coupon = $user->coupons()->where('is_used', 0)->whereRaw('lower(`code`) = ?',[$couponCode])->where('promotion_end','>',$now)->first();
        if(!$coupon){
            $coupon = Coupon::whereRaw('lower(`code`) = ?',[$couponCode])->where('type','UNLIMITED')->where('promotion_end','>',$now)->first();
            if(!$coupon) {
                $response = $this->createBasicResponse(-1, 'couponcode_is_not_valid');
                return response()->json(['resource' => $response]);
            }
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'coupon_is__valid'
            ],
            'coupon' => $coupon
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Create order for paying with bank
     * POST /billings/create-bank-order
     * 
     * @param Request $request
     * @return JSON
     */
    public function postCreateBankOrder(Request $request)
    {
        $user = Auth::user();
        $amount = $request->get('amount');
        //$vatId = $request->get('vat_id');
        $vatId = $user->vat;
        $couponCode = $request->get('discount_code');

        $ordersCount = $user->invoices()->where('is_paid', 0)->where('is_manual',0)->count();
        if($ordersCount > 0) {
            $response = $this->createBasicResponse(-3, 'you_already_have_unpaid_order');
            return response()->json(['resource' => $response]);
        }

        if( !$user->country_code )
        {
            $response = $this->createBasicResponse(-1, 'name_address_postal_code_caller_id_and_city_are_mandatory');
            return response()->json(['resource' => $response]);
        }

        $invoice = $this->invoiceRepo->createOrder($user, $amount, $vatId, $couponCode);

        $this->sendEmailRepo->sendPendingOrderEmail($user, $invoice);
        SlackNotificationService::notify('User with email - ' . $user->email . ' created bank order with sum - ' . $amount);

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'BILLINGS',
            'description' => 'User selected to pay with bank'
        ];
        $this->activityLogRepo->createActivityLog($logData);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'success_3'
            ],
            'invoice' => $invoice
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Make payment by PayPal
     * POST /billings/pay-by-paypal
     *
     * @param integer $id
     * @return JSON
     */
    public function postPayByPaypal(Request $request)
    {
        $user = Auth::user();
        $amount = $request->get('amount');
        //$vatId = $request->get('vat_id');
        $vatId = $user->vat;
        $couponCode = $request->get('discount_code');
        $orderId = $request->get('order_id');

        $ordersCount = $user->invoices()->where('is_paid', 0)->count();
        if ($ordersCount > 0 && !$orderId) {
            $response = $this->createBasicResponse(-3, 'you_already_have_unpaid_order');
            return response()->json(['resource' => $response]);
        }
        if ($orderId) {
            $invoice = $user->invoices()->where('is_paid', 0)->where('_id', $orderId)->first();
            if(!$invoice) {
                $response = $this->createBasicResponse(-4, 'order_id_not_right');
                return response()->json(['resource' => $response]);
            }
        } else {
            if( !$user->country_code )
            {
                $response = $this->createBasicResponse(-1, 'name_address_postal_code_caller_id_and_city_are_mandatory');
                return response()->json(['resource' => $response]);
            }
            $invoice = $this->invoiceRepo->createOrder($user, $amount, $vatId, $couponCode);
        }
        $PayPalConfig = config('paypal');
        $PayPal = new \angelleye\PayPal\PayPal($PayPalConfig);
        $appUrl = config('app.url');
        $token = str_random(40);
        Download::create(['user_id' => $user->_id, 'token' => $token]);
        $SECFields = array(
                    'returnurl' => $appUrl . '/billings/paypal-success/' . $invoice['_id'] . '?auth_token=' . $token,
                    'cancelurl' => $appUrl . '/',
                    //'solutiontype' => 'Sole',
                    'noshipping' => 1,
                    'landingpage' => 'Billing', 
                    'brandname' => 'Callburn Services SL',
                    );
        $Payments = array();
        $Payment = array(
                        'amt' => $invoice['total_amount'],
                        'currencycode' => 'EUR',
                        'desc' => 'Callburn Credit',
                        );
                        
        array_push($Payments, $Payment);

        $PayPalRequest = array(
                        'SECFields' => $SECFields, 
                        'Payments' => $Payments
                        );

        $paypalReponse = $PayPal -> SetExpressCheckout($PayPalRequest);
        if ($paypalReponse['ACK'] == 'Success') {
            $response = [
                'error' =>[
                    'no' => 0,
                    'text' => 'express_checkout_created'
                ],
                'token' => $paypalReponse['TOKEN'],
                'redirect_url' => $paypalReponse['REDIRECTURL']
            ];
            return response()->json(['resource' => $response]);
        } else{
            $response = [
                'error' =>[
                    'no' => -1,
                    'text' => 'something__went__wrong'
                ]
            ];
            return response()->json(['resource' => $response]);
        }
    }

    /**
     * Get Pay by paypal success
     * GET /billings/paypal-success
     *
     * @param integer $id
     * @param Request $request
     * @return Redirect
     */
    public function getPaypalSuccess($invoiceId, Request $request)
    {
        $token = $request->get('auth_token');
        $tokenObject = Download::where('token', $token)->first();
        if(!$tokenObject || !$tokenObject->user){
            echo 'token not exist - ' . $token;
            exit;
        }
        $user = $tokenObject->user;
        Download::where('token', $token)->delete();

        $invoice = $user->invoices()->where('is_paid', 0)->where('_id', $invoiceId)->first();
        if(!$invoice){
            $response = $this->createBasicResponse(-1, 'order_not_found');
        }
        $PayPalConfig = config('paypal');
        $PayPal = new \angelleye\PayPal\PayPal($PayPalConfig);
        $DECPFields = array(
                    'token' => $request->get('token'),
                    'payerid' => $request->get('PayerID')
                    );
        $Payments = array();
        $Payment = array(
                        'amt' => $invoice['total_amount'],
                        'currencycode' => $invoice['currency'],
                        'desc' => 'Callburn services',
                        );
                        
        array_push($Payments, $Payment);
        $PayPalRequest = array(
                       'DECPFields' => $DECPFields,
                       'Payments' => $Payments
                       );

        $PayPalResult =  $PayPal->DoExpressCheckoutPayment($PayPalRequest);
        \Log::info($PayPalResult);
        
        if(isset($PayPalResult['ACK']) && $PayPalResult['ACK'] != 'Success') {
            return redirect()->to('/myaccount#/account/financials?status=failed');
        }
        if( isset($PayPalResult['PAYMENTINFO_0_PAYMENTSTATUS']) && $PayPalResult['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed'){

            $invoiceRepo = new \App\Services\InvoiceService();
            $updateBalanceResponse = $invoiceRepo->finalizeOrder($user, $invoice, $PayPalResult, 'paypal');
                
            $user->is_campaign_stopped = 0;
            $user->save();
            
            if($updateBalanceResponse){
                SlackNotificationService::notify('Congrats!!!User with email - ' . $user->email . ' paid by paypal amount - ' . $PayPalResult['PAYMENTINFO_0_AMT']);
                return redirect()->to('/myaccount#/account/invoices?payment_amount=' . $invoice['total_amount']);
            }
            else{
                echo 'something wrong on creating invoice';
            }
        } else{
            \App\Models\FailedPayment::create([
                'user_id' => $user->_id,
                'type' => 'STANDARD',
                'data' => json_encode($PayPalResult)
            ]);
            echo 'something went wrong , please contact our support';
        }
    }

}