<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClassScheduleReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassReminderController extends Controller
{
    public function __construct(private readonly ClassScheduleReminderService $classScheduleReminderService)
    {
    }

    public function sendDue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group' => ['nullable', 'string', 'in:aet_premium,aet_vip_deriv,aet_vip_weltrade'],
        ]);

        $result = $this->classScheduleReminderService->sendDueReminders($validated['group'] ?? 'aet_premium');

        return response()->json([
            'message' => '📅 Recordatorios de clases procesados correctamente. | 📅 Class reminders processed successfully.',
            'message_es' => '📅 Recordatorios de clases procesados correctamente.',
            'message_en' => '📅 Class reminders processed successfully.',
            'meta' => [
                'group' => $result['group'],
                'due' => $result['due'],
                'sent' => $result['sent'],
                'failed' => $result['failed'],
            ],
            'data' => $result,
        ]);
    }
}
