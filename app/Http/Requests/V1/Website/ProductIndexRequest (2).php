<?php

namespace App\Http\Requests\V1\Website;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
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
            'search' => 'sometimes|string|max:255',
            'category' => 'sometimes|integer|exists:categories,id',
            'price_range' => 'sometimes|string|regex:/^\d+,\d+$/',
            'sort' => 'sometimes|string|in:latest,-latest,oldest,-oldest,price_low,price_high,-price_low,-price_high,name,-name',
            'perPage' => 'sometimes|integer|min:1|max:50',
            'page' => 'sometimes|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category.exists' => 'التصنيف المحدد غير موجود',
            'price_range.regex' => 'نطاق السعر يجب أن يكون بالصيغة: min,max',
            'sort.in' => 'نوع الترتيب غير صحيح',
            'perPage.max' => 'عدد العناصر في الصفحة لا يمكن أن يتجاوز 50',
        ];
    }
}
