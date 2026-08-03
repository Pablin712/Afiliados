<?php

namespace App\Http\Requests\Testimonials;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit landing_content') ?? false;
    }

    public function rules(): array
    {
        return [
            'name_es' => ['required', 'string', 'max:150'],
            'name_en' => ['required', 'string', 'max:150'],
            'quote_es' => ['required', 'string', 'max:2000'],
            'quote_en' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
