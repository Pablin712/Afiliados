<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ClassSchedule;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Log;

class ClassScheduleReminderService
{
    /**
     * Window is wider than 30 minutes so the reminder isn't missed between polls
     * (n8n may not poll exactly every minute).
     */
    private const WINDOW_MINUTES_BEFORE_MIN = 20;
    private const WINDOW_MINUTES_BEFORE_MAX = 40;

    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly WhatsappGroupService $whatsappGroupService,
    ) {
    }

    /**
     * @return array{due:int,sent:int,failed:int,schedule_ids:list<int>}
     */
    public function sendDueReminders(): array
    {
        // start_time is stored as a plain UTC string (no offset); whereBetween
        // compares it as a raw string, so the bounds must also be UTC or every
        // comparison is off by the app timezone's offset (5h for Guayaquil).
        $windowStart = now('UTC')->addMinutes(self::WINDOW_MINUTES_BEFORE_MIN);
        $windowEnd = now('UTC')->addMinutes(self::WINDOW_MINUTES_BEFORE_MAX);

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

            $success = $this->notifyChannelsForSchedule($schedule);

            if ($success) {
                $sent++;
            } else {
                $failed++;
            }

            $schedule->update(['reminder_sent_at' => now()]);
        }

        return [
            'due'          => $dueSchedules->count(),
            'sent'         => $sent,
            'failed'       => $failed,
            'schedule_ids' => $scheduleIds,
        ];
    }

    private function notifyChannelsForSchedule(ClassSchedule $schedule): bool
    {
        // The premium/exclusive channel always gets every reminder (general and
        // exclusive); the "all members" channel only gets the non-exclusive ones.
        $purposes = $schedule->is_exclusive
            ? [Channel::PURPOSE_CLASS_REMINDER_PREMIUM]
            : [Channel::PURPOSE_CLASS_REMINDER_PREMIUM, Channel::PURPOSE_CLASS_REMINDER_ALL];

        $channels = Channel::query()->active()->whereIn('purpose', $purposes)->get();

        if ($channels->isEmpty()) {
            Log::warning('Class reminder: no active channel configured for purpose.', [
                'schedule_id' => $schedule->id,
                'purposes'    => $purposes,
            ]);

            return false;
        }

        $message = $this->buildMessage($schedule);
        $anySuccess = false;

        foreach ($channels as $channel) {
            $success = $this->sendToChannel($channel, $message);

            if ($success) {
                $anySuccess = true;
            } else {
                Log::warning('Class reminder: failed to send message through channel.', [
                    'schedule_id' => $schedule->id,
                    'channel_id'  => $channel->id,
                    'channel'     => $channel->name,
                    'type'        => $channel->type,
                ]);
            }
        }

        return $anySuccess;
    }

    private function sendToChannel(Channel $channel, string $message): bool
    {
        if ($channel->chat_id === null || $channel->chat_id === '') {
            return false;
        }

        return match ($channel->type) {
            Channel::TYPE_TELEGRAM => $this->telegramService->sendMessage(
                $channel->chat_id,
                $message,
                $channel->bot_token,
            ),
            Channel::TYPE_WHATSAPP => $this->whatsappGroupService->sendText(
                (string) $channel->server_url,
                (string) $channel->instance_name,
                (string) $channel->api_key,
                $channel->chat_id,
                $message,
            ),
            default => false,
        };
    }

    private function buildMessage(ClassSchedule $schedule): string
    {
        $minutesUntilStart = max(0, (int) round(now()->diffInMinutes($schedule->start_time, false)));
        $startTime = $schedule->start_time->timezone(config('app.timezone'))->format('H:i');
        $teacherName = $schedule->teacher?->name ?? '—';

        $fallback = "\u{1F514} Recordatorio de clase en {minutes} minutos\n"
            ."\u{1F4DA} {title}\n"
            ."\u{1F468}\u{200D}\u{1F3EB} Profesor: {teacher_name}\n"
            ."\u{1F550} Hora: {start_time}\n"
            ."\u{1F4DD} {description}\n"
            ."\u{1F517} Enlace: {meeting_link}";

        $body = MessageTemplate::bodyFor('class_reminder', $fallback);

        return strtr($body, [
            '{minutes}' => (string) $minutesUntilStart,
            '{title}' => (string) $schedule->title,
            '{teacher_name}' => $teacherName,
            '{start_time}' => $startTime,
            '{description}' => (string) $schedule->description,
            '{meeting_link}' => (string) $schedule->meeting_link,
        ]);
    }
}
