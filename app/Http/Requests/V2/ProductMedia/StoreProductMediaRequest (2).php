<?php

namespace App\Http\Requests\V2\ProductMedia;

use App\Enums\IsMainEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class StoreProductMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'media' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,svg,mp4,avi,mov,wmv,flv,webm',
                'max:5120' // 5MB in KB
            ],
            'isMain' => ['nullable', new Enum(IsMainEnum::class)]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'media.required' => 'يجب اختيار ملف',
            'media.file' => 'يجب أن يكون العنصر ملف',
            'media.mimes' => 'يجب أن يكون الملف من نوع: jpg, jpeg, png, gif, webp, svg, mp4, avi, mov, wmv, flv, webm',
            'media.max' => 'يجب أن يكون حجم الملف أقل من 5 ميجابايت',
            'isMain' => 'يجب أن يكون isMain قيمة صحيحة (0 أو 1)'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Configure the validator instance.
     */

}
