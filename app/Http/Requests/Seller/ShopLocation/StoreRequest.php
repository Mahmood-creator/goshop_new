<?php

namespace App\Http\Requests\Seller\ShopLocation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'country_id' => 'nullable|integer|exists:countries,id',
            'region_id' => 'nullable|integer|exists:regions,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'delivery_fee' => 'nullable|double',
            'pickup' => 'required|boolean',
            'delivery' => 'required|boolean',
        ];
    }
}
