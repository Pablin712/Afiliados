<?php

namespace App\Services;

use App\Models\ClassSchedule;
use Illuminate\Support\Facades\Log;

class ClassScheduleReminderService
{
    /**
     * Window is wider than 30 minutes so the reminder isn't missed between polls
     * (n8n may not poll exactly every minute).
     */
    private const WINDOW_MINUTES_BEFORE_MIN = 20;
    private const WINDOW_MINUTES_BEFORE_MAX = 40;

    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    /**
     * @return array{group:string,chat_id:string,due:int,sent:int,failed:int,schedule_ids:list<int>}
     */
    public function sendDueReminders(string $group = 'aet_premium'): array
    {
        $chatId = (string) config("affiliates.telegram.groups.{$group}", '');

        $windowStart = now()->addMinutes(self::WINDOW_MINUTES_BEFORE_MIN);
        $windowEnd = now()->addMinutes(self::WINDOW_MINUTES_BEFORE_MAX);

        $dueSchedules = ClassSchedule::query()
            ->with('teacher')
            ->whereNull('reminder_sent_at')
            ->whereBetween('start_time', [$windowStart, $windowEnd])
            ->get();

        $sent = 0;
        $failed = 0;
        $scheduleIds = [];

        foreach ($dueSchedules as $schedule) {
            $scheduleIds[] = $schedule->id;

            $success = $chatId !== '' && $this->telegramService->sendMessage($chatId, $this->buildMessage($schedule));

            if ($success) {
                $sent++;
            } else {
                $failed++;
                Log::warning('Class reminder: failed to send Telegram message.', [
                    'schedule_id' => $schedule->id,
                    'group' => $group,
                ]);
            }

            $schedule->update(['reminder_sent_at' => now()]);
        }

        return [
            'group' => $group,
            'chat_id' => $chatId,
            'due' => $dueSchedules->count(),
            'sent' => $sent,
            'failed' => $failed,
            'schedule_ids' => $scheduleIds,
        ];
    }

    private function buildMessage(ClassSchedule $schedule): string
    {
        $minutesUntilStart = max(0, (int) round(now()->diffInMinutes($schedule->start_time, false)));
        $startTime = $schedule->start_time->timezone(config('app.timezone'))->format('H:i');
        $teacherName = $schedule->teacher?->name ?? '—';

        $lines = [
            "\u{1F514} Recordatorio de clase en {$minutesUntilStart} minutos",
            "\u{1F4DA} {$schedule->title}",
            "\u{1F468}\u{200D}\u{1F3EB} Profesor: {$teacherName}",
            "\u{1F550} Hora: {$startTime}",
        ];

        if (! empty($schedule->meeting_link)) {
            $lines[] = "\u{1F517} Enlace: {$schedule->meeting_link}";
        }

        return implode("\n", $lines);
    }
}
