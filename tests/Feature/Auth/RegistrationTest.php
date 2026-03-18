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
