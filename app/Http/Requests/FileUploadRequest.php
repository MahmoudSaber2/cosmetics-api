<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
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
        $maxSize = env('MAX_UPLOAD_SIZE', 2048);
        $allowedTypes = str_replace(',', '|', env('ALLOWED_IMAGE_TYPES', 'jpeg,jpg,png,gif,webp'));

        return [
            'file' => [
                'required',
                'file',
                'image',
                "max:{$maxSize}",
                "mimes:{$allowedTypes}",
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ],
            'directory' => 'sometimes|string|max:255'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        $maxSize = env('MAX_UPLOAD_SIZE', 2048);
        $allowedTypes = env('ALLOWED_IMAGE_TYPES', 'jpeg,jpg,png,gif,webp');

        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.image' => 'The file must be an image.',
            'file.max' => "The file size must not exceed {$maxSize}KB.",
            'file.mimes' => "The file must be one of the following types: {$allowedTypes}.",
            'file.dimensions' => 'The image dimensions must be between 100x100 and 4000x4000 pixels.',
        ];
    }
}
