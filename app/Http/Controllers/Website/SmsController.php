<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\SmsAction;
use App\Models\SmsResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function response(Request $request)
    {
        \Log::info($request);

	$response = SmsResponse::where('reference_id', $request->get('id'))
            ->where('destination_number', $request->get('receiver'))
            ->first();
        if (!$response) {
            $response = new SmsResponse();
            $response->reference_id = $request->get('id');
            $response->destination_number = $request->get('receiver');
        }
        $response->status_code = $request->get('status');
	$response->is_billed = 1;

        if ($request->get('submitdate')) {
            $response->send_on = $request->get('submitdate'); //Carbon::createFromTimestamp($request->get('submitdate'));
        }

        if ($request->get('donedate')) {
            $response->delivered_on = $request->get('donedate'); //Carbon::createFromTimestamp($request->get('donedate'));
        }

        if($request->get('status') == 1){
            $response->status_text = 'DELIVRD';
            $response->result_code = 1;
            $response->error_code = 1;
        }else if($request->get('status') == 2){
            $response->status_text = 'UNDELIV';
            $response->result_code = 2;
            $response->error_code = 2;
        }else if($request->get('status') == 4){
            $response->status_text = 'ACCEPTD';            
        }else{
            $response->status_text = 'UNKNOWN';
        } 
        
        $response->sender_name = $request->get('sender');
	$response->message_parts = $request->get('parts');
        $response->save();
    }
}
