<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CheckSnippetUrl extends Request
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
    public function rules()
    {

        $rules = [

            'url' => 'snippet_allowed_url',

        ];



        return $rules;
    }


    public function messages()
    {
        return [
            'url.snippet_allowed_url' => 'invalid_url'

        ];


    }
}
