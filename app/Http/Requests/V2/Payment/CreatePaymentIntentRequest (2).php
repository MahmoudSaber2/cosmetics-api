<?php

namespace App\Http\Requests\V2\Payment;

use App\Enums\PaymentMethodEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class CreatePaymentIntentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'currency' => 'nullable|string|size:3|in:usd,eur,gbp',
            'payment_method' => ['nullable', new Enum(PaymentMethodEnum::class)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'currency.size' => 'العملة يجب أن تكون 3 أحرف',
            'currency.in' => 'العملة يجب أن تكون إحدى القيم: usd, eur, gbp',
            'payment_method' => 'طريقة الدفع غير صحيحة',
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
}
