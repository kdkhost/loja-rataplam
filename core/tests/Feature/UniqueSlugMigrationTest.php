<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class UniqueSlugMigrationTest extends TestCase
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

    public function test_migration_aborts_when_slug_is_null()
    {
        // Insert a post with NULL slug bypass model events/validation if needed
        DB::table('posts')->insert([
            'title' => 'Test Title',
            'slug' => null,
            'details' => 'Content',
            'category_id' => 1
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('posts com slug NULL');

        $migration = require database_path('migrations/2026_07_31_132933_add_unique_slug_index_to_posts_table.php');
        $migration->up();
    }

    public function test_migration_aborts_when_slug_is_empty()
    {
        DB::table('posts')->insert([
            'title' => 'Test Title',
            'slug' => '',
            'details' => 'Content',
            'category_id' => 1
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('slug vazio ou composto apenas por espaços');

        $migration = require database_path('migrations/2026_07_31_132933_add_unique_slug_index_to_posts_table.php');
        $migration->up();
    }

    public function test_migration_aborts_when_slug_is_only_spaces()
    {
        DB::table('posts')->insert([
            'title' => 'Test Title',
            'slug' => '   ',
            'details' => 'Content',
            'category_id' => 1
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('slug vazio ou composto apenas por espaços');

        $migration = require database_path('migrations/2026_07_31_132933_add_unique_slug_index_to_posts_table.php');
        $migration->up();
    }

    public function test_migration_aborts_when_slugs_are_identical()
    {
        DB::table('posts')->insert([
            [
                'title' => 'Test Title 1',
                'slug' => 'duplicate-slug',
                'details' => 'Content 1',
                'category_id' => 1
            ],
            [
                'title' => 'Test Title 2',
                'slug' => 'duplicate-slug',
                'details' => 'Content 2',
                'category_id' => 1
            ]
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('slugs duplicados na tabela posts');

        $migration = require database_path('migrations/2026_07_31_132933_add_unique_slug_index_to_posts_table.php');
        $migration->up();
    }

    public function test_migration_succeeds_when_base_is_valid()
    {
        DB::table('posts')->insert([
            [
                'title' => 'Test Title 1',
                'slug' => 'slug-1',
                'details' => 'Content 1',
                'category_id' => 1
            ],
            [
                'title' => 'Test Title 2',
                'slug' => 'slug-2',
                'details' => 'Content 2',
                'category_id' => 1
            ]
        ]);

        $migration = require database_path('migrations/2026_07_31_132933_add_unique_slug_index_to_posts_table.php');
        $migration->up();

        // If it reaches here, no exception was thrown, meaning migration succeeded
        $this->assertTrue(true);

        // Rollback just in case
        $migration->down();
    }
}
