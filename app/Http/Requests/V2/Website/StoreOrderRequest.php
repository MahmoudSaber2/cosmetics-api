<?php

namespace App\Http\Requests\V2\Website;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            // Client information
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'sometimes|string|max:100',

            // Order information
            'note' => 'sometimes|string|max:1000',

            // Order items
            'orderItems' => 'required|array|min:1',
            'orderItems.*.productId' => 'required|integer|exists:products,id',
            'orderItems.*.quantity' => 'required|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.max' => 'الاسم لا يمكن أن يتجاوز 255 حرف',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.max' => 'البريد الإلكتروني لا يمكن أن يتجاوز 255 حرف',

            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.max' => 'رقم الهاتف لا يمكن أن يتجاوز 20 حرف',

            'address.required' => 'العنوان مطلوب',
            'address.max' => 'العنوان لا يمكن أن يتجاوز 500 حرف',

            'city.max' => 'المدينة لا يمكن أن تتجاوز 100 حرف',

            'note.max' => 'الملاحظة لا يمكن أن تتجاوز 1000 حرف',

            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة منتج واحد على الأقل',
            'items.*.productId.required' => 'معرف المنتج مطلوب',
            'items.*.productId.exists' => 'المنتج المحدد غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
            'items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل',
            'items.*.quantity.max' => 'الكمية لا يمكن أن تتجاوز 100',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'address' => 'العنوان',
            'city' => 'المدينة',
            'note' => 'الملاحظة',
            'items' => 'المنتجات',
            'items.*.productId' => 'معرف المنتج',
            'items.*.quantity' => 'الكمية',
        ];
    }
}
