<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'for_free',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'for_free' => 'boolean',
        ];
    }

    public function videos(): HasMany
    {
        return $this->hasMany(CourseVideo::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
