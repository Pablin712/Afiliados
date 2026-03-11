<?php

namespace App\Http\Requests\MembershipTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit membership_types') ?? false;
    }

    public function rules(): array
    {
        $membershipTypeId = $this->route('membershipType')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('membership_types', 'name')->ignore($membershipTypeId),
            ],
            'affiliates_required' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'profit' => ['required', 'numeric', 'min:0'],
        ];
    }
}
