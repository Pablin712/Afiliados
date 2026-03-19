<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
            'data' => $row,
        ]);
    }

    public function registerYesterday(): JsonResponse
    {
        $row = $this->dailyFinancialStatsService->registerForDate(now()->subDay()->startOfDay());

        return response()->json([
            'message' => 'Yesterday statistics registered successfully.',
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
            'data' => $this->dailyFinancialStatsService->dashboardSummary($from, $to),
        ]);
    }
}
