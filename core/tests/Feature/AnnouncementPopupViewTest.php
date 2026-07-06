<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Item;
use App\Models\PromoCode;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AnnouncementPopupViewTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_exit_popup_update_saves_coupon_and_product_lists_together(): void
    {
        $admin = Admin::query()->firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role_id' => 0,
            ]
        );

        $coupon = PromoCode::query()->create([
            'title' => 'Cupom teste',
            'code_name' => 'TESTE10',
            'discount' => 10,
            'status' => 1,
            'no_of_times' => 100,
            'type' => 'percentage',
        ]);

        $item = Item::query()->create([
            'name' => 'Produto teste popup',
            'slug' => 'produto-teste-popup-' . uniqid(),
            'discount_price' => 39.9,
            'previous_price' => 59.9,
            'stock' => 10,
            'status' => 1,
            'is_type' => 'new',
            'item_type' => 'normal',
            'photo' => 'placeholder.png',
            'thumbnail' => 'placeholder.png',
        ]);

        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'exit_popup_coupon_ids' => json_encode([]),
                'exit_popup_product_ids' => json_encode([]),
                'exit_popup_show_random' => 0,
            ]
        );

        $response = $this->actingAs($admin, 'admin')->post(route('back.platform.popups.update'), [
            'exit_popup_enabled' => 1,
            'exit_popup_mode' => 'mixed',
            'exit_popup_title' => 'Ei, psiu',
            'exit_popup_text' => 'Teste de saida',
            'exit_popup_coupon' => 'MANUAL',
            'exit_popup_button_text' => 'Pegar desconto',
            'exit_popup_link' => '#',
            'exit_popup_show_random' => 1,
            'exit_popup_coupon_ids' => [$coupon->id],
            'exit_popup_product_ids' => [$item->id],
            'promo_popup_mode' => 'manual',
            'promo_popup_delay' => 3,
        ]);

        $response->assertRedirect();

        $setting = Setting::query()->findOrFail(1);

        $this->assertSame([$coupon->id], array_map('intval', json_decode($setting->exit_popup_coupon_ids, true)));
        $this->assertSame([$item->id], array_map('intval', json_decode($setting->exit_popup_product_ids, true)));
        $this->assertSame('mixed', $setting->exit_popup_mode);
        $this->assertSame(1, (int) $setting->exit_popup_show_random);
    }
}
