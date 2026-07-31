<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BlogFileService;
use Illuminate\Support\Facades\Storage;

class BlogFileServiceTest extends TestCase
{
    // --- isSafeFilename tests (pure, no Storage needed) ---

    public function test_is_safe_filename_rejects_null()
    {
        $this->assertFalse(BlogFileService::isSafeFilename(null));
    }

    public function test_is_safe_filename_rejects_empty_string()
    {
        $this->assertFalse(BlogFileService::isSafeFilename(''));
        $this->assertFalse(BlogFileService::isSafeFilename('   '));
    }

    public function test_is_safe_filename_rejects_path_traversal_dot_dot()
    {
        $this->assertFalse(BlogFileService::isSafeFilename('../secret.txt'));
        $this->assertFalse(BlogFileService::isSafeFilename('..\\etc\\passwd'));
    }

    public function test_is_safe_filename_rejects_backslash()
    {
        $this->assertFalse(BlogFileService::isSafeFilename('sub\\image.jpg'));
    }

    public function test_is_safe_filename_rejects_forward_slash()
    {
        $this->assertFalse(BlogFileService::isSafeFilename('dir/image.jpg'));
    }

    public function test_is_safe_filename_rejects_url()
    {
        $this->assertFalse(BlogFileService::isSafeFilename('http://evil.com/shell.php'));
        $this->assertFalse(BlogFileService::isSafeFilename('ftp://server/file'));
    }

    public function test_is_safe_filename_rejects_null_byte()
    {
        $this->assertFalse(BlogFileService::isSafeFilename("image\0.jpg"));
    }

    public function test_is_safe_filename_accepts_valid_name()
    {
        $this->assertTrue(BlogFileService::isSafeFilename('photo123.jpg'));
        $this->assertTrue(BlogFileService::isSafeFilename('OM_12345abcd.png'));
    }

    // --- deleteImage tests with Storage::fake ---

    public function test_delete_image_returns_true_for_null_or_empty()
    {
        Storage::fake('public');
        $this->assertTrue(BlogFileService::deleteImage(null));
        $this->assertTrue(BlogFileService::deleteImage(''));
    }

    public function test_delete_image_deletes_existing_file()
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/test_photo.jpg', 'contents');
        $this->assertTrue(Storage::disk('public')->exists('images/test_photo.jpg'));

        $result = BlogFileService::deleteImage('test_photo.jpg');

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('public')->exists('images/test_photo.jpg'));
    }

    public function test_delete_image_returns_true_for_nonexistent_file()
    {
        Storage::fake('public');
        $result = BlogFileService::deleteImage('nonexistent.jpg');
        $this->assertTrue($result); // Idempotent
    }

    public function test_delete_image_repeated_deletion_is_idempotent()
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/repeat.jpg', 'contents');

        $this->assertTrue(BlogFileService::deleteImage('repeat.jpg'));
        $this->assertTrue(BlogFileService::deleteImage('repeat.jpg')); // Second call
    }

    public function test_delete_image_rejects_unsafe_filename()
    {
        Storage::fake('public');
        $this->assertFalse(BlogFileService::deleteImage('../etc/passwd'));
    }
}
