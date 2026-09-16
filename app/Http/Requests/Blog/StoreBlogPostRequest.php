<?php

namespace App\Http\Requests\Blog;

class StoreBlogPostRequest extends BlogPostRequest
{
    protected function ignoredPostId(): ?int
    {
        return null;
    }
}
