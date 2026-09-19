<?php

namespace App\Http\Requests\Programs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Rules shared by creating and updating a plan, including its optional table
 * of prices per region.
 */
class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'first_payment_cost' => ['required', 'numeric', 'min:0'],
            'card_first_payment_cost' => ['nullable', 'numeric', 'min:0'],
            'renewal_cost' => ['required', 'numeric', 'min:0'],
            'card_renewal_cost' => ['nullable', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
            'is_active' => ['nullable', 'boolean'],
            'region_prices' => ['nullable', 'array', 'max:40'],
            'region_prices.*.region' => ['required', 'string', 'max:120'],
            'region_prices.*.first_payment_cost' => ['required', 'numeric', 'min:0'],
            'region_prices.*.card_first_payment_cost' => ['nullable', 'numeric', 'min:0'],
            'region_prices.*.renewal_cost' => ['required', 'numeric', 'min:0'],
            'region_prices.*.card_renewal_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'region_prices.*.region' => __('messages.plans.region_prices.region'),
            'region_prices.*.first_payment_cost' => __('messages.plans.region_prices.first_payment_cost'),
            'region_prices.*.renewal_cost' => __('messages.plans.region_prices.renewal_cost'),
        ];
    }

    /**
     * The editor keeps a blank row at the end so the admin can add regions;
     * untouched rows are dropped instead of failing validation. Two rows with
     * the same region name would collide on the unique index, so the last one
     * typed wins.
     */
    protected function prepareForValidation(): void
    {
        $rows = $this->input('region_prices');

        if (! is_array($rows)) {
            return;
        }

        $cleaned = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);

            $isEmpty = blank($row['region'] ?? null)
                && blank($row['first_payment_cost'] ?? null)
                && blank($row['renewal_cost'] ?? null)
                && blank($row['card_first_payment_cost'] ?? null)
                && blank($row['card_renewal_cost'] ?? null);

            if ($isEmpty) {
                continue;
            }

            foreach (['first_payment_cost', 'card_first_payment_cost', 'renewal_cost', 'card_renewal_cost'] as $field) {
                $value = $row[$field] ?? null;
                $row[$field] = blank($value) ? null : str_replace(',', '.', (string) $value);
            }

            $cleaned[mb_strtolower((string) ($row['region'] ?? ''))] = $row;
        }

        $this->merge(['region_prices' => array_values($cleaned)]);
    }
}
