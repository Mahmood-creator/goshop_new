<?php

namespace App\Http\Requests\User;

use App\Helpers\ResponseError;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class AddressUpdateRequest extends FormRequest
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
            'title' => 'required|string|max:191',
            'location' => 'nullable|string',
            'address' => 'required|string|min:10|max:200|regex:/^[A-Za-z0-9 -_]+/',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'min' => trans('validation.min.numeric', [], request()->lang),
            'max' => trans('validation.max.numeric', [], request()->lang),
            'regex' => trans('validation.regex', [], request()->lang),
            'string' => trans('validation.string', [], request()->lang),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $response = $this->requestErrorResponse(
            ResponseError::ERROR_400,
            trans('errors.' . ResponseError::ERROR_400, [], request()->lang),
            $errors->messages(), Response::HTTP_BAD_REQUEST);

        throw new HttpResponseException($response);

    }
}
