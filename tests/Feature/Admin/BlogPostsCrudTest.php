<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogPostsCrudTest extends TestCase
{
    use RefreshDatabase;

    /** Phone numbers are unique, so each test user gets its own. */
    private int $phoneCounter = 0;

    private function uniquePhone(): string
    {
        return '+3412345'.str_pad((string) ++$this->phoneCounter, 4, '0', STR_PAD_LEFT);
    }

    public function test_content_admin_page_shows_the_blog_section_with_its_posts(): void
    {
        $admin = $this->createAdminWithPermissions(['view landing_content', 'edit landing_content']);

        $this->createPost(['title_es' => 'Entrada listada', 'author_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.landing-content.index'));

        $response->assertOk();
        $response->assertSee(__('messages.admin.blog.heading'));
        $response->assertSee('Entrada listada');
        $response->assertSee('blog-posts-data', false);
    }

    public function test_admin_can_create_post_with_cover(): void
    {
        Storage::fake('public');

        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $response = $this->actingAs($admin)->post(route('admin.blog.store'), [
            'title_es' => 'VPS para trading',
            'body_es' => "## Qué incluye\n\nUn servidor **siempre encendido**.",
            'excerpt_es' => 'Servidores para tu MetaTrader.',
            'cover' => UploadedFile::fake()->image('vps.jpg'),
            'is_published' => '1',
            'sort_order' => 1,
        ]);

        $response->assertSessionHasNoErrors();

        $post = BlogPost::query()->where('title_es', 'VPS para trading')->firstOrFail();

        $this->assertSame('vps-para-trading', $post->slug);
        $this->assertSame($admin->id, $post->author_id);
        $this->assertNotNull($post->cover_image_path);
        Storage::disk('public')->assertExists($post->cover_image_path);
    }

    public function test_admin_can_update_and_delete_any_post(): void
    {
        $admin = $this->createAdminWithPermissions(['edit landing_content']);
        $writer = $this->createWriter();

        $post = $this->createPost(['author_id' => $writer->id]);

        $this->actingAs($admin)->put(route('admin.blog.update', $post), [
            'title_es' => 'Título editado',
            'body_es' => 'Contenido editado.',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Título editado', $post->refresh()->title_es);

        $this->actingAs($admin)->delete(route('admin.blog.destroy', $post));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_writer_can_manage_own_posts_but_not_someone_elses(): void
    {
        $writer = $this->createWriter();
        $otherWriter = $this->createWriter();

        $ownPost = $this->createPost(['slug' => 'mia', 'author_id' => $writer->id]);
        $foreignPost = $this->createPost(['slug' => 'ajena', 'author_id' => $otherWriter->id]);

        $this->actingAs($writer)->put(route('admin.blog.update', $ownPost), [
            'title_es' => 'Editada por su autor',
            'body_es' => 'Contenido.',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Editada por su autor', $ownPost->refresh()->title_es);

        $this->actingAs($writer)->put(route('admin.blog.update', $foreignPost), [
            'title_es' => 'Intento de edición ajena',
            'body_es' => 'Contenido.',
            'is_published' => '1',
        ])->assertForbidden();

        $this->actingAs($writer)->delete(route('admin.blog.destroy', $foreignPost))->assertForbidden();

        $this->assertDatabaseHas('blog_posts', ['id' => $foreignPost->id]);
    }

    public function test_writer_page_lists_only_their_own_posts(): void
    {
        $writer = $this->createWriter();
        $otherWriter = $this->createWriter();

        $this->createPost(['slug' => 'mia', 'title_es' => 'Entrada propia', 'author_id' => $writer->id]);
        $this->createPost(['slug' => 'ajena', 'title_es' => 'Entrada ajena', 'author_id' => $otherWriter->id]);

        $response = $this->actingAs($writer)->get(route('admin.blog.index'));

        $response->assertOk();
        $response->assertSee('Entrada propia');
        $response->assertDontSee('Entrada ajena');
    }

    public function test_writer_can_publish_a_post_that_shows_on_the_public_blog(): void
    {
        $writer = $this->createWriter();

        $this->actingAs($writer)->post(route('admin.blog.store'), [
            'title_es' => 'Entrada del redactor',
            'body_es' => 'Contenido del redactor.',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $post = BlogPost::query()->where('title_es', 'Entrada del redactor')->firstOrFail();
        $this->assertSame($writer->id, $post->author_id);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('Entrada del redactor')
            ->assertSee($writer->name);
    }

    public function test_user_without_any_blog_role_is_blocked(): void
    {
        $user = User::factory()->create(['phone' => $this->uniquePhone()]);

        $this->actingAs($user)->get(route('admin.blog.index'))->assertForbidden();

        $this->actingAs($user)->post(route('admin.blog.store'), [
            'title_es' => 'Bloqueado',
            'body_es' => 'No debería guardarse.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('blog_posts', ['title_es' => 'Bloqueado']);
    }

    public function test_slug_collisions_get_a_suffix(): void
    {
        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $this->createPost(['title_es' => 'Mismo título', 'slug' => 'mismo-titulo']);

        $this->actingAs($admin)->post(route('admin.blog.store'), [
            'title_es' => 'Mismo título',
            'body_es' => 'Otro contenido.',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('blog_posts', ['slug' => 'mismo-titulo-2']);
    }

    public function test_admin_can_grant_and_revoke_the_writer_role(): void
    {
        $admin = $this->createAdminWithPermissions(['edit users']);
        Role::query()->firstOrCreate(['name' => 'blogger', 'guard_name' => 'web']);

        $user = User::factory()->create(['phone' => $this->uniquePhone()]);

        $this->actingAs($admin)
            ->postJson(route('admin.users.update-role', $user), ['action' => 'make_blogger'])
            ->assertOk()
            ->assertJson(['is_blogger' => true]);

        $this->assertTrue($user->fresh()->hasRole('blogger'));

        $this->actingAs($admin)
            ->postJson(route('admin.users.update-role', $user), ['action' => 'remove_blogger'])
            ->assertOk()
            ->assertJson(['is_blogger' => false]);

        $this->assertFalse($user->fresh()->hasRole('blogger'));
    }

    private function createPost(array $attributes = []): BlogPost
    {
        return BlogPost::query()->create(array_merge([
            'slug' => 'entrada-de-prueba',
            'title_es' => 'Entrada de prueba',
            'body_es' => 'Contenido de prueba.',
            'is_published' => true,
            'published_at' => now(),
            'sort_order' => 0,
        ], $attributes));
    }

    private function createWriter(): User
    {
        Role::query()->firstOrCreate(['name' => 'blogger', 'guard_name' => 'web']);

        $user = User::factory()->create(['phone' => $this->uniquePhone()]);
        $user->assignRole('blogger');

        return $user;
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

        $user = User::factory()->create(['phone' => $this->uniquePhone()]);
        $user->assignRole($role);

        return $user;
    }
}
