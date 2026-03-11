<?php

namespace App\Http\Requests\Memberships;

use Illuminate\Foundation\Http\FormRequest;

class IndexMembershipsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view memberships') ?? false;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort_by' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'status' => ['nullable', 'in:active,free,expired,pending_payment'],
            'membership_type_id' => ['nullable', 'integer', 'min:1'],
            'ajax' => ['nullable', 'boolean'],
            'export' => ['nullable', 'in:csv,excel,json,pdf'],
        ];
    }
}
