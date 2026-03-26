<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipTierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipTierController extends Controller
{
    public function __construct(private readonly MembershipTierService $membershipTierService)
    {
    }

    public function recalculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $result = $this->membershipTierService->recalculate(
            isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            (bool) ($validated['dry_run'] ?? false)
        );

        return response()->json([
            'message' => 'Membership tiers recalculated successfully.',
            'meta' => [
                'scope' => isset($validated['user_id']) ? 'single-user' : 'all-users',
                'user_id' => isset($validated['user_id']) ? (int) $validated['user_id'] : null,
                'dry_run' => (bool) ($validated['dry_run'] ?? false),
                'processed' => $result['processed'],
                'changed' => $result['changed'],
            ],
            'data' => $result,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'Membership tier evaluation generated successfully.',
            'meta' => [
                'user_id' => (int) $user->id,
            ],
            'data' => $this->membershipTierService->inspectUser($user),
        ]);
    }
}
