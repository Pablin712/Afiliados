<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AffiliateTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffiliateNetworkController extends Controller
{
    public function __construct(private readonly AffiliateTreeService $affiliateTreeService)
    {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'depth' => ['nullable', 'integer', 'min:1', 'max:6'],
        ]);

        $depth = (int) ($validated['depth'] ?? 3);
        $graph = $this->affiliateTreeService->buildUserScopeGraph($user, $depth);
        $affiliateLevels = $this->affiliateTreeService->affiliatesByLevel($user, 3);

        return view('user.network.index', [
            'depth' => $depth,
            'graph' => $graph,
            'sponsor' => $user->sponsor && (int) $user->sponsor->id !== (int) $user->id ? $user->sponsor : null,
            'directAffiliatesCount' => (int) (($affiliateLevels[1] ?? collect())->count()),
            'networkAffiliatesCount' => (int) collect($affiliateLevels)->sum(fn ($rows) => $rows->count()),
        ]);
    }

    public function insights(Request $request, User $user): JsonResponse
    {
        /** @var User $viewer */
        $viewer = $request->user();

        abort_unless($this->affiliateTreeService->canAccessInUserScope($viewer, $user), 403);

        return response()->json([
            'data' => $this->affiliateTreeService->userScopeInsights($viewer, $user),
        ]);
    }
}
