<?php

namespace App\Http\Requests\MembershipTypes;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create membership_types') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:membership_types,name'],
            'affiliates_required' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'profit' => ['required', 'numeric', 'min:0'],
        ];
    }
}
