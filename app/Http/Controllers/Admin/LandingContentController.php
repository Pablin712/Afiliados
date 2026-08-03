<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandingContentController extends Controller
{
    public function index(): View
    {
        $groupOrder = ['hero', 'programs', 'about', 'testimonials_section', 'contact', 'footer'];

        $rows = LandingContent::query()
            ->orderBy('sort_order')
            ->get();

        $fields = $rows
            ->groupBy('key')
            ->map(function ($rowsForKey) {
                $first = $rowsForKey->first();

                return (object) [
                    'key' => $first->key,
                    'type' => $first->type,
                    'group' => $first->group,
                    'sort_order' => $first->sort_order,
                    'es' => optional($rowsForKey->firstWhere('locale', 'es'))->value,
                    'en' => optional($rowsForKey->firstWhere('locale', 'en'))->value,
                    'all' => optional($rowsForKey->firstWhere('locale', LandingContent::LOCALE_ALL))->value,
                ];
            })
            ->groupBy('group')
            ->sortBy(fn ($fieldsInGroup, $group) => array_search($group, $groupOrder, true));

        $testimonials = Testimonial::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.landing-content.index', compact('fields', 'testimonials'));
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $field = LandingContent::query()->where('key', $key)->first();

        abort_if($field === null, 404);

        if ($field->type === LandingContent::TYPE_IMAGE) {
            return $this->updateImage($request, $key);
        }

        $hasAllRow = LandingContent::query()
            ->where('key', $key)
            ->where('locale', LandingContent::LOCALE_ALL)
            ->exists();

        if ($hasAllRow) {
            $validated = $request->validate([
                'value' => ['required', 'string', 'max:5000'],
            ]);

            LandingContent::query()
                ->where('key', $key)
                ->where('locale', LandingContent::LOCALE_ALL)
                ->update(['value' => $validated['value']]);
        } else {
            $validated = $request->validate([
                'value_es' => ['required', 'string', 'max:5000'],
                'value_en' => ['required', 'string', 'max:5000'],
            ]);

            LandingContent::query()->where('key', $key)->where('locale', 'es')->update(['value' => $validated['value_es']]);
            LandingContent::query()->where('key', $key)->where('locale', 'en')->update(['value' => $validated['value_en']]);
        }

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.landing_content.messages.updated'));
    }

    private function updateImage(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $current = LandingContent::query()
            ->where('key', $key)
            ->where('locale', LandingContent::LOCALE_ALL)
            ->first();

        if ($current && $current->value && Storage::disk('public')->exists($current->value)) {
            Storage::disk('public')->delete($current->value);
        }

        $path = $validated['image']->store('landing-content', 'public');

        LandingContent::query()
            ->where('key', $key)
            ->where('locale', LandingContent::LOCALE_ALL)
            ->update(['value' => $path]);

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.landing_content.messages.updated'));
    }
}
