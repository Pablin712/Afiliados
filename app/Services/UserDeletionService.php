<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Deletes a user without breaking what other records say about them.
 *
 * What goes with the user: membership, bank accounts, roles, sessions and
 * the commissions still owed to them (nobody is left to pay them to). What
 * stays: their payments and the commissions already paid, as accounting
 * history with no user. Their direct affiliates move up to their sponsor so
 * the network keeps its shape.
 */
class UserDeletionService
{
    /**
     * Reasons this user cannot be deleted right now, as messages for the
     * admin; empty when they can be.
     *
     * @return list<string>
     */
    public function blockers(User $user, ?User $deletedBy = null): array
    {
        $reasons = [];

        if ($deletedBy !== null && $deletedBy->is($user)) {
            $reasons[] = __('messages.admin.users.delete_blockers.self');
        }

        if ($user->hasRole('admin')) {
            $reasons[] = __('messages.admin.users.delete_blockers.admin');
        }

        if ($user->membership?->status === 'active') {
            $reasons[] = __('messages.admin.users.delete_blockers.active_membership');
        }

        // A transfer waiting for review is money already sent: it has to be
        // approved or rejected while there is still someone to credit.
        if ($user->payments()->where('state', 'pending')->where('payment_method', 'bank_transfer')->exists()) {
            $reasons[] = __('messages.admin.users.delete_blockers.pending_payment');
        }

        // Their classes would disappear from the calendar along with them.
        if (ClassSchedule::query()->where('teacher_id', $user->id)->exists()) {
            $reasons[] = __('messages.admin.users.delete_blockers.teacher_classes');
        }

        return $reasons;
    }

    /**
     * @throws RuntimeException when blockers() is not empty
     */
    public function delete(User $user, ?User $deletedBy = null): void
    {
        $reasons = $this->blockers($user, $deletedBy);

        if ($reasons !== []) {
            throw new RuntimeException(implode(' ', $reasons));
        }

        $newSponsorId = $this->sponsorForOrphans($user);

        DB::transaction(function () use ($user, $newSponsorId): void {
            User::query()
                ->where('sponsor_id', $user->id)
                ->update(['sponsor_id' => $newSponsorId]);

            Profit::query()
                ->where('user_id', $user->id)
                ->where('state', 'pending')
                ->delete();

            // An abandoned card checkout would otherwise sit in the pending
            // list forever with nobody behind it.
            Payment::query()
                ->where('user_id', $user->id)
                ->where('state', 'pending')
                ->update(['state' => 'cancelled']);

            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            // The foreign keys do the rest: membership, bank accounts and
            // roles are removed; payments, paid commissions, audit entries and
            // blog posts keep their row without the user.
            $user->delete();
        });
    }

    /**
     * Who inherits the user's direct affiliates: their own sponsor, or the
     * root of the network when they had none.
     */
    public function sponsorForOrphans(User $user): ?int
    {
        $sponsorId = (int) ($user->sponsor_id ?? 0);

        if ($sponsorId > 0 && $sponsorId !== (int) $user->id) {
            return $sponsorId;
        }

        return User::query()->whereKeyNot($user->id)->whereKey(1)->value('id')
            ?? User::query()->whereKeyNot($user->id)->orderBy('id')->value('id');
    }
}
