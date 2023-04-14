<?php

namespace App\Http\Requests;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Response;

class CategoryCreateRequest extends FormRequest
{
    use ApiResponse;

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
            'keywords' => ['string'],
            'parent_id' => ['required','numeric'],
            'type' => ['required', Rule::in(Category::TYPES)],
            'active' => ['numeric', Rule::in(1,0)],
            'images' => ['array'],
            'images.*' => ['string'],
            'title'    => ['required', 'array'],
            'title.*'  => ['required', 'string', 'min:2', 'max:255'],
            'description'  => ['array'],
            'description.*'  => ['string', 'min:2'],
            'weight' => ['required','numeric'],
            'product_type_id' => ['required','integer']
        ];
    }

    public function messages()
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'numeric' => trans('validation.numeric', [], request()->lang),
            'min' => trans('validation.min.numeric', [], request()->lang),
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
