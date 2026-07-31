<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BlogSlugService;

class BlogSlugServiceTest extends TestCase
{
    public function test_generates_slug_for_normal_title()
    {
        $resolver = function () { return false; }; // No collisions
        
        $title = 'Test Title For Slug';
        $slug = BlogSlugService::generateSlug($title, null, $resolver);
        $this->assertEquals('test-title-for-slug', $slug);
    }

    public function test_throws_exception_for_empty_base_slug()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $resolver = function () { return false; }; // Prevent DB connection
        
        $title = '    '; // Will result in empty base slug
        BlogSlugService::generateSlug($title, null, $resolver);
    }

    public function test_generates_unique_slug_when_collision_exists()
    {
        $existingSlugs = ['test-title'];
        
        $resolver = function (string $slug, ?int $ignoreId) use ($existingSlugs) {
            return in_array($slug, $existingSlugs);
        };

        $slug = BlogSlugService::generateSlug('Test Title', null, $resolver);
        $this->assertEquals('test-title-2', $slug);
    }

    public function test_generates_unique_slug_when_multiple_collisions_exist()
    {
        $existingSlugs = ['test-title', 'test-title-2', 'test-title-3'];
        
        $resolver = function (string $slug, ?int $ignoreId) use ($existingSlugs) {
            return in_array($slug, $existingSlugs);
        };

        $slug = BlogSlugService::generateSlug('Test Title', null, $resolver);
        $this->assertEquals('test-title-4', $slug);
    }

    public function test_generates_unique_slug_ignoring_own_id()
    {
        $existingData = [
            ['id' => 1, 'slug' => 'test-title'],
            ['id' => 2, 'slug' => 'other-title'],
        ];
        
        $resolver = function (string $slug, ?int $ignoreId) use ($existingData) {
            foreach ($existingData as $data) {
                if ($data['slug'] === $slug && $data['id'] !== $ignoreId) {
                    return true; // Collides with someone else
                }
            }
            return false;
        };

        // Ignoring ID 1, it should allow 'test-title'
        $slug = BlogSlugService::generateSlug('Test Title', 1, $resolver);
        $this->assertEquals('test-title', $slug);
    }
}
