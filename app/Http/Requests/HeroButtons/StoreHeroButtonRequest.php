<?php

namespace App\Http\Requests\HeroButtons;

use App\Models\HeroButton;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHeroButtonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit landing_content') ?? false;
    }

    public function rules(): array
    {
        return [
            'label_es' => ['required', 'string', 'max:100'],
            'label_en' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:500'],
            'style' => ['required', Rule::in(array_keys(HeroButton::styles()))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
