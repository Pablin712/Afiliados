<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'meeting_link',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public static function teacherColor(int $teacherId): string
    {
        $palette = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1',
        ];

        return $palette[$teacherId % count($palette)];
    }

    public function toCalendarEvent(): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'start'           => $this->start_time->toIso8601String(),
            'end'             => $this->end_time->toIso8601String(),
            'backgroundColor' => self::teacherColor($this->teacher_id),
            'borderColor'     => self::teacherColor($this->teacher_id),
            'textColor'       => '#ffffff',
            'extendedProps'   => [
                'teacher_id'   => $this->teacher_id,
                'teacher_name' => $this->teacher?->name ?? '—',
                'description'  => $this->description,
                'meeting_link' => $this->meeting_link,
            ],
        ];
    }
}
