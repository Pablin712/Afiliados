<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use App\Models\CourseVideo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $modules = CourseModule::query()
            ->where('is_active', true)
            ->with([
                'videos' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (CourseModule $module) => $module->videos->isNotEmpty())
            ->values();

        $selectedModule = $modules->firstWhere('slug', (string) $request->query('module'))
            ?? $modules->first();

        $selectedVideo = $selectedModule?->videos->firstWhere('slug', (string) $request->query('video'));

        if (! $selectedVideo && $selectedModule) {
            $selectedVideo = $selectedModule->videos->first();
        }

        if (! $selectedModule && $selectedVideo) {
            $selectedModule = $selectedVideo->module;
        }

        $videosCount = $modules->sum(fn (CourseModule $module) => $module->videos->count());

        return view('courses.index', [
            'modules' => $modules,
            'selectedModule' => $selectedModule,
            'selectedVideo' => $selectedVideo,
            'videosCount' => $videosCount,
        ]);
    }

    public function stream(Request $request, CourseVideo $video): BinaryFileResponse
    {
        $user = $request->user();

        if (! $video->is_active && ! $user?->hasRole('admin')) {
            abort(404);
        }

        if (! Storage::disk($video->disk)->exists($video->file_path)) {
            abort(404);
        }

        $path = Storage::disk($video->disk)->path($video->file_path);
        $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', $video->slug).'.mp4';

        return response()->file($path, [
            'Content-Type' => $video->mime_type ?: 'video/mp4',
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
