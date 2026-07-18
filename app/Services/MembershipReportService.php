<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class MembershipReportService
{
    /**
     * @return array<string, mixed>
     */
    public function build(CarbonInterface $from, CarbonInterface $to): array
    {
        $totalUsers = (int) User::query()
            ->where('created_at', '<=', $to)
            ->whereColumn('id', '!=', 'sponsor_id')
            ->count();

        $newUsers = (int) User::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereColumn('id', '!=', 'sponsor_id')
            ->count();

        $typeBreakdown = $this->typeBreakdown();
        $statusBreakdown = $this->statusBreakdown();
        $upgrades = $this->upgradesInPeriod($from, $to);
        $nonRenewed = $this->nonRenewedInPeriod($from, $to);

        $freeType = collect($typeBreakdown)->firstWhere('name', 'free');
        $freeCount = (int) ($freeType['total'] ?? 0);
        $payingCount = max(0, $totalUsers - $freeCount);

        return [
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'totals' => [
                'total_users' => $totalUsers,
                'new_users' => $newUsers,
                'paying_users' => $payingCount,
                'free_users' => $freeCount,
                'upgrades_count' => $upgrades['total'],
                'non_renewed_count' => $nonRenewed['total'],
            ],
            'type_breakdown' => $typeBreakdown,
            'status_breakdown' => $statusBreakdown,
            'upgrades' => $upgrades,
            'non_renewed' => $nonRenewed,
        ];
    }

    /**
     * @return list<array{name:string,total:int,percent:float}>
     */
    protected function typeBreakdown(): array
    {
        $rows = DB::table('memberships')
            ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
            ->select('membership_types.name', DB::raw('COUNT(*) as total'))
            ->groupBy('membership_types.name')
            ->orderByDesc('total')
            ->get();

        $grandTotal = (int) $rows->sum('total');

        return $rows->map(fn (object $row): array => [
            'name' => (string) $row->name,
            'total' => (int) $row->total,
            'percent' => $grandTotal > 0 ? round(((int) $row->total / $grandTotal) * 100, 1) : 0.0,
        ])->values()->all();
    }

    /**
     * @return list<array{status:string,total:int,percent:float}>
     */
    protected function statusBreakdown(): array
    {
        $rows = DB::table('memberships')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $grandTotal = (int) $rows->sum('total');

        return $rows->map(fn (object $row): array => [
            'status' => (string) $row->status,
            'total' => (int) $row->total,
            'percent' => $grandTotal > 0 ? round(((int) $row->total / $grandTotal) * 100, 1) : 0.0,
        ])->values()->all();
    }

    /**
     * Memberships that started a paid plan within the period (new customers/upgrades).
     *
     * @return array{total:int,by_type:list<array{name:string,total:int}>,records:list<array<string,mixed>>}
     */
    protected function upgradesInPeriod(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Membership::query()
            ->select('memberships.*')
            ->addSelect(['users.name as user_name', 'users.email as user_email', 'membership_types.name as membership_type_name'])
            ->join('users', 'users.id', '=', 'memberships.user_id')
            ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
            ->whereBetween('memberships.started_at', [$from, $to])
            ->where('membership_types.name', '!=', 'free')
            ->orderByDesc('memberships.started_at')
            ->limit(500)
            ->get();

        $byType = $rows->groupBy('membership_type_name')
            ->map(fn ($group, $name) => ['name' => (string) $name, 'total' => $group->count()])
            ->values()
            ->sortByDesc('total')
            ->values()
            ->all();

        return [
            'total' => $rows->count(),
            'by_type' => $byType,
            'records' => $rows->map(fn (Membership $row): array => [
                'user_name' => $row->user_name,
                'user_email' => $row->user_email,
                'membership_type_name' => $row->membership_type_name,
                'started_at' => optional($row->started_at)->toDateTimeString(),
            ])->values()->all(),
        ];
    }

    /**
     * Users whose membership dropped to "free" within the period, detected from the
     * audit trail (actions table logs every Membership update via App\Providers\AppServiceProvider).
     *
     * @return array{total:int,by_previous_type:list<array{name:string,total:int}>,records:list<array<string,mixed>>}
     */
    protected function nonRenewedInPeriod(CarbonInterface $from, CarbonInterface $to): array
    {
        $typeNamesById = MembershipType::query()->pluck('name', 'id');

        $actions = Action::query()
            ->where('module', 'memberships')
            ->where('action', 'update')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $downgrades = $actions->filter(function (Action $action): bool {
            $newValues = $action->new_values ?? [];
            $oldValues = $action->old_values ?? [];

            return ($newValues['status'] ?? null) === 'free' && ($oldValues['status'] ?? null) !== 'free';
        });

        $userIds = $downgrades->pluck('user_id')->filter()->unique()->values();
        $usersById = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        $records = $downgrades->map(function (Action $action) use ($typeNamesById, $usersById): array {
            $oldValues = $action->old_values ?? [];
            $previousTypeId = $oldValues['membership_type_id'] ?? null;
            $user = $action->user_id ? $usersById->get($action->user_id) : null;

            return [
                'user_name' => $user?->name ?? '—',
                'user_email' => $user?->email ?? '—',
                'previous_type' => $previousTypeId !== null ? (string) ($typeNamesById[$previousTypeId] ?? '—') : '—',
                'downgraded_at' => $action->created_at?->toDateTimeString(),
            ];
        })->values();

        $byPreviousType = $records->groupBy('previous_type')
            ->map(fn ($group, $name) => ['name' => (string) $name, 'total' => $group->count()])
            ->values()
            ->sortByDesc('total')
            ->values()
            ->all();

        return [
            'total' => $records->count(),
            'by_previous_type' => $byPreviousType,
            'records' => $records->take(500)->all(),
        ];
    }
}
