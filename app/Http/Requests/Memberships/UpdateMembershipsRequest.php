<?php

namespace App\Http\Requests\Memberships;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit memberships') ?? false;
    }

    public function rules(): array
    {
        return [
            // TODO: Define update validation rules for Memberships.
        ];
    }
}