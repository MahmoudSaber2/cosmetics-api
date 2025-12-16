<?php

namespace App\Http\Requests\V1\Product;

use App\Enums\ProductStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update_product');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'status' => ['required', new Enum(ProductStatusEnum::class)],
            'brandId' => ['nullable', 'exists:brands,id'],
            'categoryId' => ['nullable', 'exists:categories,id'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'minStock' => ['nullable', 'integer', 'min:0'],
            'hasStock' => ['required', 'boolean'],
            // Note: Media management is now handled separately through ProductMediaController
        ];


    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }
}
