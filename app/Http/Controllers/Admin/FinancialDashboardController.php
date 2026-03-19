<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DailyFinancialStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FinancialDashboardController extends Controller
{
    public function __construct(private readonly DailyFinancialStatsService $dailyFinancialStatsService)
    {
    }

    public function index(Request $request): View
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

        $summary = $this->dailyFinancialStatsService->dashboardSummary($from, $to);

        return view('admin.financial-dashboard.index', [
            'summary' => $summary,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function registerToday(): RedirectResponse
    {
        $this->dailyFinancialStatsService->registerForDate(now()->startOfDay());

        return back()->with('status', __('messages.admin.financial_dashboard.stats_today_registered'));
    }

    public function registerYesterday(): RedirectResponse
    {
        $this->dailyFinancialStatsService->registerForDate(now()->subDay()->startOfDay());

        return back()->with('status', __('messages.admin.financial_dashboard.stats_yesterday_registered'));
    }
}
