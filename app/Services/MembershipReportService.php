<?php

namespace App\Services;

use App\Models\Action;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MembershipReportService
{
    public const SEGMENTS = ['all', 'free', 'active', 'expired', 'pending_payment', 'non_renewed'];

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
     * Full, point-in-time list of users for a given segment, independent of the report's date range.
     *
     * @return array{total:int,records:list<array<string,mixed>>}
     */
    public function segmentUsers(string $segment): array
    {
        $records = match ($segment) {
            'free' => $this->listFreeUsers(),
            'non_renewed' => collect($this->listFreeUsers())->filter(fn (array $row) => $row['previously_paid'])->values()->all(),
            'active', 'expired', 'pending_payment' => $this->listByStatus($segment),
            default => [],
        };

        return [
            'total' => count($records),
            'records' => $records,
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
     * Users whose membership dropped to "free" within the period (for the period-bound overview).
     *
     * @return array{total:int,by_previous_type:list<array{name:string,total:int}>,records:list<array<string,mixed>>}
     */
    protected function nonRenewedInPeriod(CarbonInterface $from, CarbonInterface $to): array
    {
        $downgrades = $this->downgradeHistory($from, $to);
        $usersById = User::query()->whereIn('id', $downgrades->keys())->get()->keyBy('id');

        $records = $downgrades->map(function (array $history, int $userId) use ($usersById): array {
            $user = $usersById->get($userId);

            return [
                'user_name' => $user?->name ?? '—',
                'user_email' => $user?->email ?? '—',
                'previous_type' => $history['previous_type'],
                'downgraded_at' => optional($history['downgraded_at'])->toDateTimeString(),
            ];
        })->sortByDesc('downgraded_at')->values();

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

    /**
     * @return list<array<string,mixed>>
     */
    protected function listByStatus(string $status): array
    {
        $rows = Membership::query()
            ->select('memberships.*')
            ->addSelect([
                'users.name as user_name',
                'users.email as user_email',
                'users.created_at as user_joined_at',
                'membership_types.name as membership_type_name',
            ])
            ->join('users', 'users.id', '=', 'memberships.user_id')
            ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
            ->where('memberships.status', $status)
            ->orderByDesc('memberships.updated_at')
            ->limit(5000)
            ->get();

        return $rows->map(fn (Membership $row): array => [
            'user_name' => $row->user_name,
            'user_email' => $row->user_email,
            'membership_type_name' => $row->membership_type_name,
            'joined_at' => $row->user_joined_at ? Carbon::parse($row->user_joined_at)->toDateTimeString() : null,
            'started_at' => optional($row->started_at)->toDateTimeString(),
            'expires_at' => optional($row->expires_at)->toDateTimeString(),
        ])->values()->all();
    }

    /**
     * All users currently on the free plan, flagged with whether they previously held a paid plan
     * (detected from a stored past expiration date or a status-downgrade event in the audit trail).
     *
     * @return list<array<string,mixed>>
     */
    protected function listFreeUsers(): array
    {
        $downgrades = $this->downgradeHistory();

        $rows = Membership::query()
            ->select('memberships.*')
            ->addSelect(['users.name as user_name', 'users.email as user_email', 'users.created_at as user_joined_at'])
            ->join('users', 'users.id', '=', 'memberships.user_id')
            ->where('memberships.status', 'free')
            ->orderByDesc('users.created_at')
            ->limit(5000)
            ->get();

        return $rows->map(function (Membership $row) use ($downgrades): array {
            $history = $downgrades->get($row->user_id);
            $previouslyPaid = $row->expires_at !== null || $history !== null;

            return [
                'user_name' => $row->user_name,
                'user_email' => $row->user_email,
                'joined_at' => $row->user_joined_at ? Carbon::parse($row->user_joined_at)->toDateTimeString() : null,
                'previously_paid' => $previouslyPaid,
                'previous_type' => $history['previous_type'] ?? ($previouslyPaid ? '—' : null),
                'downgraded_at' => $history ? optional($history['downgraded_at'])->toDateTimeString() : null,
            ];
        })->values()->all();
    }

    /**
     * Most recent free-downgrade event per user, read from the audit trail (the `actions` table
     * logs every Membership update via App\Providers\AppServiceProvider). Optionally scoped to a
     * date range; unscoped when called with no arguments.
     *
     * @return Collection<int, array{downgraded_at: CarbonInterface|null, previous_type: string}>
     */
    protected function downgradeHistory(?CarbonInterface $from = null, ?CarbonInterface $to = null): Collection
    {
        $typeNamesById = MembershipType::query()->pluck('name', 'id');

        $query = Action::query()
            ->where('module', 'memberships')
            ->where('action', 'update');

        if ($from !== null && $to !== null) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $actions = $query->orderBy('created_at')->get();

        $history = [];

        foreach ($actions as $action) {
            $newValues = $action->new_values ?? [];
            $oldValues = $action->old_values ?? [];

            if (($newValues['status'] ?? null) !== 'free' || ($oldValues['status'] ?? null) === 'free' || ! $action->user_id) {
                continue;
            }

            $previousTypeId = $oldValues['membership_type_id'] ?? null;

            $history[$action->user_id] = [
                'downgraded_at' => $action->created_at,
                'previous_type' => $previousTypeId !== null ? (string) ($typeNamesById[$previousTypeId] ?? '—') : '—',
            ];
        }

        return collect($history);
    }
}
