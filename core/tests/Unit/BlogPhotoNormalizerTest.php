<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\BlogPhotoNormalizer;

class BlogPhotoNormalizerTest extends TestCase
{
    public function test_normalizes_null_to_empty_array()
    {
        $this->assertEquals([], BlogPhotoNormalizer::normalize(null));
    }

    public function test_normalizes_empty_string_to_empty_array()
    {
        $this->assertEquals([], BlogPhotoNormalizer::normalize(''));
        $this->assertEquals([], BlogPhotoNormalizer::normalize('   '));
    }

    public function test_normalizes_invalid_json_to_empty_array()
    {
        $this->assertEquals([], BlogPhotoNormalizer::normalize('{invalid_json}'));
    }

    public function test_normalizes_scalar_json_to_empty_array()
    {
        $this->assertEquals([], BlogPhotoNormalizer::normalize('"scalar_string"'));
        $this->assertEquals([], BlogPhotoNormalizer::normalize('12345'));
    }

    public function test_normalizes_empty_array_to_empty_array()
    {
        $this->assertEquals([], BlogPhotoNormalizer::normalize([]));
    }

    public function test_filters_non_string_and_empty_elements()
    {
        $input = ['img1.jpg', null, '', '   ', 123, ['nested'], 'img2.png'];
        $expected = ['img1.jpg', 'img2.png'];
        $this->assertEquals($expected, BlogPhotoNormalizer::normalize($input));
    }

    public function test_reindexes_non_sequential_arrays()
    {
        $input = [2 => 'photoA.jpg', 5 => 'photoB.jpg'];
        $expected = ['photoA.jpg', 'photoB.jpg'];
        $this->assertEquals($expected, BlogPhotoNormalizer::normalize($input));
    }

    public function test_decodes_valid_json_array()
    {
        $json = json_encode(['a.jpg', 'b.jpg']);
        $this->assertEquals(['a.jpg', 'b.jpg'], BlogPhotoNormalizer::normalize($json));
    }
}
