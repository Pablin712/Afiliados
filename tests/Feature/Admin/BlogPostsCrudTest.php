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

    public function test_content_admin_page_shows_the_blog_section_with_its_posts(): void
    {
        $admin = $this->createAdminWithPermissions(['view landing_content', 'edit landing_content']);

        $post = $this->createPost(['title_es' => 'Entrada listada']);
        $post->prices()->create(['region' => 'LATAM', 'price' => 10, 'currency' => 'USD', 'sort_order' => 0]);

        $response = $this->actingAs($admin)->get(route('admin.landing-content.index'));

        $response->assertOk();
        $response->assertSee(__('messages.admin.blog.heading'));
        $response->assertSee('Entrada listada');
        $response->assertSee('LATAM');
        $response->assertSee('blog-posts-data', false);
    }

    public function test_admin_can_create_post_with_cover_and_prices_per_region(): void
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
            'prices' => [
                ['region' => 'EE.UU.', 'item' => 'VPS Básico', 'price' => '25', 'currency' => 'USD', 'note' => 'mensual'],
                ['region' => 'LATAM', 'item' => 'VPS Básico', 'price' => '18.50', 'currency' => 'USD', 'note' => 'mensual'],
                ['region' => '', 'item' => '', 'price' => '', 'currency' => 'USD', 'note' => ''],
            ],
        ]);

        $response->assertRedirect(route('admin.landing-content.index'));
        $response->assertSessionHasNoErrors();

        $post = BlogPost::query()->where('title_es', 'VPS para trading')->firstOrFail();

        $this->assertSame('vps-para-trading', $post->slug);
        $this->assertNotNull($post->cover_image_path);
        Storage::disk('public')->assertExists($post->cover_image_path);

        // The trailing blank row of the price editor is dropped, not saved.
        $this->assertCount(2, $post->prices);
        $this->assertSame('$25.00', $post->prices->firstWhere('region', 'EE.UU.')->formattedPrice());
        $this->assertSame('$18.50', $post->prices->firstWhere('region', 'LATAM')->formattedPrice());
    }

    public function test_admin_can_update_post_and_replace_its_prices(): void
    {
        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $post = $this->createPost();
        $post->prices()->create([
            'region' => 'LATAM',
            'price' => 10,
            'currency' => 'USD',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.blog.update', $post), [
            'title_es' => 'Título editado',
            'body_es' => 'Contenido editado.',
            'is_published' => '1',
            'prices' => [
                ['region' => 'Europa', 'item' => null, 'price' => '30', 'currency' => 'EUR', 'note' => null],
            ],
        ]);

        $response->assertRedirect(route('admin.landing-content.index'));
        $response->assertSessionHasNoErrors();

        $post->refresh()->load('prices');

        $this->assertSame('Título editado', $post->title_es);
        $this->assertCount(1, $post->prices);
        $this->assertSame('€30.00', $post->prices->first()->formattedPrice());
    }

    public function test_admin_can_delete_post_and_its_prices_go_with_it(): void
    {
        $admin = $this->createAdminWithPermissions(['edit landing_content']);

        $post = $this->createPost();
        $post->prices()->create(['region' => 'LATAM', 'price' => 10, 'currency' => 'USD', 'sort_order' => 0]);

        $response = $this->actingAs($admin)->delete(route('admin.blog.destroy', $post));

        $response->assertRedirect(route('admin.landing-content.index'));
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('blog_post_prices', ['blog_post_id' => $post->id]);
    }

    public function test_user_without_permission_cannot_create_post(): void
    {
        $user = User::factory()->create(['phone' => '+34123456789']);

        $response = $this->actingAs($user)->post(route('admin.blog.store'), [
            'title_es' => 'Bloqueado',
            'body_es' => 'No debería guardarse.',
        ]);

        $response->assertForbidden();
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
