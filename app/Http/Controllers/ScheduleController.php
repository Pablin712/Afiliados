<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
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
        $start = $request->input('start');
        $end   = $request->input('end');

        $events = ClassSchedule::with('teacher')
            ->when($start, fn ($q) => $q->where('end_time', '>=', $start))
            ->when($end, fn ($q) => $q->where('start_time', '<=', $end))
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
}
