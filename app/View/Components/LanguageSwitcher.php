<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\App;

class LanguageSwitcher extends Component
{
    public $currentLocale;
    public $availableLocales;
    public $localeNames;

    public function __construct()
    {
        $this->currentLocale = App::getLocale();
        $this->availableLocales = config('locale.available_locales', ['en', 'es']);
        $this->localeNames = config('locale.locale_names', [
            'en' => 'English',
            'es' => 'Español',
        ]);
    }

    public function render(): View|Closure|string
    {
        return view('components.language-switcher');
    }
}
