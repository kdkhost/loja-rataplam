<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BlogHtmlSanitizer;

class BlogHtmlSanitizerTest extends TestCase
{
    public function test_removes_script_tags()
    {
        $dirty = '<p>Text</p><script>alert("XSS")</script>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('script', strtolower($clean));
        $this->assertStringNotContainsString('alert', $clean);
    }

    public function test_removes_onerror_without_quotes()
    {
        $dirty = '<img src=x onerror=alert(1) />';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('alert', $clean);
    }

    public function test_removes_onclick_with_quotes()
    {
        $dirty = '<a href="#" onclick="alert(\'XSS\')">Click</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('onclick', $clean);
    }

    public function test_removes_javascript_in_mixed_case()
    {
        $dirty = '<a href="JavaScript:alert(1)">Link</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('JavaScript', $clean);
        $this->assertStringNotContainsString('alert', $clean);
    }

    public function test_removes_javascript_in_href()
    {
        $dirty = '<a href="javascript:alert(1)">Link</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_removes_javascript_in_src()
    {
        $dirty = '<img src="javascript:alert(1)">';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_removes_data_text_html_urls()
    {
        $dirty = '<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">Data</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('data:text/html', $clean);
    }

    public function test_removes_svg_with_script()
    {
        $dirty = '<svg onload=alert(1)><script>alert(2)</script></svg>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('svg', strtolower($clean));
        $this->assertStringNotContainsString('alert', $clean);
    }

    public function test_preserves_legitimate_editor_html()
    {
        $dirty = '<h1>Title</h1><p>Paragraph with <strong>bold</strong> and <em>italic</em>.</p><ul><li>Item 1</li></ul>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringContainsString('<h1>Title</h1>', $clean);
        $this->assertStringContainsString('<strong>bold</strong>', $clean);
    }

    public function test_allows_valid_http_https_links_and_images()
    {
        $dirty = '<p><a href="https://example.com">Web</a> <img src="http://example.com/img.jpg" alt="Img"></p>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringContainsString('https://example.com', $clean);
        $this->assertStringContainsString('http://example.com/img.jpg', $clean);
    }

    // --- target="_blank" rel normalization tests ---

    public function test_adds_rel_noopener_noreferrer_when_rel_absent_double_quotes()
    {
        // External links automatically get target="_blank" by HTMLPurifier's HTML.TargetBlank config
        $dirty = '<a href="https://example.com">Link</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringContainsString('noopener', $clean);
        $this->assertStringContainsString('noreferrer', $clean);
    }

    public function test_preserves_existing_rel_and_adds_missing_tokens()
    {
        $dirty = '<a href="https://example.com" target="_blank" rel="nofollow">Link</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringContainsString('nofollow', $clean);
        $this->assertStringContainsString('noopener', $clean);
        $this->assertStringContainsString('noreferrer', $clean);
    }

    public function test_no_duplicate_noopener_noreferrer()
    {
        $dirty = '<a href="https://example.com" target="_blank" rel="noopener noreferrer">Link</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertEquals(1, substr_count($clean, 'noopener'));
        $this->assertEquals(1, substr_count($clean, 'noreferrer'));
    }

    public function test_does_not_add_rel_to_links_without_target_blank()
    {
        // Internal links do not get target="_blank" by default
        $dirty = '<a href="/internal-page">Normal Link</a>';
        $clean = BlogHtmlSanitizer::sanitize($dirty);
        $this->assertStringNotContainsString('noreferrer', $clean);
    }
}
