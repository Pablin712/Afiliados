<?php

namespace App\Http\Requests\Profits;

use Illuminate\Foundation\Http\FormRequest;

class IndexProfitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view profits') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'state' => ['nullable', 'in:pending,made'],
            'search' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'sort_by' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'ajax' => ['nullable', 'boolean'],
        ];
    }
}
