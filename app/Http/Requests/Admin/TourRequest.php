<?php

namespace App\Http\Requests\Admin;

use App\Enums\TourStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        $tourId = $this->route('tour')?->id;

        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('tours', 'slug')->ignore($tourId)],
            'destination_id' => ['required', 'exists:destinations,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'summary' => ['required', 'string', 'max:400'],
            'description' => ['required', 'string'],
            'image' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'duration_days' => ['required', 'integer', 'min:0', 'max:365'],
            'duration_nights' => ['required', 'integer', 'min:0', 'max:365'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'group_size' => ['required', 'integer', 'min:1', 'max:200'],
            'min_age' => ['required', 'integer', 'min:0', 'max:99'],
            'difficulty' => ['required', Rule::in(['easy', 'moderate', 'challenging', 'extreme'])],
            'departure_point' => ['nullable', 'string', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::enum(TourStatus::class)],
            'is_featured' => ['boolean'],

            // Gallery rows from the repeater; a row without a photo is dropped.
            'images' => ['nullable', 'array', 'max:12'],
            'images.*.path' => ['nullable', 'string', 'max:255'],
            'images.*.alt' => ['nullable', 'string', 'max:180'],

            // Free-text list editors, one entry per line.
            'languages' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'excludes' => ['nullable', 'string'],
            'highlights' => ['nullable', 'string'],
            'amenities' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
            'duration_hours.required_without' => 'Give either a day count or an hour count.',
        ];
    }

    /** Turns the textarea list editors back into arrays for the JSON columns. */
    public function payload(): array
    {
        $toList = fn (?string $value) => collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return [
            ...$this->safe()->except(['languages', 'includes', 'excludes', 'highlights', 'amenities', 'images']),
            'languages' => $toList($this->input('languages')),
            'includes' => $toList($this->input('includes')),
            'excludes' => $toList($this->input('excludes')),
            'highlights' => $toList($this->input('highlights')),
            'amenities' => $toList($this->input('amenities')),
            'is_featured' => $this->boolean('is_featured'),
        ];
    }
}
