<?php

namespace App\Http\Requests\Testimonials;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
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
            'media_type' => ['required', 'in:image,video'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'video' => ['nullable', 'file', 'mimes:mp4,mov,webm', 'max:51200'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $mediaType = $this->input('media_type', 'image');

            if ($mediaType === 'image' && ! $this->hasFile('photo')) {
                $validator->errors()->add('photo', __('validation.required', ['attribute' => __('validation.attributes.photo')]));
            }

            if ($mediaType === 'video' && ! $this->hasFile('video')) {
                $validator->errors()->add('video', __('validation.required', ['attribute' => __('validation.attributes.video')]));
            }
        });
    }
}
