<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipExpiryService;
use App\Services\MembershipReminderService;
use App\Services\MembershipTierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MembershipTierController extends Controller
{
    public function __construct(
        private readonly MembershipTierService $membershipTierService,
        private readonly MembershipReminderService $membershipReminderService,
        private readonly MembershipExpiryService $membershipExpiryService
    ) {
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
                'scope'                    => isset($validated['user_id']) ? 'single-user' : 'all-users',
                'user_id'                  => isset($validated['user_id']) ? (int) $validated['user_id'] : null,
                'dry_run'                  => (bool) ($validated['dry_run'] ?? false),
                'processed'                => $result['processed'],
                'changed'                  => $result['changed'],
                'whatsapp_group_removed'   => $result['whatsapp_group']['removed'] ?? 0,
                'telegram_groups_banned'   => $result['telegram_groups']['banned'] ?? 0,
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

    public function expiredToday(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:2000'],
        ]);

        $date = isset($validated['date'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay()
            : now()->addDay()->startOfDay();

        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 500;

        $users = $this->membershipReminderService->usersWithMembershipExpiredOnDate($date, $limit);

        return response()->json([
            'message' => 'Users with membership expiring on the requested date retrieved successfully.',
            'meta' => [
                'requested_date' => $date->toDateString(),
                'default_date_behavior' => isset($validated['date']) ? 'custom' : 'tomorrow',
                'count' => count($users),
                'limit' => $limit,
            ],
            'data' => $users,
        ]);
    }

    public function downgradeExpired(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $result = $this->membershipExpiryService->processExpired((bool) ($validated['dry_run'] ?? false));

        return response()->json([
            'message' => 'Expired memberships processed successfully.',
            'meta' => $result,
            'data' => $result,
        ]);
    }
}
