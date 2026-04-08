<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class CourseVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'title',
        'slug',
        'description',
        'disk',
        'file_path',
        'mime_type',
        'file_size',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'course_module_id' => 'integer',
            'file_size' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function streamUrl(): string
    {
        return Route::has('courses.videos.stream')
            ? route('courses.videos.stream', $this)
            : '';
    }
}
