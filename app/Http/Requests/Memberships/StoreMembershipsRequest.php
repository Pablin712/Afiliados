<?php

namespace App\Http\Requests\Memberships;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create memberships') ?? false;
    }

    public function rules(): array
    {
        return [
            // TODO: Define create validation rules for Memberships.
        ];
    }
}