<?php

namespace Tests\Feature\Admin;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TestimonialsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_testimonial_with_edit_landing_content_permission(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $response = $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name_es' => 'Alumno Test',
            'name_en' => 'Test Student',
            'quote_es' => 'Testimonio de prueba.',
            'quote_en' => 'Test testimonial.',
            'media_type' => 'image',
            'photo' => UploadedFile::fake()->image('testimonio.jpg'),
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.landing-content.index'));
        $response->assertSessionHasNoErrors();

        $testimonial = Testimonial::query()->where('name_es', 'Alumno Test')->firstOrFail();

        $this->assertSame(5, $testimonial->sort_order);
        Storage::disk('public')->assertExists($testimonial->photo_path);
    }

    public function test_admin_can_create_testimonial_with_video(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $response = $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name_es' => 'Alumno Video',
            'name_en' => 'Video Student',
            'quote_es' => 'Testimonio en video.',
            'quote_en' => 'Video testimonial.',
            'media_type' => 'video',
            'video' => UploadedFile::fake()->create('testimonio.mp4', 2048, 'video/mp4'),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.landing-content.index'));
        $response->assertSessionHasNoErrors();

        $testimonial = Testimonial::query()->where('name_es', 'Alumno Video')->firstOrFail();

        $this->assertSame('video', $testimonial->media_type);
        $this->assertNull($testimonial->photo_path);
        Storage::disk('public')->assertExists($testimonial->video_path);
    }

    public function test_creating_testimonial_without_matching_media_type_file_fails(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $response = $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name_es' => 'Alumno Sin Media',
            'name_en' => 'No Media Student',
            'quote_es' => 'Testimonio sin archivo.',
            'quote_en' => 'Testimonial without file.',
            'media_type' => 'video',
        ]);

        $response->assertSessionHasErrors('video');
        $this->assertDatabaseMissing('testimonials', ['name_es' => 'Alumno Sin Media']);
    }

    public function test_admin_can_update_and_delete_testimonial_with_permission(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $originalPhoto = UploadedFile::fake()->image('original.jpg')->store('testimonios', 'public');

        $testimonial = Testimonial::query()->create([
            'name_es' => 'Nombre Original',
            'name_en' => 'Original Name',
            'quote_es' => 'Cita original.',
            'quote_en' => 'Original quote.',
            'photo_path' => $originalPhoto,
            'media_type' => 'image',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.testimonials.update', $testimonial), [
            'name_es' => 'Nombre Editado',
            'name_en' => 'Edited Name',
            'quote_es' => 'Cita editada.',
            'quote_en' => 'Edited quote.',
            'media_type' => 'image',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $updateResponse->assertRedirect(route('admin.landing-content.index'));
        $updateResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'name_es' => 'Nombre Editado',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        // Photo untouched when no new file is sent.
        Storage::disk('public')->assertExists($originalPhoto);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.testimonials.destroy', $testimonial));

        $deleteResponse->assertRedirect(route('admin.landing-content.index'));
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
        Storage::disk('public')->assertMissing($originalPhoto);
    }

    public function test_user_without_permission_cannot_manage_testimonials(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['phone' => '+34123456789']);

        $response = $this->actingAs($user)->post(route('admin.testimonials.store'), [
            'name_es' => 'Alumno Test',
            'name_en' => 'Test Student',
            'quote_es' => 'Testimonio de prueba.',
            'quote_en' => 'Test testimonial.',
            'photo' => UploadedFile::fake()->image('testimonio.jpg'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('testimonials', ['name_es' => 'Alumno Test']);
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
