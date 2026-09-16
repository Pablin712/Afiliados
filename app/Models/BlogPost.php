<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'slug',
        'title_es',
        'title_en',
        'excerpt_es',
        'excerpt_en',
        'body_es',
        'body_en',
        'cover_image_path',
        'is_published',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(BlogPostPrice::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    /**
     * English copy is optional, so fall back to the Spanish one.
     */
    public function localized(string $field): ?string
    {
        $locale = app()->getLocale();

        return $this->{"{$field}_{$locale}"} ?: $this->{"{$field}_es"};
    }

    /**
     * Short text for the cards on /blog: the admin's excerpt when there is
     * one, otherwise the opening of the body with the markdown stripped out.
     */
    public function summary(int $limit = 180): string
    {
        $excerpt = $this->localized('excerpt');

        if (filled($excerpt)) {
            return Str::limit($excerpt, $limit);
        }

        $plain = preg_replace('/[#>*_`\[\]()!-]+/', ' ', (string) $this->localized('body'));

        return Str::limit(trim(preg_replace('/\s+/', ' ', $plain)), $limit);
    }

    /**
     * The body is written in markdown by the admin; HTML typed inside it is
     * escaped so a content editor can never inject scripts into the page.
     */
    public function bodyHtml(): string
    {
        return Str::markdown((string) $this->localized('body'), [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Prices grouped by region, keeping the admin's ordering within each one.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, BlogPostPrice>>
     */
    public function pricesByRegion(): \Illuminate\Support\Collection
    {
        return $this->prices->groupBy('region');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'articulo';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
