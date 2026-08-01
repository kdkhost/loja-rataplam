<?php
namespace Tests\Unit\MercadoPago;

use App\Models\MercadoPagoSetting;
use App\Models\PaymentSetting;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MercadoPagoConfigResolverTest extends TestCase
{
    public function test_resolve_public_configuration_returns_sandbox_mode()
    {
        MercadoPagoSetting::factory()->create([
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-123',
            'sandbox_access_token' => 'TEST-TOKEN',
            'pix_enabled' => true,
            'credit_card_enabled' => true,
        ]);

        $resolver = new MercadoPagoConfigResolver();
        $config = $resolver->resolvePublicConfiguration();

        $this->assertEquals('sandbox', $config['mode']);
        $this->assertTrue($config['sandbox_token_configured']);
        $this->assertFalse($config['production_token_configured']);
    }

    public function test_resolve_public_configuration_returns_production_mode()
    {
        MercadoPagoSetting::factory()->create([
            'configuration_key' => 'default',
            'mode' => 'production',
            'production_public_key' => 'PROD-123',
            'production_access_token' => 'PROD-TOKEN',
            'pix_enabled' => true,
            'credit_card_enabled' => true,
        ]);

        $resolver = new MercadoPagoConfigResolver();
        $config = $resolver->resolvePublicConfiguration();

        $this->assertEquals('production', $config['mode']);
        $this->assertTrue($config['production_token_configured']);
        $this->assertFalse($config['sandbox_token_configured']);
    }

    public function test_resolve_backend_credentials_returns_sandbox_credentials()
    {
        MercadoPagoSetting::factory()->create([
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-123',
            'sandbox_access_token' => 'TEST-TOKEN',
            'sandbox_webhook_secret' => 'TEST-SECRET',
        ]);

        $resolver = new MercadoPagoConfigResolver();
        $credentials = $resolver->resolveBackendCredentials();

        $this->assertEquals('TEST-123', $credentials->publicKey);
        $this->assertEquals('TEST-TOKEN', $credentials->accessToken);
        $this->assertEquals('TEST-SECRET', $credentials->webhookSecret);
        $this->assertEquals('sandbox', $credentials->mode);
    }

    public function test_resolve_backend_credentials_returns_production_credentials()
    {
        MercadoPagoSetting::factory()->create([
            'configuration_key' => 'default',
            'mode' => 'production',
            'production_public_key' => 'PROD-123',
            'production_access_token' => 'PROD-TOKEN',
            'production_webhook_secret' => 'PROD-SECRET',
        ]);

        $resolver = new MercadoPagoConfigResolver();
        $credentials = $resolver->resolveBackendCredentials();

        $this->assertEquals('PROD-123', $credentials->publicKey);
        $this->assertEquals('PROD-TOKEN', $credentials->accessToken);
        $this->assertEquals('PROD-SECRET', $credentials->webhookSecret);
        $this->assertEquals('production', $credentials->mode);
    }

    public function test_resolve_admin_configuration_includes_public_keys()
    {
        MercadoPagoSetting::factory()->create([
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-123',
            'production_public_key' => 'PROD-456',
        ]);

        $resolver = new MercadoPagoConfigResolver();
        $config = $resolver->resolveAdminConfiguration();

        $this->assertEquals('TEST-123', $config['sandbox_public_key']);
        $this->assertEquals('PROD-456', $config['production_public_key']);
    }

    public function test_fallback_to_legacy_when_no_settings_exist()
    {
        PaymentSetting::factory()->create([
            'unique_keyword' => 'mercadopago',
            'information' => json_encode([
                'public_key' => 'LEGACY-123',
                'token' => 'LEGACY-TOKEN',
                'check_sandbox' => 1,
                'pix_enabled' => 1,
                'credit_card_enabled' => 1,
            ]),
        ]);

        $resolver = new MercadoPagoConfigResolver();
        $config = $resolver->resolvePublicConfiguration();

        $this->assertEquals('sandbox', $config['mode']);
        $this->assertTrue($config['is_legacy']);
    }

    public function test_resolve_backend_credentials_throws_when_no_settings()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mercado Pago: Nenhuma configuração encontrada.');

        $resolver = new MercadoPagoConfigResolver();
        $resolver->resolveBackendCredentials();
    }
}
