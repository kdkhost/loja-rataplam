<?php
namespace Tests\Feature\MercadoPago;

use App\Models\Admin;
use App\Models\MercadoPagoSetting;
use App\Models\PaymentSetting;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoSettingControllerTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();

        // Disable system middleware for isolated Mercado Pago tests
        $this->withoutMiddleware([
            \App\Http\Middleware\AdminLocalize::class,
            \App\Http\Middleware\Security::class,
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_update_requires_authentication()
    {
        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
        ]);

        $response->assertRedirect(route('back.login'));
    }

    public function test_update_saves_configuration()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
            'sandbox_public_key' => 'TEST-123',
            'sandbox_access_token' => 'TEST-TOKEN',
            'pix_enabled' => '1',
            'credit_card_enabled' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('mercadopago_settings', [
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-123',
        ]);
    }

    public function test_update_validates_mode()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'invalid_mode',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
        ]);

        $response->assertSessionHasErrors(['mode']);
    }

    public function test_update_validates_pix_expiration_range()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 4, // abaixo do mínimo de 5
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
        ]);

        $response->assertSessionHasErrors(['pix_expiration_minutes']);
    }

    public function test_update_validates_max_installments_range()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 13, // acima do máximo de 12
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
        ]);

        $response->assertSessionHasErrors(['max_installments']);
    }

    public function test_update_validates_fee_percent_range()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 31, // acima do máximo de 30
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
        ]);

        $response->assertSessionHasErrors(['pix_fee_percent']);
    }

    public function test_update_validates_statement_descriptor_format()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
            'statement_descriptor' => 'inv@lid#chars', // caracteres inválidos
        ]);

        $response->assertSessionHasErrors(['statement_descriptor']);
    }

    public function test_update_prevents_removing_active_sandbox_token()
    {
        MercadoPagoSetting::create([
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-123',
            'sandbox_access_token' => 'TEST-TOKEN',
        ]);

        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'status' => '1',
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
            'remove_sandbox_token' => '1',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_update_preserves_credentials_when_empty()
    {
        MercadoPagoSetting::create([
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'EXISTING-KEY',
            'sandbox_access_token' => 'EXISTING-TOKEN',
        ]);

        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('back.setting.payment.mercadopago.update'), [
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
            'sandbox_public_key' => '', // vazio deve preservar
            'sandbox_access_token' => '', // vazio deve preservar
        ]);

        $response->assertSessionHasNoErrors();

        // Verificar através do model (que descriptografa automaticamente)
        $settings = MercadoPagoSetting::where('configuration_key', 'default')->first();
        $this->assertEquals('EXISTING-KEY', $settings->sandbox_public_key);
        $this->assertEquals('EXISTING-TOKEN', $settings->sandbox_access_token);
    }

    public function test_configuration_key_always_forced_to_default()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'admin');

        // Tentar criar configuração com configuration_key diferente
        $setting = MercadoPagoSetting::create([
            'configuration_key' => 'another',
            'mode' => 'sandbox',
            'webhook_validation_mode' => 'api_lookup',
            'fee_calculation_mode' => 'additive',
            'pix_expiration_minutes' => 30,
            'max_installments' => 1,
            'pix_fee_percent' => 0,
            'pix_fee_fixed' => 0,
            'credit_fee_percent' => 0,
            'credit_fee_fixed' => 0,
        ]);

        // Confirmar que foi forçado para 'default'
        $this->assertEquals('default', $setting->configuration_key);
        $this->assertDatabaseHas('mercadopago_settings', [
            'id' => $setting->id,
            'configuration_key' => 'default',
        ]);

        // Tentar atualizar para outro valor
        $setting->configuration_key = 'different';
        $setting->save();

        // Confirmar que permanece 'default'
        $setting->refresh();
        $this->assertEquals('default', $setting->configuration_key);
    }
}
