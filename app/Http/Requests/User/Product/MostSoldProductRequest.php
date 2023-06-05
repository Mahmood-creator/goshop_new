<?php

namespace App\Http\Requests\User\Product;

use Illuminate\Foundation\Http\FormRequest;

class MostSoldProductRequest extends FormRequest
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
            'perPage' => 'required|integer',
            'lang' => 'required|string',
            'currency_id' => 'required|integer|exists:currencies,id',
            'user_address_id' => 'nullable|integer|exists:user_addresses,id'
        ];
    }
}
