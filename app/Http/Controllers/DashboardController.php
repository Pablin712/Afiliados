<?php

namespace App\Http\Controllers;

use App\Models\Profit;
use App\Models\User;
use App\Services\AffiliateTreeService;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(private readonly AffiliateTreeService $affiliateTreeService)
    {
    }

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

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

        return view('dashboard', [
            'user' => $user,
            'isAdmin' => $isAdmin,
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
        ]);
    }
}
