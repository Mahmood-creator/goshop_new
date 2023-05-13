<?php

namespace App\Http\Requests\Rest\Country;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'perPage' => 'required|integer',
            'lang' => 'required|string',
            'search' => 'nullable|string|max:255'
        ];
    }
}
