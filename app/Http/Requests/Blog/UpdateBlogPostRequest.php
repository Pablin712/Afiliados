<?php

namespace App\Http\Requests\Blog;

class UpdateBlogPostRequest extends BlogPostRequest
{
    protected function ignoredPostId(): ?int
    {
        return $this->route('blogPost')?->id;
    }
}
