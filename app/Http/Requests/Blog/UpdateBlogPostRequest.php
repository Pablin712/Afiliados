<?php

namespace App\Http\Requests\Blog;

use Illuminate\Support\Facades\Gate;

class UpdateBlogPostRequest extends BlogPostRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('blogPost'));
    }

    protected function ignoredPostId(): ?int
    {
        return $this->route('blogPost')?->id;
    }
}
