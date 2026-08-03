<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingContent extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_IMAGE = 'image';

    public const LOCALE_ALL = 'all';

    protected $fillable = [
        'key',
        'locale',
        'type',
        'group',
        'sort_order',
        'value',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @return array<string, string>
     */
    public static function mapFor(string $locale): array
    {
        return static::query()
            ->whereIn('locale', [$locale, self::LOCALE_ALL])
            ->pluck('value', 'key')
            ->all();
    }
}
