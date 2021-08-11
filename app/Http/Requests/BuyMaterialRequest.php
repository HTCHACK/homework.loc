<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyMaterialRequest extends FormRequest
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
        return [

            'counter_agency_id'=>[ Rule::exists('counter_agencies','id')],

            'date'=>[

                'required' 

            ],

            'items.*.material_id'=>'required|distinct|exists:materials,id',

            'items.*.price'=>[ Rule::exists('materials','price')],

            'items.*.quantity'=>'required|numeric|min:0|not_in:0',
            
        ];
    }


}
