<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SnippetAudioUpload extends Request
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

        $rules = [

            'file' => 'max:100',

        ];


        return $rules;
    }


    public function messages()
    {
        return [

            //'file.max' => '',

        ];


    }
}
