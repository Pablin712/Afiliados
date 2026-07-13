<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\User;
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
        $start    = $request->input('start');
        $end      = $request->input('end');
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
     * Endpoint for n8n: returns classes starting in ~30 minutes that haven't
     * had their reminder sent yet. Marks each one as sent atomically so
     * concurrent runs don't double-notify.
     *
     * Auth: X-Internal-Token header (or Bearer token).
     */
    public function upcomingForReminder(): JsonResponse
    {
        $now  = Carbon::now('UTC');
        $from = $now->copy()->addMinutes(28)->format('Y-m-d H:i:s');
        $to   = $now->copy()->addMinutes(33)->format('Y-m-d H:i:s');

        $schedules = ClassSchedule::with('teacher')
            ->whereBetween('start_time', [$from, $to])
            ->whereNull('reminder_sent_at')
            ->get();

        // Mark as sent before returning so n8n retries don't double-notify
        $schedules->each(fn (ClassSchedule $s) => $s->update(['reminder_sent_at' => $now]));

        return response()->json($schedules->map(function (ClassSchedule $s) {
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $s->getRawOriginal('start_time'), 'UTC');
            $end   = Carbon::createFromFormat('Y-m-d H:i:s', $s->getRawOriginal('end_time'), 'UTC');

            $recipientQuery = User::whereNotNull('telegram_chat_id');

            if ($s->is_exclusive) {
                $recipientQuery->whereHas('membership', fn ($q) =>
                    $q->whereHas('membershipType', fn ($q2) =>
                        $q2->whereRaw('LOWER(name) != ?', ['free'])
                    )
                );
            }

            $chatIds = $recipientQuery->pluck('telegram_chat_id')->filter()->values()->all();

            // Always notify the teacher regardless of membership
            if ($s->teacher?->telegram_chat_id && ! in_array($s->teacher->telegram_chat_id, $chatIds, true)) {
                $chatIds[] = $s->teacher->telegram_chat_id;
            }

            return [
                'id'           => $s->id,
                'title'        => $s->title,
                'teacher_name' => $s->teacher?->name ?? '—',
                'meeting_link' => $s->meeting_link,
                'is_exclusive' => $s->is_exclusive,
                'start_iso'    => $start->toIso8601String(),
                'end_iso'      => $end->toIso8601String(),
                'chat_ids'     => $chatIds,
            ];
        }));
    }
}
