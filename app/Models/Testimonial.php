<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name_es',
        'name_en',
        'quote_es',
        'quote_en',
        'photo_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function localized(string $field): string
    {
        $locale = app()->getLocale();

        return $this->{"{$field}_{$locale}"} ?: $this->{"{$field}_es"};
    }
}
