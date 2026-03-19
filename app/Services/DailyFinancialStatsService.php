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

class DailyFinancialStatsService
{
    public function registerForDate(CarbonInterface $date): DailyFinancialStat
    {
        $day = Carbon::parse($date)->startOfDay();

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

        $rows = DailyFinancialStat::query()
            ->whereBetween('stat_date', [$fromDay->toDateString(), $toDay->toDateString()])
            ->orderBy('stat_date')
            ->get();

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
        ];
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
