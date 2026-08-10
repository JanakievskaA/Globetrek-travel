<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_country' => ['nullable', 'string', 'max:80'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'travel_time' => ['nullable', 'string', 'max:20'],
            'adults' => ['required', 'integer', 'min:1', 'max:40'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'extras' => ['nullable', 'array'],
            'extras.*' => ['string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'travel_date.after_or_equal' => 'Please choose a departure date in the future.',
        ];
    }
}
