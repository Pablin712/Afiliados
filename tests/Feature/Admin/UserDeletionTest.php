<?php

namespace Tests\Feature\Admin;

use App\Models\ClassSchedule;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\User;
use App\Models\UserBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Deleting a user must leave the rest of the database telling a consistent
 * story: accounting history stays, the network keeps its shape, and nothing
 * is left pointing at someone who no longer exists.
 */
class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    private int $phoneCounter = 0;

    public function test_admin_deletes_an_inactive_user_and_keeps_their_accounting_history(): void
    {
        $admin = $this->admin();
        $sponsor = $this->member('expired');
        $user = $this->member('expired', $sponsor);
        $affiliate = $this->member('free', $user);

        $bank = $this->bankAccount($user);
        $approved = $this->payment($user, 'approved');
        $paidCommission = $this->profit($user, $bank, 'made');
        $owedCommission = $this->profit($user, $bank, 'pending');
        // A commission the sponsor earned from this user's payment.
        $sponsorCommission = Profit::query()->create([
            'user_id' => $sponsor->id,
            'user_bank_id' => $this->bankAccount($sponsor)->id,
            'source_user_id' => $user->id,
            'source_payment_id' => $approved->id,
            'period_month' => now()->startOfMonth(),
            'amount' => 15,
            'state' => 'pending',
        ]);
        DB::table('sessions')->insert([
            'id' => 'session-of-deleted-user', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('window.openDeleteUserModal({&quot;id&quot;:'.$user->id, false);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', __('messages.admin.users.deleted', ['name' => $user->name]));

        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('memberships', ['user_id' => $user->id]);
        $this->assertModelMissing($bank);
        $this->assertDatabaseMissing('sessions', ['id' => 'session-of-deleted-user']);

        // Accounting history stays, without the user.
        $this->assertNull($approved->refresh()->user_id);
        $this->assertSame('approved', $approved->state);
        $this->assertNull($paidCommission->refresh()->user_id);
        $this->assertNull($paidCommission->user_bank_id);

        // Nobody is left to pay what was still owed to them.
        $this->assertModelMissing($owedCommission);

        // The sponsor keeps what they earned from this user.
        $this->assertModelExists($sponsorCommission);
        $this->assertNull($sponsorCommission->refresh()->source_user_id);

        // The network closes the gap.
        $this->assertSame($sponsor->id, $affiliate->refresh()->sponsor_id);
    }

    public function test_a_user_with_an_active_membership_cannot_be_deleted(): void
    {
        $user = $this->member('active');

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertSessionHas('error');

        $this->assertModelExists($user);
    }

    public function test_a_user_with_a_transfer_awaiting_review_cannot_be_deleted(): void
    {
        $user = $this->member('expired');
        $this->payment($user, 'pending');

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, __('messages.admin.users.delete_blockers.pending_payment')));

        $this->assertModelExists($user);
    }

    public function test_an_abandoned_card_checkout_does_not_block_and_is_cancelled(): void
    {
        $user = $this->member('free');
        $checkout = $this->payment($user, 'pending', 'card');

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertSessionHas('status');

        $this->assertSame('cancelled', $checkout->refresh()->state);
        $this->assertNull($checkout->user_id);
    }

    public function test_a_teacher_with_scheduled_classes_cannot_be_deleted(): void
    {
        $user = $this->member('free');
        ClassSchedule::query()->create([
            'teacher_id' => $user->id,
            'title' => 'Clase',
            'meeting_link' => 'https://meet.example/clase',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertSessionHas('error');

        $this->assertModelExists($user);
    }

    public function test_admins_cannot_be_deleted_not_even_by_themselves(): void
    {
        $admin = $this->admin();
        $otherAdmin = $this->admin();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $otherAdmin))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHas('error');

        $this->assertModelExists($admin);
        $this->assertModelExists($otherAdmin);
    }

    public function test_deleting_needs_the_delete_users_permission(): void
    {
        $user = $this->member('free');
        $editor = $this->member('free');
        $editor->givePermissionTo(Permission::findOrCreate('edit users', 'web'));

        $this->actingAs($editor)
            ->delete(route('admin.users.destroy', $user))
            ->assertForbidden();

        $this->assertModelExists($user);
    }

    public function test_review_screens_show_payments_of_deleted_users(): void
    {
        $admin = $this->admin();
        $admin->givePermissionTo(
            Permission::findOrCreate('manage payments', 'web'),
            Permission::findOrCreate('view profits', 'web'),
        );
        $user = $this->member('expired');
        $this->payment($user, 'approved');
        $this->profit($user, $this->bankAccount($user), 'made');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertSessionHas('status');

        $deletedLabel = __('messages.admin.users.deleted_user');

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee($deletedLabel);

        $this->actingAs($admin)
            ->get(route('admin.profits.index'))
            ->assertOk()
            ->assertSee($deletedLabel);
    }

    private function member(string $status, ?User $sponsor = null): User
    {
        $user = User::factory()->create([
            'phone' => '+5939100'.str_pad((string) ++$this->phoneCounter, 4, '0', STR_PAD_LEFT),
            'sponsor_id' => $sponsor?->id,
        ]);

        Membership::query()->create([
            'user_id' => $user->id,
            'membership_type_id' => MembershipType::query()->firstOrCreate(
                ['name' => 'customer'],
                ['affiliates_required' => 0, 'cost' => 97, 'profit' => 0],
            )->id,
            'status' => $status,
            'started_at' => now()->subMonths(2),
            'expires_at' => $status === 'active' ? now()->addMonth() : now()->subMonth(),
        ]);

        return $user;
    }

    private function admin(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $role->givePermissionTo(
            Permission::findOrCreate('view users', 'web'),
            Permission::findOrCreate('delete users', 'web'),
        );

        $user = User::factory()->create(['phone' => '+5939200'.str_pad((string) ++$this->phoneCounter, 4, '0', STR_PAD_LEFT)]);
        $user->assignRole($role);

        return $user;
    }

    private function bankAccount(User $user): UserBank
    {
        return UserBank::query()->create([
            'user_id' => $user->id,
            'bank_name' => 'Banco',
            'owner' => $user->name,
            'identification' => '0102030405',
            'number' => '123456',
            'type' => 'savings',
            'is_default' => true,
        ]);
    }

    private function payment(User $user, string $state, string $method = 'bank_transfer'): Payment
    {
        return Payment::query()->create([
            'user_id' => $user->id,
            'number' => 'TRX-'.uniqid(),
            'amount' => 147,
            'state' => $state,
            'payment_method' => $method,
        ]);
    }

    private function profit(User $user, UserBank $bank, string $state): Profit
    {
        return Profit::query()->create([
            'user_id' => $user->id,
            'user_bank_id' => $bank->id,
            'period_month' => now()->startOfMonth(),
            'amount' => 22,
            'state' => $state,
        ]);
    }
}
