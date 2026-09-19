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
        ];
    }


}
