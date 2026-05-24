<?php

namespace App\Http\Requests\V1\Order;

use App\Enums\DiscountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_order');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'clientId' => ['required_if:name,null', 'exists:clients,id'],
            'name' => ['nullable', 'required_without:clientId', 'string', 'max:255'],
            'email' => [
                'nullable',
                'required_without:clientId',
                'email',
                'max:255',
                Rule::unique('clients', 'email'),
            ],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]+$/', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],

            'note' => ['nullable', 'string'],
            'status' => ['required', new Enum(OrderStatusEnum::class)],
            'orderItems' => ['required', 'array', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discountType' => ['nullable', Rule::enum(DiscountTypeEnum::class)],
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }
}
