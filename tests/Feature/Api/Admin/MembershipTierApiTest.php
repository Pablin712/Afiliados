<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipTierApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $token = 'test-internal-token';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('affiliates.internal_api_token', $this->token);
    }

    public function test_it_rejects_recalculate_without_internal_token(): void
    {
        $response = $this->postJson('/api/admin/memberships/recalculate-tiers');

        $response->assertStatus(401);
    }

    public function test_it_keeps_free_user_as_free_even_when_reaching_three_active_direct_affiliates(): void
    {
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

        $beginnerType = MembershipType::query()->create([
            'name' => 'beginner',
            'affiliates_required' => 1,
            'cost' => 0,
            'profit' => 0,
        ]);

        $sponsor = User::factory()->create();

        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $freeType->id,
            'status' => 'free',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $affiliate = User::factory()->create([
                'sponsor_id' => $sponsor->id,
            ]);

            Membership::query()->create([
                'user_id' => $affiliate->id,
                'membership_type_id' => $customerType->id,
                'status' => 'active',
                'started_at' => now()->subDays(5),
            ]);
        }

        $response = $this
            ->withHeaders(['X-Internal-Token' => $this->token])
            ->postJson('/api/admin/memberships/recalculate-tiers');

        $response->assertOk();
        $response->assertJsonPath('meta.changed', 0);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $sponsor->id,
            'membership_type_id' => $freeType->id,
            'status' => 'free',
        ]);
    }

    public function test_it_promotes_active_customer_to_constructor_when_reaching_three_active_direct_affiliates(): void
    {
        $customerType = MembershipType::query()->create([
            'name' => 'customer',
            'affiliates_required' => 0,
            'cost' => 97,
            'profit' => 0,
        ]);

        MembershipType::query()->create([
            'name' => 'beginner',
            'affiliates_required' => 1,
            'cost' => 0,
            'profit' => 0,
        ]);

        $constructorType = MembershipType::query()->create([
            'name' => 'constructor',
            'affiliates_required' => 3,
            'cost' => 0,
            'profit' => 0,
        ]);

        $sponsor = User::factory()->create();

        Membership::query()->create([
            'user_id' => $sponsor->id,
            'membership_type_id' => $customerType->id,
            'status' => 'active',
            'started_at' => now()->subDays(10),
        ]);

        for ($i = 0; $i < 4; $i++) {
            $affiliate = User::factory()->create([
                'sponsor_id' => $sponsor->id,
            ]);

            Membership::query()->create([
                'user_id' => $affiliate->id,
                'membership_type_id' => $customerType->id,
                'status' => 'active',
                'started_at' => now()->subDays(5),
            ]);
        }

        $response = $this
            ->withHeaders(['X-Internal-Token' => $this->token])
            ->postJson('/api/admin/memberships/recalculate-tiers');

        $response->assertOk();
        $response->assertJsonPath('meta.changed', 1);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $sponsor->id,
            'membership_type_id' => $constructorType->id,
            'status' => 'active',
        ]);
    }
}
