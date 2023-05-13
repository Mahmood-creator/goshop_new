<?php

namespace App\Http\Requests\Rest\Region;

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
    public function rules()
    {
        return [
            'perPage' => 'required|integer',
            'lang' => 'required|string',
            'search' => 'nullable|string|max:255',
            'country_id' => 'required|integer|exists:countries,id'
        ];
    }
}
