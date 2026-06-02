<?php

namespace App\Http\Controllers;

use App\Models\Profit;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AffiliateTreeService;
use App\Services\MembershipTierService;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AffiliateTreeService $affiliateTreeService,
        private readonly MembershipTierService $membershipTierService,
    ) {
    }

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $membershipTypeName = strtolower((string) ($user->membership?->membershipType?->name ?? 'free'));
        $canDownloadScanners = ! $isAdmin && $membershipTypeName !== 'free';

        $isFreeUser = ! $isAdmin && $membershipTypeName === 'free';
        $freeDerivWindowExpiresAt = $isFreeUser ? $user->created_at->copy()->addHours(12) : null;
        $freeDerivWindowOpen = $isFreeUser && now()->lt($freeDerivWindowExpiresAt);

        $user->loadMissing([
            'membership.membershipType',
            'membership.lastPayment',
            'sponsor.membership.membershipType',
        ]);

        $affiliateCollections = $this->affiliateTreeService->affiliatesByLevel($user, 3);
        $affiliateCounts = collect($affiliateCollections)
            ->map(fn ($rows) => $rows->count())
            ->all();

        $monthlyProfitsQuery = Profit::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $pendingProfitsQuery = Profit::query()
            ->where('user_id', $user->id)
            ->where('state', 'pending');

        $paidProfitsQuery = Profit::query()
            ->where('user_id', $user->id)
            ->where('state', 'made');

        $hasSourcePaymentColumn = Schema::hasColumn('profits', 'source_payment_id');
        $rankProgress = $isAdmin ? null : $this->buildRankProgress($user);

        $adminKpis = $isAdmin ? [
            'users_total' => (int) User::query()
                ->whereColumn('id', '!=', 'sponsor_id')
                ->count(),
            'customers_total' => (int) DB::table('memberships')
                ->join('membership_types', 'membership_types.id', '=', 'memberships.membership_type_id')
                ->whereRaw('LOWER(membership_types.name) = ?', ['customer'])
                ->count(),
            'approved_payments_month' => (int) Payment::query()
                ->where('state', 'approved')
                ->whereBetween('reviewed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'pending_profits_total' => (float) Profit::query()
                ->where('state', 'pending')
                ->sum('amount'),
            'net_month' => (float) Transaction::query()
                ->where('is_annulled', false)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as net")
                ->value('net'),
        ] : null;

        return view('dashboard', [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'canDownloadScanners' => $canDownloadScanners,
            'freeDerivWindowOpen' => $freeDerivWindowOpen,
            'freeDerivWindowExpiresAt' => $freeDerivWindowExpiresAt,
            'currentMembership' => $isAdmin
                ? __('messages.user.dashboard.admin_membership')
                : ($user->membership?->membershipType?->name ?? 'Free'),
            'sponsor' => $user->sponsor && (int) $user->sponsor->id !== (int) $user->id ? $user->sponsor : null,
            'directAffiliatesCount' => (int) ($affiliateCounts[1] ?? 0),
            'levelTwoAffiliatesCount' => (int) ($affiliateCounts[2] ?? 0),
            'levelThreeAffiliatesCount' => (int) ($affiliateCounts[3] ?? 0),
            'networkAffiliatesCount' => (int) array_sum($affiliateCounts),
            'monthlyProfitsAmount' => $isAdmin ? 0.0 : (float) (clone $monthlyProfitsQuery)->sum('amount'),
            'monthlyApprovedPaymentsCount' => $isAdmin
                ? 0
                : (int) ($hasSourcePaymentColumn
                    ? (clone $monthlyProfitsQuery)->whereNotNull('source_payment_id')->count()
                    : (clone $monthlyProfitsQuery)->count()),
            'pendingProfitsAmount' => $isAdmin ? 0.0 : (float) (clone $pendingProfitsQuery)->sum('amount'),
            'paidProfitsAmount' => $isAdmin ? 0.0 : (float) (clone $paidProfitsQuery)->sum('amount'),
            'recentProfits' => $isAdmin
                ? new Collection()
                : Profit::query()
                    ->with(['sourceUser', 'sourcePayment'])
                    ->where('user_id', $user->id)
                    ->latest('created_at')
                    ->limit(6)
                    ->get(),
            'recentAffiliates' => $user->affiliates()
                ->with(['membership.membershipType'])
                ->latest('created_at')
                ->limit(6)
                ->get(),
            'adminKpis' => $adminKpis,
            'rankProgress' => $rankProgress,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildRankProgress(User $user): ?array
    {
        $membershipTypeName = strtolower((string) ($user->membership?->membershipType?->name ?? 'free'));
        $membershipStatus = strtolower((string) ($user->membership?->status ?? 'free'));

        $snapshot = $this->membershipTierService->inspectUser($user);
        $currentRankIndex = (int) ($snapshot['rank_index'] ?? 0);
        $directAffiliates = (int) ($snapshot['active_direct_affiliates'] ?? 0);
        $teamPoints = (int) ($snapshot['team_points'] ?? 0);

        $rankOrder = ['beginner', 'constructor', 'explorer', 'professional', 'elite', 'master', 'legend'];
        $rankRules = (array) config('affiliates.rank_rules', []);
        $pointsPerAffiliate = max(1, (int) config('affiliates.points_per_affiliate', 100));

        if ($membershipStatus !== 'active' || in_array($membershipTypeName, ['free', 'customer', ''], true)) {
            $nextRankName = 'beginner';
            $currentRankName = $membershipTypeName === 'free' ? 'free' : 'customer';
        } else {
            $currentRankName = $rankOrder[$currentRankIndex - 1] ?? 'customer';
            $nextRankName = $rankOrder[$currentRankIndex] ?? null;
        }

        if ($nextRankName === null) {
            return [
                'current_rank_name' => $currentRankName,
                'next_rank_name' => null,
                'direct_affiliates' => $directAffiliates,
                'team_points' => $teamPoints,
                'is_max_rank' => true,
                'progress_percent' => 100,
            ];
        }

        /** @var array<string, int|null> $rule */
        $rule = (array) ($rankRules[$nextRankName] ?? []);
        $requiredDirect = (int) ($rule['direct_affiliates'] ?? 0);
        $requiredTeamPoints = (int) ($rule['team_points'] ?? 0);
        $requiredDirectRankMin = isset($rule['direct_rank_min']) ? (int) $rule['direct_rank_min'] : null;
        $requiredDirectRankCount = (int) ($rule['direct_rank_count'] ?? 0);
        $requiredPointsByDirects = $requiredDirect * $pointsPerAffiliate;
        $effectiveRequiredPoints = max($requiredTeamPoints, $requiredPointsByDirects);

        $remainingDirect = max(0, $requiredDirect - $directAffiliates);
        $remainingPoints = max(0, $effectiveRequiredPoints - $teamPoints);
        $remainingTeamAffiliates = (int) ceil($remainingPoints / $pointsPerAffiliate);

        $qualifiedDirectByRank = 0;
        $remainingQualifiedDirectByRank = 0;

        if ($requiredDirectRankMin !== null && $requiredDirectRankCount > 0) {
            $directs = $this->affiliateTreeService->affiliatesByLevel($user, 1)[1] ?? collect();

            foreach ($directs as $direct) {
                if (! $direct instanceof User) {
                    continue;
                }

                $directSnapshot = $this->membershipTierService->inspectUser($direct);
                $directRankIndex = (int) ($directSnapshot['rank_index'] ?? 0);

                if ($directRankIndex >= $requiredDirectRankMin) {
                    $qualifiedDirectByRank++;
                }
            }

            $remainingQualifiedDirectByRank = max(0, $requiredDirectRankCount - $qualifiedDirectByRank);
        }

        $totalCriteria = 0;
        $metCriteria = 0;

        if ($requiredDirect > 0) {
            $totalCriteria++;
            if ($remainingDirect === 0) {
                $metCriteria++;
            }
        }

        if ($requiredTeamPoints > 0) {
            $totalCriteria++;
            if ($remainingPoints === 0) {
                $metCriteria++;
            }
        }

        if ($requiredDirectRankMin !== null && $requiredDirectRankCount > 0) {
            $totalCriteria++;
            if ($remainingQualifiedDirectByRank === 0) {
                $metCriteria++;
            }
        }

        $progressPercent = $totalCriteria > 0
            ? (int) round(($metCriteria / $totalCriteria) * 100)
            : 100;

        return [
            'current_rank_name' => $currentRankName,
            'next_rank_name' => $nextRankName,
            'direct_affiliates' => $directAffiliates,
            'team_points' => $teamPoints,
            'required_direct' => $requiredDirect,
            'required_team_points' => $requiredTeamPoints,
            'required_effective_points' => $effectiveRequiredPoints,
            'required_direct_rank_min' => $requiredDirectRankMin,
            'required_direct_rank_count' => $requiredDirectRankCount,
            'qualified_direct_by_rank' => $qualifiedDirectByRank,
            'remaining_direct' => $remainingDirect,
            'remaining_points' => $remainingPoints,
            'remaining_team_affiliates' => $remainingTeamAffiliates,
            'remaining_qualified_direct_by_rank' => $remainingQualifiedDirectByRank,
            'is_max_rank' => false,
            'progress_percent' => max(0, min(100, $progressPercent)),
        ];
    }
}
