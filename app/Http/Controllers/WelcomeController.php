<?php

namespace App\Http\Controllers;

use App\Models\LandingContent;
use App\Models\Testimonial;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();

        $defaults = collect(__('messages'))
            ->filter(fn ($value, $key) => str_starts_with($key, 'welcome.'))
            ->mapWithKeys(fn ($value, $key) => [substr($key, strlen('welcome.')) => $value])
            ->all();

        $content = array_merge($defaults, LandingContent::mapFor($locale));

        $testimonials = Testimonial::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('welcome', compact('content', 'testimonials'));
    }
}
