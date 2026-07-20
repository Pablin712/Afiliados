<?php

namespace App\Services;

use App\Models\DailyFinancialStat;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DailyFinancialStatsService
{
    public function __construct(private readonly MembershipReportService $membershipReportService)
    {
    }

    public function hasStatsTable(): bool
    {
        return Schema::hasTable('daily_financial_stats');
    }

    public function registerForDate(CarbonInterface $date): DailyFinancialStat
    {
        $day = Carbon::parse($date)->startOfDay();

        if (! $this->hasStatsTable()) {
            return new DailyFinancialStat([
                'stat_date' => $day->toDateString(),
                'incomes_total' => 0,
                'expenses_total' => 0,
                'net_profit' => 0,
                'new_users_count' => 0,
                'new_customers_count' => 0,
                'approved_payments_count' => 0,
                'pending_profits_total' => 0,
                'profits_paid_total' => 0,
            ]);
        }

        $incomes = (float) Transaction::query()
            ->where('type', 'income')
            ->where('is_annulled', false)
            ->whereDate('created_at', $day)
            ->sum('amount');

        $expenses = (float) Transaction::query()
            ->where('type', 'expense')
            ->where('is_annulled', false)
            ->whereDate('created_at', $day)
            ->sum('amount');

        $newUsers = (int) User::query()
            ->whereDate('created_at', $day)
            ->whereColumn('id', '!=', 'sponsor_id')
            ->count();

        $newCustomers = (int) DB::table('memberships')
            ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
            ->whereRaw('LOWER(membership_types.name) = ?', ['customer'])
            ->whereDate('memberships.started_at', $day)
            ->count();

        $approvedPayments = (int) Payment::query()
            ->where('state', 'approved')
            ->whereDate('reviewed_at', $day)
            ->count();

        $pendingProfits = (float) Profit::query()
            ->where('state', 'pending')
            ->whereDate('created_at', '<=', $day)
            ->sum('amount');

        $profitsPaid = (float) Profit::query()
            ->where('state', 'made')
            ->whereDate('paid_at', $day)
            ->sum('amount');

        return DailyFinancialStat::query()->updateOrCreate(
            ['stat_date' => $day->toDateString()],
            [
                'incomes_total' => $incomes,
                'expenses_total' => $expenses,
                'net_profit' => $incomes - $expenses,
                'new_users_count' => $newUsers,
                'new_customers_count' => $newCustomers,
                'approved_payments_count' => $approvedPayments,
                'pending_profits_total' => $pendingProfits,
                'profits_paid_total' => $profitsPaid,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardSummary(CarbonInterface $from, CarbonInterface $to): array
    {
        $fromDay = Carbon::parse($from)->startOfDay();
        $toDay = Carbon::parse($to)->endOfDay();

        $summary = $this->buildDashboardSummary($fromDay, $toDay);
        $expiryTotals = $this->membershipExpiryRunTotals($fromDay, $toDay);

        $summary['totals']['free_renewals_count'] = $expiryTotals['free_renewals'];
        $summary['totals']['downgraded_count'] = $expiryTotals['downgraded'];
        $summary['totals']['non_renewed_total_now'] = $this->nonRenewedTotalNow();

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildDashboardSummary(CarbonInterface $fromDay, CarbonInterface $toDay): array
    {
        if (! $this->hasStatsTable()) {
            return $this->liveSummary($fromDay, $toDay, true);
        }

        $rows = DailyFinancialStat::query()
            ->whereBetween('stat_date', [$fromDay->toDateString(), $toDay->toDateString()])
            ->orderBy('stat_date')
            ->get();

        if ($rows->isEmpty()) {
            return $this->liveSummary($fromDay, $toDay, false);
        }

        $pendingToPayNow = (float) Profit::query()
            ->where('state', 'pending')
            ->sum('amount');

        return [
            'range' => [
                'from' => $fromDay->toDateString(),
                'to' => $toDay->toDateString(),
            ],
            'totals' => [
                'incomes_total' => (float) $rows->sum('incomes_total'),
                'expenses_total' => (float) $rows->sum('expenses_total'),
                'net_profit_total' => (float) $rows->sum('net_profit'),
                'new_users_count' => (int) $rows->sum('new_users_count'),
                'new_customers_count' => (int) $rows->sum('new_customers_count'),
                'approved_payments_count' => (int) $rows->sum('approved_payments_count'),
                'pending_profits_total_now' => $pendingToPayNow,
            ],
            'line_series' => $rows->map(fn (DailyFinancialStat $row) => [
                'date' => $row->stat_date->toDateString(),
                'incomes' => (float) $row->incomes_total,
                'expenses' => (float) $row->expenses_total,
                'net_profit' => (float) $row->net_profit,
            ])->values()->all(),
            'candles' => $this->buildProfitCandles($rows),
            'membership_totals' => $this->membershipTotals(),
            'stats_table_missing' => false,
            'is_live_fallback' => false,
        ];
    }

    /**
     * @return array{free_renewals: int, downgraded: int}
     */
    protected function membershipExpiryRunTotals(CarbonInterface $fromDay, CarbonInterface $toDay): array
    {
        if (! Schema::hasTable('membership_expiry_runs')) {
            return ['free_renewals' => 0, 'downgraded' => 0];
        }

        $row = DB::table('membership_expiry_runs')
            ->whereBetween('run_at', [$fromDay, $toDay])
            ->selectRaw('COALESCE(SUM(free_renewals), 0) as free_renewals, COALESCE(SUM(downgraded), 0) as downgraded')
            ->first();

        return [
            'free_renewals' => (int) ($row->free_renewals ?? 0),
            'downgraded' => (int) ($row->downgraded ?? 0),
        ];
    }

    /**
     * Live snapshot (not date-bound): users currently on the free plan who previously held a
     * paid membership, i.e. the current "no renovaron" count.
     */
    protected function nonRenewedTotalNow(): int
    {
        return $this->membershipReportService->segmentUsers('non_renewed')['total'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function liveSummary(CarbonInterface $fromDay, CarbonInterface $toDay, bool $statsTableMissing): array
    {
        $dateCursor = Carbon::parse($fromDay)->startOfDay();
        $dateEnd = Carbon::parse($toDay)->startOfDay();

        /** @var array<string, array{date:string,incomes:float,expenses:float,net_profit:float}> $seriesByDate */
        $seriesByDate = [];

        while ($dateCursor->lte($dateEnd)) {
            $dateKey = $dateCursor->toDateString();
            $seriesByDate[$dateKey] = [
                'date' => $dateKey,
                'incomes' => 0.0,
                'expenses' => 0.0,
                'net_profit' => 0.0,
            ];
            $dateCursor->addDay();
        }

        $incomeRows = Transaction::query()
            ->selectRaw('DATE(created_at) as stat_date, SUM(amount) as total')
            ->where('type', 'income')
            ->where('is_annulled', false)
            ->whereBetween('created_at', [$fromDay, $toDay])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'stat_date');

        $expenseRows = Transaction::query()
            ->selectRaw('DATE(created_at) as stat_date, SUM(amount) as total')
            ->where('type', 'expense')
            ->where('is_annulled', false)
            ->whereBetween('created_at', [$fromDay, $toDay])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'stat_date');

        foreach ($incomeRows as $date => $total) {
            $key = (string) $date;
            if (isset($seriesByDate[$key])) {
                $seriesByDate[$key]['incomes'] = (float) $total;
            }
        }

        foreach ($expenseRows as $date => $total) {
            $key = (string) $date;
            if (isset($seriesByDate[$key])) {
                $seriesByDate[$key]['expenses'] = (float) $total;
            }
        }

        foreach ($seriesByDate as $date => $row) {
            $seriesByDate[$date]['net_profit'] = $row['incomes'] - $row['expenses'];
        }

        $lineSeries = array_values($seriesByDate);

        $newUsersCount = (int) User::query()
            ->whereBetween('created_at', [$fromDay, $toDay])
            ->whereColumn('id', '!=', 'sponsor_id')
            ->count();

        $newCustomersCount = (int) DB::table('memberships')
            ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
            ->whereRaw('LOWER(membership_types.name) = ?', ['customer'])
            ->whereBetween('memberships.started_at', [$fromDay, $toDay])
            ->count();

        $approvedPaymentsCount = (int) Payment::query()
            ->where('state', 'approved')
            ->whereBetween('reviewed_at', [$fromDay, $toDay])
            ->count();

        return [
            'range' => [
                'from' => Carbon::parse($fromDay)->toDateString(),
                'to' => Carbon::parse($toDay)->toDateString(),
            ],
            'totals' => [
                'incomes_total' => (float) collect($lineSeries)->sum('incomes'),
                'expenses_total' => (float) collect($lineSeries)->sum('expenses'),
                'net_profit_total' => (float) collect($lineSeries)->sum('net_profit'),
                'new_users_count' => $newUsersCount,
                'new_customers_count' => $newCustomersCount,
                'approved_payments_count' => $approvedPaymentsCount,
                'pending_profits_total_now' => (float) Profit::query()->where('state', 'pending')->sum('amount'),
            ],
            'line_series' => $lineSeries,
            'candles' => $this->buildCandlesFromLineSeries($lineSeries),
            'membership_totals' => $this->membershipTotals(),
            'stats_table_missing' => $statsTableMissing,
            'is_live_fallback' => true,
        ];
    }

    /**
     * @param list<array{date:string,incomes:float,expenses:float,net_profit:float}> $lineSeries
     * @return list<array<string, float|string>>
     */
    protected function buildCandlesFromLineSeries(array $lineSeries): array
    {
        $previousClose = 0.0;

        return collect($lineSeries)->map(function (array $row) use (&$previousClose): array {
            $open = $previousClose;
            $close = (float) $row['net_profit'];
            $high = max($open, $close);
            $low = min($open, $close);
            $previousClose = $close;

            return [
                'date' => (string) $row['date'],
                'open' => $open,
                'high' => $high,
                'low' => $low,
                'close' => $close,
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, DailyFinancialStat> $rows
     * @return list<array<string, float|string>>
     */
    protected function buildProfitCandles(Collection $rows): array
    {
        $previousClose = 0.0;

        return $rows->map(function (DailyFinancialStat $row) use (&$previousClose): array {
            $open = $previousClose;
            $close = (float) $row->net_profit;
            $high = max($open, $close);
            $low = min($open, $close);

            $previousClose = $close;

            return [
                'date' => $row->stat_date->toDateString(),
                'open' => $open,
                'high' => $high,
                'low' => $low,
                'close' => $close,
            ];
        })->values()->all();
    }

    /**
     * @return list<array{name:string,total:int}>
     */
    protected function membershipTotals(): array
    {
        $rows = DB::table('memberships')
            ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
            ->select('membership_types.name', DB::raw('COUNT(*) as total'))
            ->groupBy('membership_types.name')
            ->orderBy('membership_types.name')
            ->get();

        return $rows->map(fn (object $row): array => [
            'name' => (string) $row->name,
            'total' => (int) $row->total,
        ])->values()->all();
    }
}
