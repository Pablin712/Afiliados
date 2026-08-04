<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HeroButtons\StoreHeroButtonRequest;
use App\Http\Requests\HeroButtons\UpdateHeroButtonRequest;
use App\Models\HeroButton;
use Illuminate\Http\RedirectResponse;

class HeroButtonsController extends Controller
{
    public function store(StoreHeroButtonRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? 0;
        $payload['is_active'] = $request->boolean('is_active', true);

        HeroButton::query()->create($payload);

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.hero_buttons.messages.created'));
    }

    public function update(UpdateHeroButtonRequest $request, HeroButton $heroButton): RedirectResponse
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? $heroButton->sort_order;
        $payload['is_active'] = $request->boolean('is_active', true);

        $heroButton->update($payload);

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.hero_buttons.messages.updated'));
    }

    public function destroy(HeroButton $heroButton): RedirectResponse
    {
        $heroButton->delete();

        return redirect()
            ->route('admin.landing-content.index')
            ->with('status', __('messages.admin.hero_buttons.messages.deleted'));
    }
}
