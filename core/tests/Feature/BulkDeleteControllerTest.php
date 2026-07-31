<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Services\BlogFileService;

class BulkDeleteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->markTestSkipped('MariaDB indisponível no ambiente local.');
        }
    }

    public function test_bulk_delete_posts_continues_when_id_not_found()
    {
        $this->withoutMiddleware();

        $post1 = Post::create([
            'title' => 'Test Post 1',
            'slug' => 'test-post-1',
            'details' => 'Content 1',
            'category_id' => 1,
            'photo' => 'photo1.jpg'
        ]);

        $post2 = Post::create([
            'title' => 'Test Post 2',
            'slug' => 'test-post-2',
            'details' => 'Content 2',
            'category_id' => 1,
            'photo' => 'photo2.jpg'
        ]);

        $invalidId = 9999;

        $response = $this->post(route('back.bulk.delete'), [
            'ids' => [$post1->id . ',' . $invalidId . ',' . $post2->id],
            'table' => 'posts'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('posts', ['id' => $post1->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post2->id]);
    }

    public function test_bulk_delete_posts_continues_when_image_deletion_fails()
    {
        $this->withoutMiddleware();

        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post-3',
            'details' => 'Content',
            'category_id' => 1,
            'photo' => 'photo3.jpg'
        ]);

        // Simula falha na exclusão da imagem chamando o BlogFileService (mock)
        $diskMock = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $diskMock->shouldReceive('exists')->andReturn(true);
        $diskMock->shouldReceive('delete')->andReturn(false);
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')->with('public')->andReturn($diskMock);

        \Illuminate\Support\Facades\File::shouldReceive('exists')->andReturn(true);
        \Illuminate\Support\Facades\File::shouldReceive('delete')->andReturn(false);

        $response = $this->post(route('back.bulk.delete'), [
            'ids' => [$post->id],
            'table' => 'posts'
        ]);

        $response->assertStatus(302);

        // Post deve ser excluído mesmo se a exclusão da imagem falhar
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
