<?php

namespace Tests\Feature\Services;

use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\User;
use App\Services\ProfitDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitDistributionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomerType(): MembershipType
    {
        return MembershipType::query()->firstOrCreate(
            ['name' => 'customer'],
            ['affiliates_required' => 0, 'cost' => 100, 'profit' => 0]
        );
    }

    private function approvedPayment(User $buyer, float $amount, ?\Illuminate\Support\Carbon $reviewedAt): Payment
    {
        return Payment::query()->create([
            'user_id' => $buyer->id,
            'number' => 'PAY-'.uniqid(),
            'amount' => $amount,
            'state' => 'approved',
            'reviewed_at' => $reviewedAt,
        ]);
    }

    public function test_first_direct_sale_of_the_year_generates_no_commission(): void
    {
        $customerType = $this->makeCustomerType();

        $sponsor = User::factory()->create(['commission_balance' => 0]);
        $buyer = User::factory()->create(['sponsor_id' => $sponsor->id]);

        $payment = $this->approvedPayment($buyer, 200, now());

        app(ProfitDistributionService::class)->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseMissing('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
        ]);

        $sponsor->refresh();
        $this->assertSame(0.0, (float) $sponsor->commission_balance);
    }

    public function test_second_direct_sale_of_the_year_earns_15_percent(): void
    {
        $customerType = $this->makeCustomerType();

        $sponsor = User::factory()->create(['commission_balance' => 0]);
        $firstBuyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $secondBuyer = User::factory()->create(['sponsor_id' => $sponsor->id]);

        // Prior sale this year gives the sponsor 100 points before the next one.
        $this->approvedPayment($firstBuyer, 150, now());
        $payment = $this->approvedPayment($secondBuyer, 300, now());

        app(ProfitDistributionService::class)->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
            'source_user_id' => $secondBuyer->id,
            'source_level' => 1,
            'amount' => 45.00,
            'state' => 'pending',
            'detail' => 'direct_sale|15|100',
        ]);

        $sponsor->refresh();
        $this->assertSame(45.0, (float) $sponsor->commission_balance);
    }

    public function test_commission_percentage_increases_with_accumulated_points(): void
    {
        $customerType = $this->makeCustomerType();

        $sponsor = User::factory()->create(['commission_balance' => 0]);

        // 4 prior approved sales this year = 400 points before the 5th sale (20% tier).
        for ($i = 0; $i < 4; $i++) {
            $priorBuyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
            $this->approvedPayment($priorBuyer, 100, now());
        }

        $buyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $payment = $this->approvedPayment($buyer, 100, now());

        app(ProfitDistributionService::class)->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
            'amount' => 20.00,
            'detail' => 'direct_sale|20|400',
        ]);
    }

    public function test_prior_sales_from_a_previous_year_do_not_count_towards_current_year_points(): void
    {
        $customerType = $this->makeCustomerType();

        $sponsor = User::factory()->create(['commission_balance' => 0]);
        $lastYearBuyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $this->approvedPayment($lastYearBuyer, 150, now()->subYear());

        $buyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $payment = $this->approvedPayment($buyer, 200, now());

        app(ProfitDistributionService::class)->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseMissing('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
        ]);
    }

    public function test_sponsor_without_own_membership_still_earns_commission(): void
    {
        $customerType = $this->makeCustomerType();

        // Sponsor has no membership row at all (never bought), unlike the legacy rule.
        $sponsor = User::factory()->create(['commission_balance' => 0]);
        $firstBuyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $secondBuyer = User::factory()->create(['sponsor_id' => $sponsor->id]);

        $this->approvedPayment($firstBuyer, 100, now());
        $payment = $this->approvedPayment($secondBuyer, 100, now());

        app(ProfitDistributionService::class)->distributeForApprovedPayment($payment, $customerType);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
            'amount' => 15.00,
        ]);
    }
}
