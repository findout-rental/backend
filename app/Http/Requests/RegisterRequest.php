<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
                'unique:users,mobile_number',
            ],
            'otp_code' => [
                'required',
                'string',
                'size:6',
                'regex:/^\d{6}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['tenant', 'owner']),
            ],
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'personal_photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
            ],
            'id_photo' => [
                'required',
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
            'mobile_number.required' => 'Mobile number is required',
            'mobile_number.unique' => 'This mobile number is already registered',
            'mobile_number.regex' => 'Please enter a valid Syrian mobile number (e.g., +963991877688 or 0991877688)',
            'otp_code.required' => 'OTP code is required',
            'otp_code.size' => 'OTP code must be 6 digits',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either tenant or owner',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'personal_photo.required' => 'Personal photo is required',
            'personal_photo.image' => 'Personal photo must be an image',
            'personal_photo.max' => 'Personal photo must not exceed 5MB',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be in the past',
            'id_photo.required' => 'ID photo is required',
            'id_photo.image' => 'ID photo must be an image',
            'id_photo.max' => 'ID photo must not exceed 5MB',
        ];
    }
}
