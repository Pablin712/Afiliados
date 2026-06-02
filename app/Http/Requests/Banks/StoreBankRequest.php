<?php

namespace App\Http\Requests\Banks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create banks') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'owner' => ['required', 'string', 'max:150'],
            'identification' => ['required', 'string', 'max:50'],
            'number' => ['required', 'string', 'max:80', Rule::unique('banks', 'number')],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'detail' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
