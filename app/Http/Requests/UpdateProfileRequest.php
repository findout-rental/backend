<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[\p{L}\s]+$/u', // Letters and spaces only
            ],
            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[\p{L}\s]+$/u', // Letters and spaces only
            ],
            'personal_photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'first_name.max' => 'First name must not exceed 100 characters',
            'first_name.regex' => 'First name must contain only letters and spaces',
            'last_name.required' => 'Last name is required',
            'last_name.max' => 'Last name must not exceed 100 characters',
            'last_name.regex' => 'Last name must contain only letters and spaces',
            'personal_photo.image' => 'Personal photo must be an image',
            'personal_photo.mimes' => 'Personal photo must be a JPEG or PNG file',
            'personal_photo.max' => 'Personal photo must not exceed 5MB',
        ];
    }
}
