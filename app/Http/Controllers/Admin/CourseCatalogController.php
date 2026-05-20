<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\CourseVideo;
use App\Services\CourseCatalogImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseCatalogController extends Controller
{
    public function __construct(private readonly CourseCatalogImportService $courseCatalogImportService)
    {
    }

    public function index(): View
    {
        $modules = CourseModule::query()
            ->with(['videos'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.courses.index', [
            'modules' => $modules,
            'videosCount' => $modules->sum(fn (CourseModule $module) => $module->videos->count()),
        ]);
    }

    public function storeModule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'for_free' => ['nullable', 'boolean'],
        ]);

        $slug = $this->uniqueModuleSlug($validated['name']);

        CourseModule::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'for_free' => $request->boolean('for_free', false),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.admin.courses.messages.module_created'));
    }

    public function toggleFree(CourseModule $module): RedirectResponse
    {
        $module->update(['for_free' => ! $module->for_free]);

        $messageKey = $module->for_free
            ? 'messages.admin.courses.messages.module_made_free'
            : 'messages.admin.courses.messages.module_made_members_only';

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __($messageKey));
    }

    public function destroyModule(CourseModule $module): RedirectResponse
    {
        if ($module->videos()->exists()) {
            return redirect()
                ->route('admin.courses.index')
                ->with('error', __('messages.admin.courses.messages.module_delete_blocked'));
        }

        $module->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.admin.courses.messages.module_deleted'));
    }

    public function storeVideo(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'course_module_id' => ['required', 'integer', 'exists:course_modules,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:1048576'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('video');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = Str::slug($validated['title']).'-'.now()->format('YmdHis').'.'.$extension;
        $path = $file->storeAs('course-videos', $filename, 'local');

        CourseVideo::query()->create([
            'course_module_id' => (int) $validated['course_module_id'],
            'title' => $validated['title'],
            'slug' => $this->uniqueVideoSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'disk' => 'local',
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => (int) $file->getSize(),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $message = __('messages.admin.courses.messages.video_created');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('admin.courses.index')
            ->with('status', $message);
    }

    public function destroyVideo(CourseVideo $video): RedirectResponse
    {
        if (Storage::disk($video->disk)->exists($video->file_path)) {
            Storage::disk($video->disk)->delete($video->file_path);
        }

        $video->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.admin.courses.messages.video_deleted'));
    }

    public function importExisting(): RedirectResponse
    {
        $imported = $this->courseCatalogImportService->importExistingVideos();

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.admin.courses.messages.imported', ['count' => $imported]));
    }

    protected function uniqueModuleSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (CourseModule::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function uniqueVideoSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (CourseVideo::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
