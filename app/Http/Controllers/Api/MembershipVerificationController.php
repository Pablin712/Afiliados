<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipVerificationController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $user = User::query()
            ->with('membership.membershipType')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user instanceof User) {
            return response()->json([
                'active' => 'false',
                'membership' => null,
                'message' => 'No se encontró el usuario.',
            ], 404);
        }

        $membershipType = strtolower((string) ($user->membership?->membershipType?->name ?? 'free'));
        $membershipStatus = strtolower((string) ($user->membership?->status ?? 'free'));

        $isActiveMembership = $membershipStatus === 'active' && $membershipType !== 'free';

        return response()->json([
            'active' => $isActiveMembership ? 'true' : 'false',
            'membership' => $membershipType,
            'message' => $isActiveMembership
                ? 'El usuario tiene una membresía activa.'
                : 'El usuario no tiene una membresía activa.',
        ]);
    }
}
