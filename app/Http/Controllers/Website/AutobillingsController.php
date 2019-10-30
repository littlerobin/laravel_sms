<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Download;
use Auth;


class AutobillingsController extends WebsiteController
{

    /**
     * Create a new instance of CampaignsController class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.headers');
        $this->middleware('jwt.auth',['except' =>[
                'getCheckPreapproval'
            ]
        ]);
        $this->activityLogRepo = new \App\Services\ActivityLogService();

    }

	/**
     * Send request to server for recharging users balance.
     * POST /billings/activate-automatic-billing
     * 
     * @param Request $request
     * @return JSON
     */
    public function postActivateAutomaticBilling(Request $request)
    {
    	$user = Auth::user();
        $PayPalConfig = config('paypal');
        $PayPal = new \angelleye\PayPal\Adaptive($PayPalConfig);
        $token = str_random(40);

        Download::create(['user_id' => $user->_id, 'token' => $token]);

        $appUrl = config('app.url');
        $PreapprovalFields = [
            'MaxTotalAmountOfAllPayments' => 1200,
            'CancelURL' => $appUrl . '/myaccount#/account/financials',
            'CurrencyCode' => 'EUR',
            'IPNNotificationURL' => $appUrl,
            'ReturnURL' => $appUrl . '/autobillings/check-preapproval?auth_token=' . $token,
            'StartingDate' => date('Y-m-d'),
            'EndingDate' => date('Y-m-d',strtotime('now + 8 month')), 
        ];
        $PayPalRequestData = [ 'PreapprovalFields' => $PreapprovalFields ];
        $PayPalResult = $PayPal->Preapproval($PayPalRequestData);
        if($PayPalResult['Ack'] == 'Success'){
            //dd($PayPalResult);
            $user->paypal_preapproval_key = $PayPalResult['PreapprovalKey'];
            $user->save();
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => ''
                ],
                'redirect_url' => $PayPalResult['RedirectURL']
            ];
        } else{
            //dd($PayPalResult);
            \Log::info($PayPalResult);
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'error__occured_1'
                ]
            ];
        }
        return response()->json(['resource' => $response]);
    }

    /**
     * Check the status of preapproval
     * GET /autobillings/check-preapproval
     * 
     * @param Request $request
     * @return Redirect
     */
    public function getCheckPreapproval(Request $request)
    {
        $token = $request->get('auth_token');
        $tokenObject = Download::where('token', $token)->first();
        if(!$tokenObject || !$tokenObject->user){
            return redirect()->to('/myaccount#/account/financials');
        }
        $user = $tokenObject->user;
        Download::where('token', $token)->delete();
        
        $PayPalConfig = config('paypal');
        $PayPal = new \angelleye\PayPal\Adaptive($PayPalConfig);

        $PreapprovalDetailsFields = [
                                  'GetBillingAddress' => '',
                                  'PreapprovalKey' => $user->paypal_preapproval_key
                                  ];
        $PayPalRequestData = [ 'PreapprovalDetailsFields' => $PreapprovalDetailsFields ];
        $PayPalResult = $PayPal->PreapprovalDetails($PayPalRequestData);
        //dd($PayPalResult);
        if($PayPalResult['Ack'] == 'Success' && $PayPalResult['Status'] == 'ACTIVE'){
            $status = $PayPalResult['Status'];
            $user->autobilling_amount = 5;
            $user->is_autobilling_active = true;
            $user->save();

            $logData = [
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'BILLINGS',
                'description' => 'User activated autobilling'
            ];
            $this->activityLogRepo->createActivityLog($logData);
        }
        return redirect()->to('/myaccount#/account/financials');

    }


    /**
     * Send request to server for cancelling automatic billing.
     * POST /autobillings/cancel-automatic-billing
     * 
     * @param Request $request
     * @return JSON
     */
    public function postCancelAutomaticBilling(Request $request){
    	$user = Auth::user();
        $preapprovalKey = $user->paypal_preapproval_key;
        $PayPalConfig = config('paypal');
        $PayPal = new \angelleye\PayPal\Adaptive($PayPalConfig);
        $PayPalRequestData = ['CancelPreapprovalFields' => ['PreapprovalKey' => $preapprovalKey] ];
        $PayPalResult = $PayPal->CancelPreapproval($PayPalRequestData);
        $user->paypal_preapproval_key = null;
		$user->autobilling_amount = null;
		$user->is_autobilling_active = 0;
		$user->save();
		$logData = [
			'user_id' => $user->_id,
			'device' => 'WEBSITE',
			'action' => 'BILLINGS',
			'description' => 'User cancelled autobilling'
		];
		$this->activityLogRepo->createActivityLog($logData);
        $response = $this->createBasicResponse(0, 'canceled__1');
        return response()->json(['resource' => $response]);
    }


    /**
     * Send request for setting autobilling amount price
     * POST /billings/set-auto-recharge-amount
     *
     * @param Request $request
     * @return JSON
     */
    public function postSetAutoRechargeAmount(Request $request)
    {
        $user = Auth::user();
        $autorechargeWith = $request->get('autorecharge_with');
        $availableValues = [25, 50, 100];
		if(!in_array($autorechargeWith, $availableValues)){
			$response = $this->createBasicResponse(-1, 'amount_should_be_5__25_or_50');
			return response()->json(['resource' => $response]);
		}
		$user->autobilling_amount = $autorechargeWith;
		$user->save();
		$response = [
			'error' => [
				'no' => 0,
				'text' => 'Activated__1'
			]
		];
		return response()->json(['resource' => $response]);
    }

    /**
     * Send request to server for recharging users balance.
     * POST /billings/switch-automatic-billing
     * 
     * @param Request $request
     * @return JSON
     */
    public function postSwitchAutomaticBilling(Request $request)
    {
        $user = Auth::user();
        $status = $request->status;
        
        if ($status === 1) {
            $user->is_autobilling_active = 1;
            $user->autobilling_amount = $user->autobilling_amount === 0 ? 25 : $user->autobilling_amount;
        } elseif ($status === 0) {
            $user->is_autobilling_active = 0;
        } else {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'something_went_wrong'
                ]
            ];

            return response()->json(['resource' => $response]);
        }
        $user->save();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'successfully_updated',
                'vars' => 'stat: ' . $status  // !DEBUGGING! should be deleted for production
            ]
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request to server for updating auto-recharge amount.
     * POST /billings/update-automatic-billing-amount
     * 
     * @param Request $request
     * @return JSON
     */
    public function postUpdateAutomaticBillingAmount(Request $request)
    {
        $user = Auth::user();
        $amount = $request->autobilling_amount;
        if ($user->is_autobilling_active === 1) {
            $user->autobilling_amount = $amount;
            $user->save();
        } else {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'autobilling_is_not_active'
                ]
            ];

            return response()->json(['resource' => $response]);
        }
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'successfully_updated',
                'vars' => 'amount: ' . $amount // !DEBUGGING! should be deleted for production
            ]
        ];

        return response()->json(['resource' => $response]);
    }
}
