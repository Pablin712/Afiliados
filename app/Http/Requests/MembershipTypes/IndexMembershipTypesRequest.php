<?php

namespace App\Http\Requests\MembershipTypes;

use Illuminate\Foundation\Http\FormRequest;

class IndexMembershipTypesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view membership_types') ?? false;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort_by' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'ajax' => ['nullable', 'boolean'],
        ];
    }
}
