<?php

namespace Tests\Feature\User;

use App\Models\Profit;
use App\Models\User;
use App\Models\UserBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAffiliateViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_and_user_views_are_available(): void
    {
        $this->ensureUserRole();

        $sponsor = User::factory()->create();
        $user = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(__('messages.user.dashboard.subtitle'));
        $response->assertSee(route('user.network.index'));
        $response->assertSee(route('user.profits.index'));
    }

    public function test_user_network_insights_hide_sponsor_affiliates(): void
    {
        $this->ensureUserRole();

        $sponsor = User::factory()->create();
        $user = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);
        $user->assignRole('user');
        $sponsorAffiliate = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('user.network.insights', $sponsor));

        $response->assertOk();
        $response->assertJsonPath('data.scope.relation', 'sponsor');
        $response->assertJsonPath('data.affiliates', []);
        $response->assertJsonMissing(['id' => $sponsorAffiliate->id]);
    }

    public function test_user_only_sees_own_profits(): void
    {
        $this->ensureUserRole();

        $sponsor = User::factory()->create();
        $user = User::factory()->create([
            'sponsor_id' => $sponsor->id,
        ]);
        $user->assignRole('user');
        $other = User::factory()->create();

        $userBank = UserBank::query()->create([
            'user_id' => $user->id,
            'bank_name' => 'Banco usuario',
            'owner' => $user->name,
            'identification' => 'USR-001',
            'number' => '100200300',
            'type' => 'binance',
            'is_default' => true,
        ]);

        $otherBank = UserBank::query()->create([
            'user_id' => $other->id,
            'bank_name' => 'Banco otro',
            'owner' => $other->name,
            'identification' => 'OTH-001',
            'number' => '400500600',
            'type' => 'binance',
            'is_default' => true,
        ]);

        Profit::query()->create([
            'user_id' => $user->id,
            'user_bank_id' => $userBank->id,
            'period_month' => now()->startOfMonth()->toDateString(),
            'amount' => 15,
            'state' => 'pending',
            'detail' => 'Own profit',
        ]);

        Profit::query()->create([
            'user_id' => $other->id,
            'user_bank_id' => $otherBank->id,
            'period_month' => now()->startOfMonth()->toDateString(),
            'amount' => 99,
            'state' => 'pending',
            'detail' => 'Other profit',
        ]);

        $response = $this->actingAs($user)->get(route('user.profits.index'));

        $response->assertOk();
        $response->assertSee('Own profit');
        $response->assertDontSee('Other profit');
    }

    protected function ensureUserRole(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);
    }
}
