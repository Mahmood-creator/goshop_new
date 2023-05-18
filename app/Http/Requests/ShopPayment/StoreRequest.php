<?php

namespace App\Http\Requests\ShopPayment;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    use ApiResponse;
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
            'payment_id' => 'required|integer|exists:payments,id',
            'status' => 'required|boolean',
            'client_id' => 'nullable|string',
            'secret_id' => 'nullable|string',
        ];
    }
}
