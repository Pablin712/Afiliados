<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogPostRequest;
use App\Http\Requests\Blog\UpdateBlogPostRequest;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlogPostsController extends Controller
{
    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $payload = $this->payloadFrom($request);
        $payload['slug'] = BlogPost::uniqueSlug($request->input('slug') ?: $request->input('title_es'));

        if ($request->hasFile('cover')) {
            $payload['cover_image_path'] = $request->file('cover')->store('blog', 'public');
        }

        DB::transaction(function () use ($payload, $request): void {
            $post = BlogPost::query()->create($payload);
            $this->syncPrices($post, $request->input('prices', []));
        });

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.blog.messages.created'));
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $payload = $this->payloadFrom($request);
        $payload['slug'] = BlogPost::uniqueSlug(
            $request->input('slug') ?: $request->input('title_es'),
            $blogPost->id
        );

        if ($request->hasFile('cover')) {
            $this->deleteFile($blogPost->cover_image_path);
            $payload['cover_image_path'] = $request->file('cover')->store('blog', 'public');
        }

        DB::transaction(function () use ($blogPost, $payload, $request): void {
            $blogPost->update($payload);
            $this->syncPrices($blogPost, $request->input('prices', []));
        });

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.blog.messages.updated'));
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        abort_unless(request()->user()?->can('edit landing_content'), 403);

        $this->deleteFile($blogPost->cover_image_path);

        // Prices go with it through the cascading foreign key.
        $blogPost->delete();

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.blog.messages.deleted'));
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

    /**
     * The price editor posts the whole table on every save, so the stored rows
     * are replaced wholesale instead of diffed row by row.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncPrices(BlogPost $post, array $rows): void
    {
        $post->prices()->delete();

        foreach (array_values($rows) as $index => $row) {
            $post->prices()->create([
                'region' => $row['region'],
                'item' => $row['item'] ?? null,
                'price' => $row['price'],
                'currency' => $row['currency'],
                'note' => $row['note'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
