<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Users are only ever deleted by hand: the system records how long they have
 * been away so the admin can decide, and never deletes them on its own.
 */
class UserInactivityTest extends TestCase
{
    use RefreshDatabase;

    private int $phoneCounter = 0;

    public function test_the_old_automatic_clean_up_no_longer_deletes_anyone(): void
    {
        config(['affiliates.internal_api_token' => 'secret-token']);

        $longGone = $this->user(['last_seen_at' => now()->subYear(), 'created_at' => now()->subYears(2)]);
        $longGone->assignRole(Role::findOrCreate('user', 'web'));

        $this->postJson(route('api.admin.users.prune-inactive'), ['months' => 1], ['X-Internal-Token' => 'secret-token'])
            ->assertOk()
            ->assertJsonPath('data.deleted', 0);

        $this->assertModelExists($longGone);
    }

    public function test_browsing_while_logged_in_records_when_the_user_was_last_seen(): void
    {
        $user = $this->user(['last_seen_at' => null]);

        $this->actingAs($user)->get(route('dashboard'));

        $this->assertNotNull($user->refresh()->last_seen_at);
        $this->assertTrue($user->last_seen_at->isToday());
    }

    public function test_the_last_seen_time_is_not_rewritten_on_every_request(): void
    {
        $recently = now()->subMinutes(2)->startOfSecond();
        $user = $this->user(['last_seen_at' => $recently]);
        $updatedAt = $user->updated_at;

        $this->actingAs($user)->get(route('dashboard'));

        $user->refresh();
        $this->assertTrue($user->last_seen_at->equalTo($recently));
        $this->assertTrue($user->updated_at->equalTo($updatedAt));
    }

    public function test_the_users_table_shows_how_long_each_user_has_been_inactive(): void
    {
        $role = Role::findOrCreate('admin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('view users', 'web'));
        $admin = $this->user();
        $admin->assignRole($role);

        $this->user(['name' => 'Usuario Ausente', 'last_seen_at' => now()->subDays(120)]);
        $this->user(['name' => 'Usuario Nunca Visto', 'last_seen_at' => null]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(__('messages.admin.users.last_seen'))
            ->assertSee(__('messages.admin.users.inactive_days', ['days' => 120]))
            ->assertSee(__('messages.admin.users.never_seen'));
    }

    /** @param  array<string, mixed>  $attributes */
    private function user(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'phone' => '+5939300'.str_pad((string) ++$this->phoneCounter, 4, '0', STR_PAD_LEFT),
        ]);
    }
}
