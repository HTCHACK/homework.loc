<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            
            'name' => 'required',

            'selling_price' => 'required|numeric|min:0|not_in:0',

            'product_code' => 'required|numeric',

            'product_material.*.material_id' => 'required|distinct|exists:materials,id',

            'product_material.*.quantity' => 'required|numeric|min:0|not_in:0',

        ];
    }
}
