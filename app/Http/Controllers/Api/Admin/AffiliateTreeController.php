<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AffiliateTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateTreeController extends Controller
{
    public function __construct(private readonly AffiliateTreeService $affiliateTreeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'root_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'depth' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $rootUser = null;
        if (isset($validated['root_user_id'])) {
            $rootUser = User::query()->find($validated['root_user_id']);
        }

        $tree = $this->affiliateTreeService->buildTree(
            $rootUser,
            (int) ($validated['depth'] ?? 6)
        );

        return response()->json([
            'data' => $tree,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $this->affiliateTreeService->userInsights($user),
        ]);
    }
}
