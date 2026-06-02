<?php

namespace Tests\Feature\Admin;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BanksCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_view_banks_permission_can_open_banks_page(): void
    {
        $admin = $this->createAdminWithPermissions(['view banks']);

        $response = $this->actingAs($admin)->get(route('admin.banks.index'));

        $response->assertOk();
        $response->assertSee(__('messages.admin.banks.title'));
    }

    public function test_admin_can_create_bank_when_has_create_permission(): void
    {
        $admin = $this->createAdminWithPermissions(['create banks', 'view banks']);

        $response = $this->actingAs($admin)->post(route('admin.banks.store'), [
            'name' => 'Banco Test',
            'owner' => 'Titular Test',
            'identification' => 'ID-123',
            'number' => 'ACC-123',
            'amount' => 1250.50,
            'detail' => 'Banco para pagos de pruebas',
        ]);

        $response->assertRedirect(route('admin.banks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('banks', [
            'name' => 'Banco Test',
            'number' => 'ACC-123',
        ]);
    }

    public function test_admin_can_update_and_delete_bank_with_permissions(): void
    {
        $admin = $this->createAdminWithPermissions(['edit banks', 'delete banks', 'view banks']);

        $bank = Bank::query()->create([
            'name' => 'Banco Origen',
            'owner' => 'Owner Origen',
            'identification' => 'ID-OLD',
            'number' => 'ACC-OLD',
            'amount' => 100,
            'detail' => null,
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.banks.update', $bank), [
            'name' => 'Banco Editado',
            'owner' => 'Owner Editado',
            'identification' => 'ID-NEW',
            'number' => 'ACC-NEW',
            'amount' => 500,
            'detail' => 'Detalle editado',
        ]);

        $updateResponse->assertRedirect(route('admin.banks.index'));
        $updateResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('banks', [
            'id' => $bank->id,
            'name' => 'Banco Editado',
            'number' => 'ACC-NEW',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.banks.destroy', $bank));

        $deleteResponse->assertRedirect(route('admin.banks.index'));
        $this->assertDatabaseMissing('banks', [
            'id' => $bank->id,
        ]);
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

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
