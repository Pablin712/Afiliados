<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogPostRequest;
use App\Http\Requests\Blog\UpdateBlogPostRequest;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BlogPostsController extends Controller
{
    /**
     * Writing screen for admins and for the users the admin gave the writer
     * role to. A writer only sees their own entries; an admin sees them all.
     */
    public function index(): View
    {
        $user = request()->user();

        Gate::authorize('viewAny', BlogPost::class);

        $blogPosts = BlogPost::query()
            ->with('author')
            ->unless($user->can('edit landing_content'), fn ($query) => $query->where('author_id', $user->id))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.blog.index', compact('blogPosts'));
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        Gate::authorize('create', BlogPost::class);

        $payload = $this->payloadFrom($request);
        $payload['slug'] = BlogPost::uniqueSlug($request->input('slug') ?: $request->input('title_es'));
        $payload['author_id'] = $request->user()->id;

        if ($request->hasFile('cover')) {
            $payload['cover_image_path'] = $request->file('cover')->store('blog', 'public');
        }

        BlogPost::query()->create($payload);

        return back()->with('status', __('messages.admin.blog.messages.created'));
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        Gate::authorize('update', $blogPost);

        $payload = $this->payloadFrom($request);
        $payload['slug'] = BlogPost::uniqueSlug(
            $request->input('slug') ?: $request->input('title_es'),
            $blogPost->id
        );

        if ($request->hasFile('cover')) {
            $this->deleteFile($blogPost->cover_image_path);
            $payload['cover_image_path'] = $request->file('cover')->store('blog', 'public');
        }

        $blogPost->update($payload);

        return back()->with('status', __('messages.admin.blog.messages.updated'));
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        Gate::authorize('delete', $blogPost);

        $this->deleteFile($blogPost->cover_image_path);

        $blogPost->delete();

        return back()->with('status', __('messages.admin.blog.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFrom(StoreBlogPostRequest|UpdateBlogPostRequest $request): array
    {
        return [
            'title_es' => $request->input('title_es'),
            'title_en' => $request->input('title_en'),
            'excerpt_es' => $request->input('excerpt_es'),
            'excerpt_en' => $request->input('excerpt_en'),
            'body_es' => $request->input('body_es'),
            'body_en' => $request->input('body_en'),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->input('published_at') ?: now(),
            'sort_order' => (int) ($request->input('sort_order') ?? 0),
        ];
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
