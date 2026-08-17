<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonials\StoreTestimonialRequest;
use App\Http\Requests\Testimonials\UpdateTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestimonialsController extends Controller
{
    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? 0;
        $payload['is_active'] = $request->boolean('is_active', true);
        unset($payload['photo'], $payload['video']);

        if ($payload['media_type'] === 'video') {
            $payload['video_path'] = $request->file('video')->store('testimonials', 'public');
            $payload['photo_path'] = null;
        } else {
            $payload['photo_path'] = $request->file('photo')->store('testimonials', 'public');
            $payload['video_path'] = null;
        }

        Testimonial::query()->create($payload);

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.testimonials.messages.created'));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? $testimonial->sort_order;
        $payload['is_active'] = $request->boolean('is_active', true);
        unset($payload['photo'], $payload['video']);

        if ($payload['media_type'] === 'video') {
            $payload['video_path'] = $request->hasFile('video')
                ? $this->replaceFile($testimonial->video_path, $request->file('video'))
                : $testimonial->video_path;

            $this->deleteFile($testimonial->photo_path);
            $payload['photo_path'] = null;
        } else {
            $payload['photo_path'] = $request->hasFile('photo')
                ? $this->replaceFile($testimonial->photo_path, $request->file('photo'))
                : $testimonial->photo_path;

            $this->deleteFile($testimonial->video_path);
            $payload['video_path'] = null;
        }

        $testimonial->update($payload);

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.testimonials.messages.updated'));
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->deleteFile($testimonial->photo_path);
        $this->deleteFile($testimonial->video_path);

        $testimonial->delete();

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.testimonials.messages.deleted'));
    }

    private function replaceFile(?string $existingPath, UploadedFile $file): string
    {
        $this->deleteFile($existingPath);

        return $file->store('testimonials', 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
