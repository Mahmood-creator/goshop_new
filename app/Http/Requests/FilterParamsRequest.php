<?php

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class FilterParamsRequest extends FormRequest
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
            "sort"    => ['string', Rule::in(['asc', 'desc'])],
            "column"    => ['string'],
            'status' => ['string'],
            'perPage' => ['numeric'],
            'shop_id' => ['numeric'],
            'user_id' => ['numeric'],
            'category_id' => ['numeric'],
            'brand_id' => ['numeric'],
            'price' => ['numeric'],
            'note' => ['string', 'max:255'],
        ];
    }
}
