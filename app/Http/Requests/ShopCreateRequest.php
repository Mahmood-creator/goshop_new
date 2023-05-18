<?php

namespace App\Http\Requests;

use App\Models\Shop;
use App\Traits\ApiResponse;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShopCreateRequest extends FormRequest
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
            'open_time' => ['required', 'string'],
            'close_time' => ['required', 'string'],
            'status' => ['string', Rule::in(Shop::STATUS)],
            'active' => ['numeric', Rule::in(1,0)],

            "title"    => ['required', 'array'],
            "title.*"  => ['required', 'string', 'max:199'],
            "description"  => ['array'],
            "description.*"  => ['string'],
            "address"    => ['required', 'array'],
            "address.*"  => ['string'],
        ];
    }

}
