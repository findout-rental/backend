<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
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
        $today = now()->format('Y-m-d');
        
        return [
            'check_in_date' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:' . $today,
            ],
            'check_out_date' => [
                'sometimes',
                'required',
                'date',
                'after:check_in_date',
            ],
            'number_of_guests' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:20',
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
            'check_in_date.required' => 'Check-in date is required',
            'check_in_date.date' => 'Check-in date must be a valid date',
            'check_in_date.after_or_equal' => 'Check-in date must be today or in the future',
            'check_out_date.required' => 'Check-out date is required',
            'check_out_date.date' => 'Check-out date must be a valid date',
            'check_out_date.after' => 'Check-out date must be after check-in date',
            'number_of_guests.integer' => 'Number of guests must be a number',
            'number_of_guests.min' => 'Number of guests must be at least 1',
            'number_of_guests.max' => 'Number of guests cannot exceed 20',
        ];
    }
}

