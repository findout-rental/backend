<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'mobile_number' => [
                'required',
                'string',
                'regex:/^(\+9639\d{8}|09\d{8})$/',
            ],
            'password' => [
                'required',
                'string',
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
            'mobile_number.required' => 'Mobile number is required',
            'mobile_number.regex' => 'Please enter a valid Syrian mobile number (e.g., +963991877688 or 0991877688)',
            'password.required' => 'Password is required',
        ];
    }
}
