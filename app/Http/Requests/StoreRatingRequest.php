<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware and controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_id' => [
                'required',
                'integer',
                'exists:bookings,id',
            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'review_text' => [
                'nullable',
                'string',
                'max:500',
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
            'booking_id.required' => 'Booking ID is required',
            'booking_id.exists' => 'The selected booking does not exist',
            'rating.required' => 'Rating is required',
            'rating.integer' => 'Rating must be a number',
            'rating.min' => 'Rating must be at least 1 star',
            'rating.max' => 'Rating cannot exceed 5 stars',
            'review_text.max' => 'Review text cannot exceed 500 characters',
        ];
    }
}

