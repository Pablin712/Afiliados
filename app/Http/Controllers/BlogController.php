<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\LandingContent;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::query()
            ->published()
            ->with('author')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        return view('blog.index', [
            'posts' => $posts,
            'content' => $this->landingContent(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()
            ->published()
            ->with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = BlogPost::query()
            ->published()
            ->whereKeyNot($post->id)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('blog.show', [
            'post' => $post,
            'related' => $related,
            'content' => $this->landingContent(),
        ]);
    }

    /**
     * The blog header/footer reuse the landing page's editable contact data,
     * so the same admin screen keeps both in sync.
     *
     * @return array<string, string>
     */
    private function landingContent(): array
    {
        $defaults = collect(__('messages'))
            ->filter(fn ($value, $key) => str_starts_with($key, 'welcome.'))
            ->mapWithKeys(fn ($value, $key) => [substr($key, strlen('welcome.')) => $value])
            ->all();

        return array_merge($defaults, LandingContent::mapFor(app()->getLocale()));
    }
}
