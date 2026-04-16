<?php

namespace Tests\Feature\Auth;

use App\Models\Membership;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->createSponsor();

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_free_membership_without_creating_pending_payments(): void
    {
        $sponsor = $this->createSponsor();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+593987654321',
            'identification' => 'V-12345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sponsor_id' => $sponsor->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $membership = Membership::query()->with('membershipType')->where('user_id', $user->id)->first();

        $this->assertNotNull($user->approved_at);
        $this->assertTrue($user->hasRole('user'));
        $this->assertNotNull($membership);
        $this->assertSame('free', $membership?->status);
        $this->assertSame('free', $membership?->membershipType?->name);
        $this->assertDatabaseMissing('payments', ['user_id' => $user->id]);
    }

    public function test_new_user_can_register_through_referral_code(): void
    {
        $sponsor = $this->createSponsor();

        $sponsor->forceFill([
            'affiliate_code' => User::buildAffiliateCode($sponsor->name, $sponsor->id),
        ])->save();

        $response = $this->post('/register', [
            'name' => 'Referral User',
            'email' => 'referral@example.com',
            'phone' => '+593912345678',
            'identification' => 'V-87654321',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sponsor_id' => $sponsor->id,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'referral@example.com')->firstOrFail();
        $this->assertSame($sponsor->id, $user->sponsor_id);
    }

    private function createSponsor(): User
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $sponsor = User::factory()->create([
            'name' => 'Sponsor Admin',
            'email' => 'sponsor@example.com',
            'identification' => 'J-00000001',
        ]);

        $sponsor->forceFill([
            'sponsor_id' => $sponsor->id,
        ])->save();

        $sponsor->assignRole('admin');

        return $sponsor;
    }
}
