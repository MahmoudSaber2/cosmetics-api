<?php

namespace App\Http\Requests\V1\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-users');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]+$/', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', new Enum(StatusEnum::class)],
            'roleId' => ['required']
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name must not exceed 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'phone.regex' => 'The phone number format is invalid.',
            'phone.max' => 'The phone number must not exceed 20 characters.',
            'address.max' => 'The address must not exceed 500 characters.',
            'status.in' => 'The status must be either 0 (inactive) or 1 (active).',
            'roles.required' => 'At least one role must be assigned.',
            'roles.array' => 'The roles must be provided as an array.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
