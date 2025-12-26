<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApartmentRequest extends FormRequest
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
            'photos' => [
                'sometimes',
                'array',
                'min:1',
                'max:10',
            ],
            'photos.*' => [
                'required_with:photos',
                'string',
            ],
            'governorate' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'governorate_ar' => [
                'nullable',
                'string',
                'max:100',
            ],
            'city' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'city_ar' => [
                'nullable',
                'string',
                'max:100',
            ],
            'address' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'address_ar' => [
                'nullable',
                'string',
                'min:10',
                'max:500',
            ],
            'nightly_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
            ],
            'monthly_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
            ],
            'bedrooms' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:10',
            ],
            'bathrooms' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'living_rooms' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:5',
            ],
            'size' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'min:50',
                'max:2000',
            ],
            'description_ar' => [
                'nullable',
                'string',
                'min:50',
                'max:2000',
            ],
            'amenities' => [
                'nullable',
                'array',
            ],
            'amenities.*' => [
                'string',
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['active', 'inactive']),
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
            'photos.min' => 'At least one photo is required',
            'photos.max' => 'Maximum 10 photos allowed',
            'governorate.required' => 'Governorate is required',
            'city.required' => 'City is required',
            'address.required' => 'Address is required',
            'address.min' => 'Address must be at least 10 characters',
            'address.max' => 'Address must not exceed 500 characters',
            'nightly_price.required' => 'Nightly price is required',
            'nightly_price.min' => 'Nightly price must be greater than 0',
            'monthly_price.required' => 'Monthly price is required',
            'monthly_price.min' => 'Monthly price must be greater than 0',
            'bedrooms.required' => 'Number of bedrooms is required',
            'bedrooms.min' => 'Bedrooms must be at least 0',
            'bedrooms.max' => 'Bedrooms must not exceed 10',
            'bathrooms.required' => 'Number of bathrooms is required',
            'bathrooms.min' => 'Bathrooms must be at least 1',
            'bathrooms.max' => 'Bathrooms must not exceed 10',
            'living_rooms.required' => 'Number of living rooms is required',
            'living_rooms.min' => 'Living rooms must be at least 0',
            'living_rooms.max' => 'Living rooms must not exceed 5',
            'size.required' => 'Apartment size is required',
            'size.min' => 'Size must be greater than 0',
            'description.required' => 'Description is required',
            'description.min' => 'Description must be at least 50 characters',
            'description.max' => 'Description must not exceed 2000 characters',
        ];
    }
}
