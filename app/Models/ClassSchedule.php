<?php

namespace App\Models;

use Carbon\Carbon;
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
        'is_exclusive',
        'reminder_sent_at',
    ];

    protected $casts = [
        'start_time'       => 'datetime',
        'end_time'         => 'datetime',
        'is_exclusive'     => 'boolean',
        'reminder_sent_at' => 'datetime',
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

    /**
     * Read raw DB strings as UTC to bypass app-timezone misinterpretation.
     * Eloquent's datetime cast applies the app timezone when creating Carbon
     * instances; since we store UTC datetimes in a DATETIME column, we must
     * parse the raw value explicitly in UTC so FullCalendar receives a correct
     * ISO-8601 string with +00:00 and can convert to browser local time.
     */
    public function toCalendarEvent(): array
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $this->getRawOriginal('start_time'), 'UTC');
        $end   = Carbon::createFromFormat('Y-m-d H:i:s', $this->getRawOriginal('end_time'), 'UTC');

        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'start'           => $start->toIso8601String(),
            'end'             => $end->toIso8601String(),
            'backgroundColor' => self::teacherColor($this->teacher_id),
            'borderColor'     => self::teacherColor($this->teacher_id),
            'textColor'       => '#ffffff',
            'extendedProps'   => [
                'teacher_id'   => $this->teacher_id,
                'teacher_name' => $this->teacher?->name ?? '—',
                'description'  => $this->description,
                'meeting_link' => $this->meeting_link,
                'is_exclusive' => $this->is_exclusive,
            ],
        ];
    }
}
