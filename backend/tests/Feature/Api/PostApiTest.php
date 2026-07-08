<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('r2');
    }

    // --- GET /api/posts ---

    public function test_posts_index_returns_only_published(): void
    {
        Post::factory()->published()->create(['title' => 'Opublikowany']);
        Post::factory()->create(['title' => 'Szkic', 'published_at' => null]);
        Post::factory()->create(['title' => 'Przyszły', 'published_at' => now()->addDay()]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.title', 'Opublikowany');
    }

    public function test_posts_index_returns_excerpt_not_content(): void
    {
        Post::factory()->published()->create([
            'content' => '<p>' . str_repeat('a', 500) . '</p>',
        ]);

        $response = $this->getJson('/api/posts');

        $data = $response->json('0');
        $this->assertArrayHasKey('excerpt', $data);
        $this->assertArrayNotHasKey('content', $data);
        $this->assertLessThanOrEqual(303, strlen($data['excerpt']));
    }

    public function test_posts_index_returns_required_fields(): void
    {
        Post::factory()->published()->create();

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonStructure([['id', 'title', 'slug', 'cover_image', 'excerpt', 'published_at']]);
    }

    public function test_posts_index_ordered_by_published_at_desc(): void
    {
        Post::factory()->published()->create(['title' => 'Starszy', 'published_at' => now()->subDays(2)]);
        Post::factory()->published()->create(['title' => 'Nowszy',  'published_at' => now()->subDay()]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
                 ->assertJsonPath('0.title', 'Nowszy')
                 ->assertJsonPath('1.title', 'Starszy');
    }

    // --- GET /api/posts/{slug} ---

    public function test_posts_show_returns_full_post(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->getJson("/api/posts/{$post->slug}");

        $response->assertOk()
                 ->assertJsonStructure(['id', 'title', 'slug', 'cover_image', 'content', 'published_at', 'images'])
                 ->assertJsonPath('slug', $post->slug);
    }

    public function test_posts_show_returns_images_ordered(): void
    {
        $post = Post::factory()->published()->create();
        PostImage::factory()->create(['post_id' => $post->id, 'path' => 'img2.jpg', 'order' => 1]);
        PostImage::factory()->create(['post_id' => $post->id, 'path' => 'img1.jpg', 'order' => 0]);

        $response = $this->getJson("/api/posts/{$post->slug}");

        $images = $response->json('images');
        $this->assertCount(2, $images);
        $this->assertStringContainsString('img1.jpg', $images[0]['path']);
        $this->assertStringContainsString('img2.jpg', $images[1]['path']);
    }

    public function test_posts_show_returns_404_for_draft(): void
    {
        $post = Post::factory()->create(['published_at' => null]);

        $this->getJson("/api/posts/{$post->slug}")->assertNotFound();
    }

    public function test_posts_show_returns_404_for_nonexistent_slug(): void
    {
        $this->getJson('/api/posts/nie-istnieje')->assertNotFound();
    }
}
