<?php

namespace App\Http\Requests;

use App\Models\Shop;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopStatusChangeRequest extends FormRequest
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
             'status' => ['required', 'string', Rule::in(Shop::STATUS)],
         ];
     }

}
