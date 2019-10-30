<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateSnippetRequest extends Request
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

        $allowedDateTimes =  $request->allowed_date_times;

        $id = $request->get('_id');

        $rules = [

            'callerIds' => 'required',
            'countries' => 'required',
            'wait_time' => 'required',
            'snippetName' => 'required',
            'allowed_url' => 'snippet_allowed_url',
            'subdomain' => 'min:8|unique:snippets,subdomain,' . $id . ',_id',


        ];

        if(! count($allowedDateTimes['weekDays'])) {
            $rules['weekDays'] = 'required';
        }

        if(!array_key_exists('dateRangeEnd', $allowedDateTimes) or !array_key_exists('dateRangeStart', $allowedDateTimes)) {
            $rules['dateRange'] = 'required';
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
