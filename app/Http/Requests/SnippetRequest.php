<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SnippetRequest extends Request
{


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(\Illuminate\Http\Request $request)
    {

        $user = \Auth::user();

        $allowedDateTimes =  $request->allowed_date_times;
        $customDateTimes =  $request->custom_date_times;
        $hasCustomDateTime = $request->get('has_custom_date_times');
        //$hasAllowedDateTime = $request->get('has_allowed_date_times');


        $id = $request->get('_id');

        $rules = [

            'callerIds' => 'required_and_exist_for_user',
            'countries' => 'required',
            'wait_time' => 'required',
            'snippetName' => 'required',
            'allowed_url' => 'snippet_allowed_url',


        ];

        if($id) {
            $rules['subdomain'] = 'unique:snippets,subdomain,' . $id . ',_id';
        } else {

            $rules['subdomain'] = 'unique:snippets,subdomain';
        }

        if($hasCustomDateTime) {


            if(!$request->get('custom_date_times')) {
                $rules['weekDays'] = 'required';
            }

        } else {

            if(! count($allowedDateTimes['weekDays'])) {
                $rules['weekDays'] = 'required';
            }

            if(!array_key_exists('dateRangeEnd', $allowedDateTimes) or !$allowedDateTimes['dateRangeEnd'] or !array_key_exists('dateRangeStart', $allowedDateTimes) or !$allowedDateTimes['dateRangeStart'] ) {

                $rules['dateRange'] = 'required';
            }
        }


        return $rules;
    }


    public function messages()
    {
        return [

            'callerIds.required' => 'snippet_please_choose_caller_ids',
            'countries.required' => 'snippet_please_choose_countries',
            'wait_time.required' => 'snippet_please_choose_wait_time',
            'weekDays.required' => 'snippet_please_choose_weekdays',
            'dateRange.required' => 'snippet_please_choose_date_range',
            'snippetName.required' => 'snippet_the_name_is_required',
            'allowed_url.required' => 'allowed_url_is_required',
        ];


    }
}
