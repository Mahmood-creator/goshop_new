<?php

namespace App\Http\Requests\User;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class AddressStoreRequest extends FormRequest
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
            'title' => 'required|string|max:191',
            'location' => 'nullable|string',
            'address' => 'required|string|min:10|max:200|regex:/^[A-Za-z0-9 -_]+/',
        ];
    }
}
