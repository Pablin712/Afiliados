<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonials\StoreTestimonialRequest;
use App\Http\Requests\Testimonials\UpdateTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class TestimonialsController extends Controller
{
    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['photo_path'] = $request->file('photo')->store('testimonials', 'public');
        $payload['sort_order'] = $payload['sort_order'] ?? 0;
        $payload['is_active'] = $request->boolean('is_active', true);
        unset($payload['photo']);

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
        unset($payload['photo']);

        if ($request->hasFile('photo')) {
            if ($testimonial->photo_path && Storage::disk('public')->exists($testimonial->photo_path)) {
                Storage::disk('public')->delete($testimonial->photo_path);
            }

            $payload['photo_path'] = $request->file('photo')->store('testimonials', 'public');
        }

        $testimonial->update($payload);

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.testimonials.messages.updated'));
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->photo_path && Storage::disk('public')->exists($testimonial->photo_path)) {
            Storage::disk('public')->delete($testimonial->photo_path);
        }

        $testimonial->delete();

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.testimonials.messages.deleted'));
    }
}
