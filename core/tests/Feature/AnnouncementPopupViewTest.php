<?php

namespace Tests\Feature;

use App\Models\Setting;
use Tests\TestCase;

class AnnouncementPopupViewTest extends TestCase
{
    public function test_announcement_popup_renders_title_and_details_for_banner_type(): void
    {
        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'announcement_type' => 'banner',
                'announcement_title' => 'Promocao especial',
                'announcement_details' => 'Texto do popup',
                'announcement_link' => 'https://example.com/oferta',
                'announcement' => null,
            ]
        );

        $html = view('includes.announcement-popup')->render();

        $this->assertStringContainsString('Promocao especial', $html);
        $this->assertStringContainsString('Texto do popup', $html);
        $this->assertStringContainsString('announcement-with-content', $html);
    }
}
