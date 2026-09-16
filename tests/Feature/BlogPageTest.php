<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_lists_published_posts_only(): void
    {
        $this->createPost(['title_es' => 'Publicada', 'slug' => 'publicada']);
        $this->createPost(['title_es' => 'Borrador', 'slug' => 'borrador', 'is_published' => false]);
        $this->createPost([
            'title_es' => 'Programada',
            'slug' => 'programada',
            'published_at' => now()->addWeek(),
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('Publicada');
        $response->assertDontSee('Borrador');
        $response->assertDontSee('Programada');
    }

    public function test_post_page_renders_markdown_body_and_regional_prices(): void
    {
        $post = $this->createPost([
            'title_es' => 'VPS para trading',
            'slug' => 'vps-para-trading',
            'body_es' => "## Qué incluye\n\nUn servidor **siempre encendido**.",
        ]);

        $post->prices()->createMany([
            ['region' => 'EE.UU.', 'item' => 'VPS Básico', 'price' => 25, 'currency' => 'USD', 'sort_order' => 0],
            ['region' => 'LATAM', 'item' => 'VPS Básico', 'price' => 18.5, 'currency' => 'USD', 'sort_order' => 1],
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('<h2>Qué incluye</h2>', false);
        $response->assertSee('<strong>siempre encendido</strong>', false);
        $response->assertSee('EE.UU.');
        $response->assertSee('$25.00');
        $response->assertSee('LATAM');
        $response->assertSee('$18.50');
    }

    public function test_html_typed_into_the_body_is_escaped(): void
    {
        $post = $this->createPost([
            'slug' => 'con-html',
            'body_es' => '<script>alert(1)</script>',
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_unpublished_post_returns_404(): void
    {
        $post = $this->createPost(['slug' => 'oculta', 'is_published' => false]);

        $this->get(route('blog.show', $post->slug))->assertNotFound();
    }

    public function test_landing_page_links_to_the_blog(): void
    {
        $this->seed(\Database\Seeders\LandingContentSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('blog.index'), false);
    }

    private function createPost(array $attributes = []): BlogPost
    {
        return BlogPost::query()->create(array_merge([
            'slug' => 'entrada-de-prueba',
            'title_es' => 'Entrada de prueba',
            'body_es' => 'Contenido de prueba.',
            'is_published' => true,
            'published_at' => now()->subDay(),
            'sort_order' => 0,
        ], $attributes));
    }
}
