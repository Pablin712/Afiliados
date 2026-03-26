<?php

namespace Tests\Feature\Payments;

use App\Http\Controllers\PendingRegistrationController;
use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PendingRegistrationDurationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_first_activation_from_free_to_customer_gets_two_months(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 12, 0, 0));

        $freeType = MembershipType::query()->create([
            'name' => 'free',
            'affiliates_required' => 0,
            'cost' => 0,
            'profit' => 0,
        ]);

        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 97,
            'profit' => 0,
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Plan',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin',
            'identification' => '1234567890',
            'number' => '000111222333',
            'amount' => 0,
        ]);

        $user = User::factory()->create();
        Membership::query()->create([
            'user_id' => $user->id,
            'membership_type_id' => $freeType->id,
            'status' => 'free',
        ]);

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => 0,
            'amount' => 0,
            'amount_now' => 0,
            'detail' => null,
            'is_annulled' => false,
            'created_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => 'PAY-100',
            'amount' => 147,
            'state' => 'pending',
        ]);

        $admin = User::factory()->create();
        Auth::login($admin);

        app(PendingRegistrationController::class)->approve($payment);

        $membership = Membership::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('active', $membership->status);
        $this->assertSame($customerType->id, (int) $membership->membership_type_id);
        $this->assertTrue($membership->expires_at !== null && $membership->expires_at->isSameDay(now()->addMonths(2)));
    }

    public function test_customer_renewal_gets_one_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 12, 0, 0));

        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 97,
            'profit' => 0,
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Plan',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin',
            'identification' => '1234567890',
            'number' => '000111222333',
            'amount' => 0,
        ]);

        $user = User::factory()->create();
        Membership::query()->create([
            'user_id' => $user->id,
            'membership_type_id' => $customerType->id,
            'status' => 'active',
            'started_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => 0,
            'amount' => 0,
            'amount_now' => 0,
            'detail' => null,
            'is_annulled' => false,
            'created_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => 'PAY-200',
            'amount' => 47,
            'state' => 'pending',
        ]);

        $admin = User::factory()->create();
        Auth::login($admin);

        app(PendingRegistrationController::class)->approve($payment);

        $membership = Membership::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('active', $membership->status);
        $this->assertSame($customerType->id, (int) $membership->membership_type_id);
        $this->assertTrue($membership->expires_at !== null && $membership->expires_at->isSameDay(now()->addMonth()));
    }

    public function test_approving_third_direct_customer_payment_promotes_direct_sponsor_to_beginner(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 25, 12, 0, 0));

        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 97,
            'profit' => 0,
        ]);

        $beginnerType = MembershipType::query()->create([
            'name' => 'beginner',
            'affiliates_required' => 3,
            'cost' => 0,
            'profit' => 0,
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Plan',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin',
            'identification' => '1234567890',
            'number' => '000111222333',
            'amount' => 0,
        ]);

        $sponsor = User::factory()->create();
        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $customerType->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'expires_at' => now()->addMonth(),
        ]);

        for ($i = 0; $i < 2; $i++) {
            $activeAffiliate = User::factory()->create([
                'sponsor_id' => $sponsor->id,
            ]);

            Membership::query()->create([
                'user_id' => $activeAffiliate->id,
                'membership_type_id' => $customerType->id,
                'status' => 'active',
                'started_at' => now()->subDays(10),
                'expires_at' => now()->addMonth(),
            ]);
        }

        $newAffiliate = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);

        Membership::query()->create([
            'user_id' => $newAffiliate->id,
            'membership_type_id' => $customerType->id,
            'status' => 'free',
        ]);

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => 0,
            'amount' => 0,
            'amount_now' => 0,
            'detail' => null,
            'is_annulled' => false,
            'created_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'user_id' => $newAffiliate->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => 'PAY-300',
            'amount' => 147,
            'state' => 'pending',
        ]);

        $admin = User::factory()->create();
        Auth::login($admin);

        app(PendingRegistrationController::class)->approve($payment);

        $sponsorMembership = Membership::query()->where('user_id', $sponsor->id)->firstOrFail();

        $this->assertSame($beginnerType->id, (int) $sponsorMembership->membership_type_id);
        $this->assertSame('active', $sponsorMembership->status);
    }
}
