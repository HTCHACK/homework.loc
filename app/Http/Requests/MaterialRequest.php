<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
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

            'name'=>'required|min:3|unique:materials',

            'material_code'=>'required|numeric',

            'price'=>'required|numeric|min:0|not_in:0'
            
        ];
    }
}
