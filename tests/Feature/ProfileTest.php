<?php

namespace Tests\Feature;

use App\Models\UserBank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_can_create_binance_user_bank_data(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'binance_account_id' => 'BN-ACC-1001',
                'binance_username' => 'binance_user_01',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $bank = $user->refresh()->userBanks()->first();

        $this->assertNotNull($bank);
        $this->assertSame('Binance', $bank->bank_name);
        $this->assertSame('binance', $bank->type);
        $this->assertSame('BN-ACC-1001', $bank->identification);
        $this->assertSame('BN-ACC-1001', $bank->number);
        $this->assertSame('binance_user_01', $bank->owner);
        $this->assertTrue($bank->is_default);
    }

    public function test_profile_can_update_existing_single_user_bank_data(): void
    {
        $user = User::factory()->create();

        UserBank::query()->create([
            'user_id' => $user->id,
            'bank_name' => 'Binance',
            'owner' => 'old_user',
            'identification' => 'OLD-ACC',
            'number' => 'OLD-ACC',
            'type' => 'binance',
            'is_default' => true,
            'detail' => 'old detail',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'binance_account_id' => 'NEW-ACC-2026',
                'binance_username' => 'new_binance_user',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame(1, $user->refresh()->userBanks()->count());

        $bank = $user->userBanks()->first();
        $this->assertNotNull($bank);
        $this->assertSame('NEW-ACC-2026', $bank->identification);
        $this->assertSame('new_binance_user', $bank->owner);
    }

    public function test_profile_rejects_duplicate_binance_account_id(): void
    {
        $ownerUser = User::factory()->create();
        $anotherUser = User::factory()->create();

        UserBank::query()->create([
            'user_id' => $ownerUser->id,
            'bank_name' => 'Binance',
            'owner' => 'existing_owner',
            'identification' => 'DUPL-ACC-100',
            'number' => 'DUPL-ACC-100',
            'type' => 'binance',
            'is_default' => true,
            'detail' => 'existing detail',
        ]);

        $response = $this
            ->actingAs($anotherUser)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $anotherUser->name,
                'email' => $anotherUser->email,
                'binance_account_id' => 'DUPL-ACC-100',
                'binance_username' => 'new_owner_attempt',
            ]);

        $response
            ->assertSessionHasErrors('binance_account_id')
            ->assertRedirect('/profile');

        $this->assertSame(0, $anotherUser->fresh()->userBanks()->count());
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
