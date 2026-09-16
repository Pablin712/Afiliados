<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared rules for creating and updating a blog entry. The only difference
 * between both forms is the entry being ignored by the unique slug check.
 */
abstract class BlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit landing_content') ?? false;
    }

    abstract protected function ignoredPostId(): ?int;

    public function rules(): array
    {
        return [
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('blog_posts', 'slug')->ignore($this->ignoredPostId()),
            ],
            'title_es' => ['required', 'string', 'max:200'],
            'title_en' => ['nullable', 'string', 'max:200'],
            'excerpt_es' => ['nullable', 'string', 'max:500'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'body_es' => ['required', 'string', 'max:40000'],
            'body_en' => ['nullable', 'string', 'max:40000'],
            'cover' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'prices' => ['nullable', 'array', 'max:60'],
            'prices.*.region' => ['required', 'string', 'max:120'],
            'prices.*.item' => ['nullable', 'string', 'max:150'],
            'prices.*.price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'prices.*.currency' => ['required', 'string', 'size:3', 'alpha'],
            'prices.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'prices.*.region' => __('messages.admin.blog.columns.region'),
            'prices.*.price' => __('messages.admin.blog.columns.price'),
            'prices.*.currency' => __('messages.admin.blog.columns.currency'),
        ];
    }

    /**
     * The price editor always renders an empty row so the admin can keep
     * adding regions; rows left untouched are dropped instead of failing
     * validation.
     */
    protected function prepareForValidation(): void
    {
        $rows = $this->input('prices');

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
                && blank($row['item'] ?? null)
                && blank($row['price'] ?? null)
                && blank($row['note'] ?? null);

            if ($isEmpty) {
                continue;
            }

            $row['currency'] = strtoupper($row['currency'] ?? 'USD') ?: 'USD';
            $row['price'] = str_replace(',', '.', (string) ($row['price'] ?? ''));

            $cleaned[] = $row;
        }

        $this->merge(['prices' => $cleaned]);
    }
}
