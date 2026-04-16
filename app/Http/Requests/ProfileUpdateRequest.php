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
        $phone = $this->normalizePhone((string) $this->input('phone', ''));

        $this->merge([
            'binance_account_id' => $accountId === '' ? null : $accountId,
            'binance_username' => $username === '' ? null : $username,
            'phone' => $phone === '' ? null : $phone,
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
            'phone' => [
                'required',
                'string',
                'regex:/^\+593\d{9}$/',
                'max:30',
                Rule::unique(User::class, 'phone')->ignore($this->user()->id),
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

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '593')) {
            return '+'.$digits;
        }

        return $digits;
    }
}
