<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $accountId = trim((string) $this->input('binance_account_id', ''));
        $username = trim((string) $this->input('binance_username', ''));

        $this->merge([
            'binance_account_id' => $accountId === '' ? null : $accountId,
            'binance_username' => $username === '' ? null : $username,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userBankId = $this->user()?->userBanks()->value('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'binance_account_id' => [
                'nullable',
                'string',
                'max:80',
                'required_with:binance_username',
                Rule::unique('user_banks', 'identification')->ignore($userBankId),
            ],
            'binance_username' => ['nullable', 'string', 'max:150', 'required_with:binance_account_id'],
        ];
    }
}
