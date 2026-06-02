<?php

namespace Tests\Feature\Plans;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlansRenewalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_blocks_payment_receipt_for_non_customer_non_free_users(): void
    {
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
            'description' => 'Program',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 1,
            'is_active' => true,
        ]);

        Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin',
            'identification' => '9999999999',
            'number' => '1234567890',
            'amount' => 1000,
        ]);

        $user = User::factory()->create();

        Membership::query()->create([
            'user_id' => $user->id,
            'membership_type_id' => $beginnerType->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_it_allows_free_month_renewal_for_active_tier_user_on_expiry_day_with_required_affiliates(): void
    {
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

        $user = User::factory()->create();

        Membership::query()->create([
            'user_id' => $user->id,
            'membership_type_id' => $beginnerType->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'expires_at' => now(),
        ]);

        for ($i = 0; $i < 3; $i++) {
            $affiliate = User::factory()->create([
                'sponsor_id' => $user->id,
            ]);

            Membership::query()->create([
                'user_id' => $affiliate->id,
                'membership_type_id' => $customerType->id,
                'status' => 'active',
                'started_at' => now()->subDays(10),
                'expires_at' => now()->addDays(20),
            ]);
        }

        $response = $this->actingAs($user)->post(route('plans.renew-free'));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $membership = Membership::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertTrue($membership->expires_at !== null && $membership->expires_at->greaterThan(now()->addDays(20)));
    }

    public function test_it_sends_webhook_when_pending_payment_is_created(): void
    {
        Storage::fake('public');

        config()->set('affiliates.payment_verifier_webhook_url', 'https://autobot.aaronsoft.es/webhook/afiliados-payment-verifier');
        config()->set('affiliates.payment_verifier_webhook_token', 'token-test-123');

        Http::fake();

        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 97,
            'profit' => 0,
        ]);

        $program = Program::query()->create([
            'name' => 'Customer Program',
            'description' => 'Program',
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => true,
        ]);

        $bank = Bank::query()->create([
            'name' => 'Admin Bank',
            'owner' => 'Admin Owner',
            'identification' => '9999999999',
            'number' => '1234567890',
            'amount' => 1000,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'bank_id' => $bank->id,
            'number' => 'CMP-45990812',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Http::assertSent(function ($request): bool {
            if ((string) $request->url() !== 'https://autobot.aaronsoft.es/webhook/afiliados-payment-verifier') {
                return false;
            }

            $data = $request->data();

            return (string) ($request->header('X-Webhook-Token')[0] ?? '') === 'token-test-123'
                && (string) ($data['event'] ?? '') === 'payment.pending'
                && (string) ($data['payment_number'] ?? '') === 'CMP-45990812'
                && isset($data['payment_id'])
                && is_string($data['receipt_url'] ?? null)
                && str_contains((string) $data['receipt_url'], '/api/admin/payments/pending/')
                && str_contains((string) $data['receipt_url'], '/receipt');
        });
    }
}
