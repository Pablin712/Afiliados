<?php

namespace Tests\Feature\Admin;

use App\Models\HeroButton;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeroButtonsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_hero_button_with_edit_landing_content_permission(): void
    {
        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $response = $this->actingAs($admin)->post(route('admin.hero-buttons.store'), [
            'label_es' => 'Habla con nosotros',
            'label_en' => 'Talk to us',
            'url' => 'https://wa.me/593978855098',
            'style' => 'primary',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.landing-content.index'));
        $response->assertSessionHasNoErrors();

        $button = HeroButton::query()->where('label_es', 'Habla con nosotros')->firstOrFail();
        $this->assertSame(5, $button->sort_order);
        $this->assertSame('primary', $button->style);
        $this->assertTrue($button->opensExternally());
    }

    public function test_admin_can_update_and_delete_hero_button_with_permission(): void
    {
        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $button = HeroButton::query()->create([
            'label_es' => 'Original',
            'label_en' => 'Original',
            'url' => '#programas',
            'style' => 'primary',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertFalse($button->opensExternally());

        $updateResponse = $this->actingAs($admin)->put(route('admin.hero-buttons.update', $button), [
            'label_es' => 'Editado',
            'label_en' => 'Edited',
            'url' => 'https://example.com/promo',
            'style' => 'accent',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $updateResponse->assertRedirect(route('admin.landing-content.index'));
        $updateResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('hero_buttons', [
            'id' => $button->id,
            'label_es' => 'Editado',
            'style' => 'accent',
            'is_active' => false,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.hero-buttons.destroy', $button));

        $deleteResponse->assertRedirect(route('admin.landing-content.index'));
        $this->assertDatabaseMissing('hero_buttons', ['id' => $button->id]);
    }

    public function test_user_without_permission_cannot_manage_hero_buttons(): void
    {
        $user = User::factory()->create(['phone' => '+34123456789']);

        $response = $this->actingAs($user)->post(route('admin.hero-buttons.store'), [
            'label_es' => 'Bloqueado',
            'label_en' => 'Blocked',
            'url' => '#contacto',
            'style' => 'primary',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('hero_buttons', ['label_es' => 'Bloqueado']);
    }

    /**
     * @param list<string> $permissions
     */
    protected function createAdminWithPermissions(array $permissions): User
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $role->syncPermissions($permissions);

        $user = User::factory()->create(['phone' => '+34123456789']);
        $user->assignRole($role);

        return $user;
    }
}
