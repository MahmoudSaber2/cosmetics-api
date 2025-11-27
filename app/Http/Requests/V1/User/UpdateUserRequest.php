<?php

namespace App\Http\Requests\V1\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update_user');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => ['nullable', 'sometimes', 'string', 'min:8'],
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
    // public function messages(): array
    // {
    //     return [
    //         'name.max' => 'The name must not exceed 255 characters.',
    //         'email.email' => 'Please provide a valid email address.',
    //         'email.unique' => 'This email address is already registered.',
    //         'email.max' => 'The email must not exceed 255 characters.',
    //         'password.min' => 'The password must be at least 8 characters.',
    //         'password.confirmed' => 'The password confirmation does not match.',
    //         'phone.regex' => 'The phone number format is invalid.',
    //         'phone.max' => 'The phone number must not exceed 20 characters.',
    //         'address.max' => 'The address must not exceed 500 characters.',
    //         'roles.array' => 'The roles must be provided as an array.',
    //         'roles.*.exists' => 'One or more selected roles do not exist.',
    //     ];
    // }
}
