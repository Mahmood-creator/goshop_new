<?php

namespace App\Http\Requests\Rest\City;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'perPage'   => 'required|integer',
            'lang'      => 'required|string',
            'search'    => 'nullable|string|max:255',
            'region_id' => 'nullable|integer|exists:regions,id',
            'status'    => 'nullable|boolean'
        ];
    }
}
