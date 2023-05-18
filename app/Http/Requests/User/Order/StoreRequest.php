<?php

namespace App\Http\Requests\User\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'price' => 'required|double',
            'currency_id' => 'required|integer|exists:currencies,id',
            'rate' => 'required|double',
            'note' => 'nullable',
            'total_delivery_fee' => 'required|double',
            'tax' => 'nullable|double',
            'user_address_id' => 'required|integer|exists:user_addresses,id',
            'deliveryman_id' => 'nullable|integer|exists:deliveries,id',
            'products' => 'required|array',
            'products.*.stock_id' => 'required|integer|exists:stocks,id',
            'products.*.origin_price' => 'required|double',
            'products.*.total_price' => 'required|double',
            'products.*.tax' => 'required|double',
            'products.*.discount' => 'required|double',
            'products.*.quantity' => 'required|array',
        ];
    }
}
