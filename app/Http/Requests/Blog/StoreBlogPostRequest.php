<?php

namespace App\Http\Requests\Blog;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Gate;

class StoreBlogPostRequest extends BlogPostRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', BlogPost::class);
    }

    protected function ignoredPostId(): ?int
    {
        return null;
    }
}
