<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('affiliates.internal_api_token', 'test-internal-token');
    }

    public function test_internal_token_is_required_for_payment_verification_endpoints(): void
    {
        $response = $this->getJson('/api/admin/payments/pending');

        $response->assertUnauthorized();
    }

    public function test_pending_payment_can_be_listed_and_approved_by_internal_api(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment-receipts/test-receipt.jpg', 'fake image');

        $payment = $this->createPendingPayment('payment-receipts/test-receipt.jpg', 12.50);

        $listResponse = $this->withHeaders([
            'X-Internal-Token' => 'test-internal-token',
        ])->getJson('/api/admin/payments/pending');

        $listResponse->assertOk();
        $listResponse->assertJsonPath('data.0.payment_id', $payment->id);
        $listResponse->assertJsonPath('data.0.payment_number', $payment->number);

        $approveResponse = $this->withHeaders([
            'X-Internal-Token' => 'test-internal-token',
        ])->postJson('/api/admin/payments/pending/'.$payment->id.'/approve', [
            'trace_id' => 'n8n-trace-001',
            'ai_score' => 96,
            'gateway_reference' => 'GW-ABC-001',
        ]);

        $approveResponse->assertOk();
        $approveResponse->assertJsonPath('meta.state', 'approved');

        $payment->refresh();

        $this->assertSame('approved', $payment->state);
        $this->assertNotNull($payment->reviewed_at);

        $transaction = $payment->transaction()->first();
        $this->assertNotNull($transaction);
        $this->assertSame('12.50', number_format((float) $transaction->amount, 2, '.', ''));

        $this->assertDatabaseHas('memberships', [
            'user_id' => $payment->user_id,
            'status' => 'active',
        ]);
    }

    public function test_pending_payment_receipt_and_reject_work_via_internal_api(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payment-receipts/test-receipt-2.jpg', 'fake image 2');

        $payment = $this->createPendingPayment('payment-receipts/test-receipt-2.jpg', 8.00);

        $receiptResponse = $this->withHeaders([
            'X-Internal-Token' => 'test-internal-token',
        ])->get('/api/admin/payments/pending/'.$payment->id.'/receipt');

        $receiptResponse->assertOk();

        $rejectResponse = $this->withHeaders([
            'X-Internal-Token' => 'test-internal-token',
        ])->postJson('/api/admin/payments/pending/'.$payment->id.'/reject', [
            'reason' => 'OCR mismatch',
        ]);

        $rejectResponse->assertOk();
        $rejectResponse->assertJsonPath('meta.state', 'rejected');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'state' => 'rejected',
        ]);
    }

    public function test_approving_an_affiliate_payment_recalculates_sponsor_rank_before_profit_distribution(): void
    {
        $freeType = MembershipType::query()->firstOrCreate(
            ['name' => 'free'],
            ['affiliates_required' => 0, 'cost' => 0, 'profit' => 0]
        );

        $customerType = MembershipType::query()->firstOrCreate(
            ['name' => 'customer'],
            ['affiliates_required' => 0, 'cost' => 97, 'profit' => 0]
        );

        $beginnerType = MembershipType::query()->firstOrCreate(
            ['name' => 'beginner'],
            ['affiliates_required' => 1, 'cost' => 0, 'profit' => 0]
        );

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin Owner',
            'identification' => 'ADM-001',
            'number' => '2200-001',
            'amount' => 100,
            'detail' => 'Main payout bank',
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Program for tests',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 147,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $sponsor = User::factory()->create([
            'commission_balance' => 0,
        ]);

        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $customerType->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $affiliate = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);

        Membership::query()->create([
            'user_id' => $affiliate->id,
            'membership_type_id' => $freeType->id,
            'status' => 'free',
            'started_at' => now()->subDay(),
            'expires_at' => null,
        ]);

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => 0,
            'amount' => 0,
            'amount_now' => 0,
            'detail' => 'Pending sponsor rank test transaction',
            'is_annulled' => false,
            'created_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'user_id' => $affiliate->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => 'CMP-9001',
            'photo' => null,
            'amount' => 147,
            'state' => 'pending',
        ]);

        $response = $this->withHeaders([
            'X-Internal-Token' => 'test-internal-token',
        ])->postJson('/api/admin/payments/pending/'.$payment->id.'/approve', [
            'trace_id' => 'n8n-trace-rank-first',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('memberships', [
            'user_id' => $sponsor->id,
            'membership_type_id' => $beginnerType->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
            'source_user_id' => $affiliate->id,
            'source_level' => 1,
            'amount' => '22.00',
            'state' => 'pending',
        ]);

        $sponsor->refresh();

        $this->assertSame(22.0, (float) $sponsor->commission_balance);
        $this->assertSame(1, Profit::query()->where('user_id', $sponsor->id)->count());
    }

    public function test_reaching_explorer_grants_rank_promotion_bonus(): void
    {
        $freeType = MembershipType::query()->firstOrCreate(
            ['name' => 'free'],
            ['affiliates_required' => 0, 'cost' => 0, 'profit' => 0]
        );

        $customerType = MembershipType::query()->firstOrCreate(
            ['name' => 'customer'],
            ['affiliates_required' => 0, 'cost' => 97, 'profit' => 0]
        );

        $constructorType = MembershipType::query()->firstOrCreate(
            ['name' => 'constructor'],
            ['affiliates_required' => 3, 'cost' => 0, 'profit' => 0]
        );

        $explorerType = MembershipType::query()->firstOrCreate(
            ['name' => 'explorer'],
            ['affiliates_required' => 5, 'cost' => 0, 'profit' => 40]
        );

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin Owner',
            'identification' => 'ADM-001',
            'number' => '2200-001',
            'amount' => 100,
            'detail' => 'Main payout bank',
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Program for tests',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 147,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $sponsor = User::factory()->create([
            'commission_balance' => 0,
        ]);

        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $constructorType->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        for ($index = 0; $index < 4; $index++) {
            $direct = User::factory()->create([
                'sponsor_id' => $sponsor->id,
            ]);

            Membership::query()->create([
                'user_id' => $direct->id,
                'membership_type_id' => $customerType->id,
                'status' => 'active',
                'started_at' => now()->subDay(),
                'expires_at' => now()->addMonth(),
            ]);
        }

        $affiliate = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);

        Membership::query()->create([
            'user_id' => $affiliate->id,
            'membership_type_id' => $freeType->id,
            'status' => 'free',
            'started_at' => now()->subDay(),
            'expires_at' => null,
        ]);

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => 0,
            'amount' => 0,
            'amount_now' => 0,
            'detail' => 'Pending explorer bonus test transaction',
            'is_annulled' => false,
            'created_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'user_id' => $affiliate->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => 'CMP-9010',
            'photo' => null,
            'amount' => 147,
            'state' => 'pending',
        ]);

        $response = $this->withHeaders([
            'X-Internal-Token' => 'test-internal-token',
        ])->postJson('/api/admin/payments/pending/'.$payment->id.'/approve', [
            'trace_id' => 'n8n-trace-explorer-bonus',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('memberships', [
            'user_id' => $sponsor->id,
            'membership_type_id' => $explorerType->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => $payment->id,
            'source_user_id' => $affiliate->id,
            'source_level' => 1,
            'amount' => '22.00',
            'state' => 'pending',
        ]);

        $this->assertDatabaseHas('profits', [
            'user_id' => $sponsor->id,
            'source_payment_id' => null,
            'source_user_id' => null,
            'source_level' => null,
            'amount' => '40.00',
            'state' => 'pending',
        ]);

        $this->assertTrue(
            Profit::query()
                ->where('user_id', $sponsor->id)
                ->where('detail', 'like', 'rank_bonus|promotion|explorer|%')
                ->exists()
        );

        $sponsor->refresh();

        $this->assertSame(62.0, (float) $sponsor->commission_balance);
    }

    public function test_maintaining_explorer_grants_monthly_bonus_only_once(): void
    {
        $freeType = MembershipType::query()->firstOrCreate(
            ['name' => 'free'],
            ['affiliates_required' => 0, 'cost' => 0, 'profit' => 0]
        );

        $customerType = MembershipType::query()->firstOrCreate(
            ['name' => 'customer'],
            ['affiliates_required' => 0, 'cost' => 97, 'profit' => 0]
        );

        $explorerType = MembershipType::query()->firstOrCreate(
            ['name' => 'explorer'],
            ['affiliates_required' => 5, 'cost' => 0, 'profit' => 40]
        );

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin Owner',
            'identification' => 'ADM-001',
            'number' => '2200-001',
            'amount' => 100,
            'detail' => 'Main payout bank',
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Program for tests',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 147,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $sponsor = User::factory()->create([
            'commission_balance' => 0,
        ]);

        $membership = Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $explorerType->id,
            'status' => 'active',
            'started_at' => now()->subMonth()->startOfMonth()->addDays(3),
            'expires_at' => now()->addMonth(),
            'updated_at' => now()->subMonth()->endOfMonth(),
            'created_at' => now()->subMonth()->startOfMonth()->addDays(3),
        ]);

        $membership->timestamps = false;
        $membership->updated_at = now()->subMonth()->endOfMonth();
        $membership->created_at = now()->subMonth()->startOfMonth()->addDays(3);
        $membership->save();

        $directSponsors = [];

        for ($index = 0; $index < 5; $index++) {
            $direct = User::factory()->create([
                'sponsor_id' => $sponsor->id,
            ]);

            Membership::query()->create([
                'user_id' => $direct->id,
                'membership_type_id' => $customerType->id,
                'status' => 'active',
                'started_at' => now()->subDay(),
                'expires_at' => now()->addMonth(),
            ]);

            $directSponsors[] = $direct;
        }

        foreach ([0, 1] as $index) {
            $affiliate = User::factory()->create([
                'sponsor_id' => $directSponsors[$index]->id,
            ]);

            Membership::query()->create([
                'user_id' => $affiliate->id,
                'membership_type_id' => $freeType->id,
                'status' => 'free',
                'started_at' => now()->subDay(),
                'expires_at' => null,
            ]);

            $transaction = Transaction::query()->create([
                'bank_id' => $bank->id,
                'type' => 'income',
                'amount_previous' => 0,
                'amount' => 0,
                'amount_now' => 0,
                'detail' => 'Pending explorer maintenance test transaction '.$index,
                'is_annulled' => false,
                'created_at' => now(),
            ]);

            $payment = Payment::query()->create([
                'user_id' => $affiliate->id,
                'program_id' => $program->id,
                'transaction_id' => $transaction->id,
                'number' => 'CMP-910'.$index,
                'photo' => null,
                'amount' => 147,
                'state' => 'pending',
            ]);

            $response = $this->withHeaders([
                'X-Internal-Token' => 'test-internal-token',
            ])->postJson('/api/admin/payments/pending/'.$payment->id.'/approve', [
                'trace_id' => 'n8n-trace-explorer-maintenance-'.$index,
            ]);

            $response->assertOk();
        }

        $this->assertSame(
            1,
            Profit::query()
                ->where('user_id', $sponsor->id)
                ->where('detail', 'like', 'rank_bonus|maintenance|explorer|%')
                ->count()
        );

        $this->assertSame(
            2,
            Profit::query()
                ->where('user_id', $sponsor->id)
                ->where('source_level', 2)
                ->count()
        );

        $sponsor->refresh();

        $this->assertSame(44.0, (float) $sponsor->commission_balance);
    }

    private function createPendingPayment(string $photoPath, float $amount): Payment
    {
        $user = User::factory()->create();

        $customerType = MembershipType::query()->firstOrCreate(
            ['name' => 'customer'],
            ['affiliates_required' => 0, 'cost' => 0, 'profit' => 0]
        );

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin Owner',
            'identification' => 'ADM-001',
            'number' => '2200-001',
            'amount' => 100,
            'detail' => 'Main payout bank',
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Program for tests',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => $amount,
            'renewal_cost' => $amount,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => 0,
            'amount' => 0,
            'amount_now' => 0,
            'detail' => 'Pending test transaction',
            'is_annulled' => false,
            'created_at' => now(),
        ]);

        return Payment::query()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => 'CMP-'.random_int(1000, 9999),
            'photo' => $photoPath,
            'amount' => $amount,
            'state' => 'pending',
        ]);
    }
}
