<?php

namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoPixResponseSanitizer;
use PHPUnit\Framework\TestCase;

class MercadoPagoPixResponseSanitizerTest extends TestCase
{
    private MercadoPagoPixResponseSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new MercadoPagoPixResponseSanitizer();
    }

    /** @test */
    public function ticket_url_https_valida_aceita()
    {
        $data = ['ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123'];
        $result = $this->sanitizer->sanitize($data);

        $this->assertArrayHasKey('ticket_url', $result);
        $this->assertEquals('https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123', $result['ticket_url']);
    }

    /** @test */
    public function url_http_rejeitada()
    {
        $data = ['ticket_url' => 'http://www.mercadopago.com.br/checkout'];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Ticket URL deve usar HTTPS.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function javascript_rejeitado()
    {
        $data = ['qr_code' => 'javascript:alert(1)'];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code contém protocolo inválido.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function data_rejeitado()
    {
        $data = ['qr_code' => 'data:text/plain;base64,SGVsbG8='];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code contém protocolo inválido.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function url_malformada_rejeitada()
    {
        $data = ['ticket_url' => 'not-a-url'];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Ticket URL deve usar HTTPS.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function url_acima_do_limite_rejeitada()
    {
        $data = ['ticket_url' => 'https://example.com/' . str_repeat('a', 2100)];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Ticket URL excede limite de tamanho.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function qr_code_dentro_do_limite_aceito()
    {
        $data = ['qr_code' => '00020126580014BR.GOV.BCB.PIX0136123e4567-e89b-12d3-a456-4266141740005204000053039865406100.005802BR5913Loja Teste6008Sao Paulo62070503***6304ABCD'];
        $result = $this->sanitizer->sanitize($data);

        $this->assertArrayHasKey('qr_code', $result);
        $this->assertEquals($data['qr_code'], $result['qr_code']);
    }

    /** @test */
    public function qr_code_acima_do_limite_rejeitado()
    {
        $data = ['qr_code' => str_repeat('A', 3000)];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code excede limite de tamanho.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function base64_dentro_do_limite_aceito()
    {
        $data = ['qr_code_base64' => str_repeat('A', 50000)];
        $result = $this->sanitizer->sanitize($data);

        $this->assertArrayHasKey('qr_code_base64', $result);
        $this->assertEquals($data['qr_code_base64'], $result['qr_code_base64']);
    }

    /** @test */
    public function base64_acima_de_100000_caracteres_rejeitado()
    {
        $data = ['qr_code_base64' => str_repeat('A', 100001)];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code Base64 excede limite de tamanho.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function caracteres_de_controle_rejeitados()
    {
        $data = ['qr_code' => "test\x00\x01\x02code"];

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code contém caracteres inválidos.');

        $this->sanitizer->sanitize($data);
    }

    /** @test */
    public function campos_vazios_sao_ignorados()
    {
        $data = [
            'qr_code' => null,
            'qr_code_base64' => '',
            'ticket_url' => null,
        ];
        $result = $this->sanitizer->sanitize($data);

        $this->assertEmpty($result);
    }

    /** @test */
    public function expiration_date_preservado()
    {
        $data = ['expiration_date' => '2026-12-31T23:59:59Z'];
        $result = $this->sanitizer->sanitize($data);

        $this->assertArrayHasKey('expiration_date', $result);
        $this->assertEquals('2026-12-31T23:59:59Z', $result['expiration_date']);
    }

    /** @test */
    public function todos_os_campos_validos_sao_retornados()
    {
        $data = [
            'qr_code' => '00020126580014BR.GOV.BCB.PIX0136123e4567-e89b-12d3-a456-4266141740005204000053039865406100.005802BR5913Loja Teste6008Sao Paulo62070503***6304ABCD',
            'qr_code_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123',
            'expiration_date' => '2026-12-31T23:59:59Z',
        ];
        $result = $this->sanitizer->sanitize($data);

        $this->assertCount(4, $result);
        $this->assertArrayHasKey('qr_code', $result);
        $this->assertArrayHasKey('qr_code_base64', $result);
        $this->assertArrayHasKey('ticket_url', $result);
        $this->assertArrayHasKey('expiration_date', $result);
    }
}
