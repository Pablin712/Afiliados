<?php

namespace Tests\Feature\Services;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\User;
use App\Services\ProfitDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitDistributionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_profit_when_sponsor_membership_is_active_and_non_free(): void
    {
        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 100,
            'profit' => 0,
        ]);

        MembershipType::query()->create([
            'name' => 'free',
            'affiliates_required' => 0,
            'cost' => 0,
            'profit' => 0,
        ]);

        $sponsor = User::factory()->create([
            'commission_balance' => 0,
        ]);

        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $customerType->id,
            'status' => 'active',
        ]);

        $buyer = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);

        $payment = Payment::query()->create([
            'user_id' => $buyer->id,
            'number' => 'PAY-001',
            'amount' => 200,
            'state' => 'approved',
        ]);

        /** @var ProfitDistributionService $service */
        $service = app(ProfitDistributionService::class);
        $service->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'user_bank_id' => null,
            'source_payment_id' => $payment->id,
            'source_user_id' => $buyer->id,
            'source_level' => 1,
            'state' => 'pending',
        ]);

        $sponsor->refresh();
        $this->assertSame(20.0, (float) $sponsor->commission_balance);
    }

    public function test_it_does_not_generate_profit_when_sponsor_membership_is_free(): void
    {
        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 100,
            'profit' => 0,
        ]);

        $freeType = MembershipType::query()->create([
            'name' => 'free',
            'affiliates_required' => 0,
            'cost' => 0,
            'profit' => 0,
        ]);

        $sponsor = User::factory()->create([
            'commission_balance' => 0,
        ]);

        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $freeType->id,
            'status' => 'free',
        ]);

        $buyer = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);

        $payment = Payment::query()->create([
            'user_id' => $buyer->id,
            'number' => 'PAY-002',
            'amount' => 200,
            'state' => 'approved',
        ]);

        /** @var ProfitDistributionService $service */
        $service = app(ProfitDistributionService::class);
        $service->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseMissing('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
            'source_user_id' => $buyer->id,
        ]);

        $sponsor->refresh();
        $this->assertSame(0.0, (float) $sponsor->commission_balance);
    }
}
