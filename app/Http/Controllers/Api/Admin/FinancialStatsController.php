<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyFinancialStat;
use App\Services\DailyFinancialStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinancialStatsController extends Controller
{
    public function __construct(private readonly DailyFinancialStatsService $dailyFinancialStatsService)
    {
    }

    public function registerForDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay()
            : now()->startOfDay();

        $row = $this->dailyFinancialStatsService->registerForDate($date);

        return response()->json([
            'message' => 'Daily statistics registered successfully.',
            'meta' => [
                'requested_date' => $date->toDateString(),
            ],
            'data' => $row,
        ]);
    }

    public function registerToday(): JsonResponse
    {
        $date = now()->startOfDay();
        $row = $this->dailyFinancialStatsService->registerForDate($date);

        return response()->json([
            'message' => 'Today statistics registered successfully.',
            'meta' => [
                'requested_date' => $date->toDateString(),
            ],
            'data' => $row,
        ]);
    }

    public function registerYesterday(): JsonResponse
    {
        $date = now()->subDay()->startOfDay();
        $row = $this->dailyFinancialStatsService->registerForDate($date);

        return response()->json([
            'message' => 'Yesterday statistics registered successfully.',
            'meta' => [
                'requested_date' => $date->toDateString(),
            ],
            'data' => $row,
        ]);
    }

    public function registerRange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d'],
        ]);

        $from = Carbon::createFromFormat('Y-m-d', $validated['from'])->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $validated['to'])->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $days = $from->diffInDays($to) + 1;
        abort_if($days > 366, 422, 'Date range cannot exceed 366 days.');

        $rows = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $rows[] = $this->dailyFinancialStatsService->registerForDate($cursor)->toArray();
            $cursor->addDay();
        }

        return response()->json([
            'message' => 'Range statistics registered successfully.',
            'meta' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'days' => $days,
                'registered_rows' => count($rows),
            ],
            'data' => $rows,
        ]);
    }

    public function statsByDate(string $date): JsonResponse
    {
        $parsed = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

        $row = DailyFinancialStat::query()
            ->whereDate('stat_date', $parsed)
            ->first();

        if (! $row instanceof DailyFinancialStat) {
            return response()->json([
                'message' => 'No daily statistics found for the requested date.',
                'meta' => [
                    'requested_date' => $parsed->toDateString(),
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Daily statistics retrieved successfully.',
            'meta' => [
                'requested_date' => $parsed->toDateString(),
            ],
            'data' => $row,
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $to = isset($validated['to'])
            ? Carbon::createFromFormat('Y-m-d', $validated['to'])->endOfDay()
            : now()->endOfDay();

        $from = isset($validated['from'])
            ? Carbon::createFromFormat('Y-m-d', $validated['from'])->startOfDay()
            : $to->copy()->subDays(29)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return response()->json([
            'message' => 'Financial dashboard summary generated successfully.',
            'meta' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'data' => $this->dailyFinancialStatsService->dashboardSummary($from, $to),
        ]);
    }
}
