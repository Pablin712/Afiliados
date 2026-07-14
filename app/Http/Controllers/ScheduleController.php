<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\User;
use App\Services\ClassScheduleReminderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    private function isFreeUser(): bool
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return false;
        }

        return strtolower((string) ($user->membership?->membershipType?->name ?? 'free')) === 'free';
    }

    public function index(): View
    {
        $teachers = User::role(['teacher', 'admin'])->select('id', 'name')->orderBy('name')->get()
            ->map(fn (User $u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'color' => ClassSchedule::teacherColor($u->id),
            ]);

        return view('schedules.index', compact('teachers'));
    }

    public function events(Request $request): JsonResponse
    {
        // FullCalendar sends these with an explicit UTC offset (timeZone: 'local');
        // start_time/end_time are stored as plain UTC strings, so the bounds must
        // be normalized to UTC before comparing against the raw column.
        $start    = $request->input('start') ? Carbon::parse($request->input('start'))->utc()->format('Y-m-d H:i:s') : null;
        $end      = $request->input('end') ? Carbon::parse($request->input('end'))->utc()->format('Y-m-d H:i:s') : null;
        $freeUser = $this->isFreeUser();

        $events = ClassSchedule::with('teacher')
            ->when($start, fn ($q) => $q->where('end_time', '>=', $start))
            ->when($end, fn ($q) => $q->where('start_time', '<=', $end))
            ->when($freeUser, fn ($q) => $q->where('is_exclusive', false))
            ->orderBy('start_time')
            ->get()
            ->map(fn (ClassSchedule $s) => $s->toCalendarEvent());

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'meeting_link' => ['required', 'url', 'max:500'],
            'start_time'   => ['required', 'date'],
            'end_time'     => ['required', 'date', 'after:start_time'],
            'is_exclusive' => ['required', 'boolean'],
        ]);

        $schedule = ClassSchedule::create([
            ...$validated,
            'teacher_id' => Auth::id(),
        ]);

        $schedule->load('teacher');

        return response()->json($schedule->toCalendarEvent(), 201);
    }

    public function update(Request $request, ClassSchedule $schedule): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hasRole('admin') && $schedule->teacher_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'meeting_link' => ['required', 'url', 'max:500'],
            'start_time'   => ['required', 'date'],
            'end_time'     => ['required', 'date', 'after:start_time'],
            'is_exclusive' => ['required', 'boolean'],
        ]);

        $schedule->update($validated);
        $schedule->load('teacher');

        return response()->json($schedule->toCalendarEvent());
    }

    public function destroy(ClassSchedule $schedule): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hasRole('admin') && $schedule->teacher_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $schedule->delete();

        return response()->json(['message' => 'Clase eliminada.']);
    }

    /**
     * Endpoint for n8n: finds due classes, sends the reminder through the
     * channel configured for each class's audience (all members vs premium
     * exclusive), marks them as sent, and returns a summary.
     *
     * Auth: X-Internal-Token header (or Bearer token).
     */
    public function sendReminders(ClassScheduleReminderService $reminderService): JsonResponse
    {
        $result = $reminderService->sendDueReminders();

        return response()->json($result);
    }
}
