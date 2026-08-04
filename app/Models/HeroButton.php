<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroButton extends Model
{
    protected $fillable = [
        'label_es',
        'label_en',
        'url',
        'style',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    /**
     * Tailwind class presets matching the site's original 4 hero buttons,
     * so the admin can pick a look without needing a full style editor.
     *
     * @return array<string, string>
     */
    public static function styles(): array
    {
        return [
            'primary' => 'inline-flex items-center px-5 py-3 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500',
            'secondary' => 'inline-flex items-center px-5 py-3 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400',
            'dark' => 'inline-flex items-center px-5 py-3 rounded-md bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 dark:bg-brand-500 dark:hover:bg-brand-400',
            'accent' => 'inline-flex items-center px-5 py-3 rounded-md border border-sky-300 bg-sky-50 text-sm font-semibold text-sky-800 hover:border-sky-400 hover:bg-sky-100 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:border-sky-400 dark:hover:bg-sky-500/20',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function styleLabels(): array
    {
        return [
            'primary' => 'Azul sólido',
            'secondary' => 'Gris con borde',
            'dark' => 'Oscuro sólido',
            'accent' => 'Celeste con borde',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function localized(string $field): string
    {
        $locale = app()->getLocale();

        return $this->{"{$field}_{$locale}"} ?: $this->{"{$field}_es"};
    }

    public function styleClasses(): string
    {
        return self::styles()[$this->style] ?? self::styles()['primary'];
    }

    /**
     * Anchors (#section) and relative paths stay in the same tab; only
     * full external URLs open in a new one.
     */
    public function opensExternally(): bool
    {
        return str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://');
    }
}
