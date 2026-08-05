<?php

namespace Tests\Feature\MercadoPago;

use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoPaymentService;
use App\Services\MercadoPago\MercadoPixResponseSanitizer;
use App\Services\MercadoPago\MercadoPagoResponse;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoPixReplayTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected MercadoPagoPaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();

        // Configurar serviços reais
        $this->paymentService = app(MercadoPagoPaymentService::class);

        // Mock config resolver
        $mockConfigResolver = $this->createMock(\App\Services\MercadoPago\MercadoPagoConfigResolver::class);
        $mockConfigResolver->method('resolve')->willReturn([
            'mode' => 'sandbox',
            'pix_enabled' => true,
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
            'notification_url' => 'https://example.com/webhook',
        ]);
        $mockConfigResolver->method('resolveBackendCredentials')->willReturn(
            new \App\Services\MercadoPago\MercadoPagoCredentials(
                publicKey: 'synthetic-public-key',
                accessToken: 'fake-token',
                webhookSecret: 'synthetic-webhook-secret',
                mode: 'sandbox',
                collectorId: 'synthetic-collector'
            )
        );
        $this->paymentService->setConfigResolver($mockConfigResolver);
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    /** @test */
    public function primeira_criacao_pix_persiste_campos_necessarios()
    {
        $orderData = [
            'order_id' => null,
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        // Mock do cliente para retornar resposta Pix completa
        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-123',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => '00020126580014BR.GOV.BCB.PIX0136123e4567-e89b-12d3-a456-4266141740005204000053039865406100.005802BR5913Loja Teste6008Sao Paulo62070503***6304ABCD',
                        'qr_code_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
                        'ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123',
                    ],
                ],
            ],
            null,
            'Success'
        );

        $this->paymentService->setClient($this->createMockClient($mockResponse));

        $result = $this->paymentService->createPixPayment($orderData);

        $this->assertArrayHasKey('payment_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('qr_code', $result);
        $this->assertArrayHasKey('qr_code_base64', $result);
        $this->assertArrayHasKey('ticket_url', $result);
        $this->assertEquals('payment-123', $result['payment_id']);
        $this->assertEquals('pending', $result['status']);

        // Verificar persistência no banco
        $action = MercadoPagoAction::where('mercadopago_operation_id', 'payment-123')->first();
        $this->assertNotNull($action);
        $this->assertEquals('00020126580014BR.GOV.BCB.PIX0136123e4567-e89b-12d3-a456-4266141740005204000053039865406100.005802BR5913Loja Teste6008Sao Paulo62070503***6304ABCD', $action->pix_qr_code);
        $this->assertEquals('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', $action->pix_qr_code_base64);
        $this->assertEquals('https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=123', $action->pix_ticket_url);
    }

    /** @test */
    public function retry_existing_success_retorna_campos_persistidos()
    {
        $orderData = [
            'order_id' => 'order-456',
            'amount' => '50.00',
            'authoritative_amount' => '50.00',
            'description' => 'Test order 2',
            'payer_email' => 'customer2@example.com',
        ];

        // Criar ação existente com sucesso e campos Pix
        $action = MercadoPagoAction::create([
            'order_id' => 'order-456',
            'environment' => 'sandbox',
            'action' => 'create_pix_payment',
            'requested_amount' => 50.00,
            'currency' => 'BRL',
            'idempotency_key' => 'fixed-pix-key-1',
            'request_fingerprint' => null,
            'local_status' => 'success',
            'mercadopago_operation_id' => 'payment-456',
            'remote_status' => 'pending',
            'pix_qr_code' => '00020126580014BR.GOV.BCB.PIX0136123e4567-e89b-12d3-a456-426614174000520400005303986540650.005802BR5913Loja Teste6008Sao Paulo62070503***6304EFGH',
            'pix_qr_code_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'pix_ticket_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=456',
        ]);

        // Mock do idempotency service para retornar existing success
        $mockIdempotencyService = $this->createMockIdempotencyService($action);
        $this->paymentService->setIdempotencyService($mockIdempotencyService);

        $result = $this->paymentService->createPixPayment($orderData);

        $this->assertArrayHasKey('payment_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('qr_code', $result);
        $this->assertArrayHasKey('qr_code_base64', $result);
        $this->assertArrayHasKey('ticket_url', $result);
        $this->assertArrayHasKey('from_cache', $result);
        $this->assertEquals('payment-456', $result['payment_id']);
        $this->assertEquals('00020126580014BR.GOV.BCB.PIX0136123e4567-e89b-12d3-a456-426614174000520400005303986540650.005802BR5913Loja Teste6008Sao Paulo62070503***6304EFGH', $result['qr_code']);
        $this->assertEquals('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', $result['qr_code_base64']);
        $this->assertEquals('https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=456', $result['ticket_url']);
        $this->assertTrue($result['from_cache']);
    }

    /** @test */
    public function retry_nao_chama_mercadopago_client()
    {
        $orderData = [
            'order_id' => 'order-789',
            'amount' => '75.00',
            'authoritative_amount' => '75.00',
            'description' => 'Test order 3',
            'payer_email' => 'customer3@example.com',
        ];

        // Criar ação existente com sucesso
        $action = MercadoPagoAction::create([
            'order_id' => 'order-789',
            'environment' => 'sandbox',
            'action' => 'create_pix_payment',
            'requested_amount' => 75.00,
            'currency' => 'BRL',
            'idempotency_key' => 'fixed-pix-key-2',
            'request_fingerprint' => null,
            'local_status' => 'success',
            'mercadopago_operation_id' => 'payment-789',
            'remote_status' => 'pending',
            'pix_qr_code' => 'test-qr-code',
        ]);

        // Mock do cliente que falha se chamado
        $mockClient = $this->createMockClientThatFailsIfCalled();
        $this->paymentService->setClient($mockClient);

        // Mock do idempotency service para retornar existing success
        $mockIdempotencyService = $this->createMockIdempotencyService($action);
        $this->paymentService->setIdempotencyService($mockIdempotencyService);

        $result = $this->paymentService->createPixPayment($orderData);

        // Se o cliente foi chamado, o teste falharia
        $this->assertArrayHasKey('from_cache', $result);
        $this->assertTrue($result['from_cache']);
    }

    /** @test */
    public function resposta_com_segredos_nao_persiste_campos_sensiveis()
    {
        $orderData = [
            'order_id' => 'order-555',
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-555',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'test-qr-code',
                        'qr_code_base64' => 'test-base64',
                        'ticket_url' => 'https://www.mercadopago.com.br/checkout',
                    ],
                ],
            ],
            null,
            'Success'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        $this->paymentService->createPixPayment($orderData);

        // Verificar que o registro não contém segredos
        $action = MercadoPagoAction::where('order_id', 'order-555')->first();
        $this->assertNotNull($action);

        // Verificar campos Pix
        $this->assertEquals('test-qr-code', $action->pix_qr_code);
        $this->assertEquals('test-base64', $action->pix_qr_code_base64);
        $this->assertEquals('https://www.mercadopago.com.br/checkout', $action->pix_ticket_url);

        // Verificar que response_summary não contém dados sensíveis
        $this->assertArrayNotHasKey('point_of_interaction', $action->response_summary);
    }

    /** @test */
    public function payload_excedente_nao_e_persistido()
    {
        $orderData = [
            'order_id' => 'order-999',
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        // QR Code excedente (acima de 2000 caracteres)
        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-999',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => str_repeat('A', 3000),
                    ],
                ],
            ],
            null,
            'Success'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code excede limite de tamanho.');

        $this->paymentService->createPixPayment($orderData);
    }

    /** @test */
    public function url_nao_https_e_rejeitada()
    {
        $orderData = [
            'order_id' => 'order-888',
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        // URL HTTP em vez de HTTPS
        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-888',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'test-qr-code',
                        'ticket_url' => 'http://www.mercadopago.com.br/checkout',
                    ],
                ],
            ],
            null,
            'Success'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Ticket URL deve usar HTTPS.');

        $this->paymentService->createPixPayment($orderData);
    }

    /** @test */
    public function javascript_e_rejeitado()
    {
        $orderData = [
            'order_id' => 'order-777',
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        // QR Code com javascript:
        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-777',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'javascript:alert(1)',
                    ],
                ],
            ],
            null,
            'Success'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code contém protocolo inválido.');

        $this->paymentService->createPixPayment($orderData);
    }

    /** @test */
    public function data_e_rejeitado()
    {
        $orderData = [
            'order_id' => 'order-666',
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        // QR Code com data:
        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-666',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'data:text/plain;base64,SGVsbG8=',
                    ],
                ],
            ],
            null,
            'Success'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('QR Code contém protocolo inválido.');

        $this->paymentService->createPixPayment($orderData);
    }

    /** @test */
    public function nenhum_segredo_aparece_nos_logs()
    {
        $orderData = [
            'order_id' => 'order-log-test',
            'amount' => '100.00',
            'authoritative_amount' => '100.00',
            'description' => 'Test order',
            'payer_email' => 'customer@example.com',
        ];

        $mockResponse = new MercadoPagoResponse(
            true,
            200,
            [
                'id' => 'payment-log-test',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'test-qr-code',
                        'qr_code_base64' => 'test-base64',
                        'ticket_url' => 'https://www.mercadopago.com.br/checkout',
                    ],
                ],
            ],
            null,
            'Success'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        // Capturar logs
        \Log::shouldReceive('info')
            ->never()
            ->with(\Mockery::pattern('/access_token/i'));

        $this->paymentService->createPixPayment($orderData);

        // Verificar que o registro foi criado
        $action = MercadoPagoAction::where('order_id', 'order-log-test')->first();
        $this->assertNotNull($action);
    }

    public function test_contrato_primeira_criacao_e_replay_sao_equivalentes()
    {
        $orderData = [
            'order_id' => 'order-contract-test',
            'amount_cents' => 10000,
            'authoritative_amount' => '100.00',
            'description' => 'Teste contrato',
            'payer_email' => 'test@example.com',
        ];

        // Mock response com expiration_date
        $mockResponse = new MercadoPagoResponse(
            true,
            201,
            [
                'id' => 'payment-contract-123',
                'status' => 'pending',
                'date_of_expiration' => '2026-08-03 23:59:59',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'qr-contract-test',
                        'qr_code_base64' => 'base64-contract-test',
                        'ticket_url' => 'https://www.mercadopago.com.br/checkout/contract',
                    ],
                ],
            ],
            'created',
            'Payment created'
        );

        $mockClient = $this->createMockClient($mockResponse);
        $this->paymentService->setClient($mockClient);

        // Primeira criação
        $firstResponse = $this->paymentService->createPixPayment($orderData);

        // Verificar campos públicos na primeira resposta
        $this->assertArrayHasKey('payment_id', $firstResponse);
        $this->assertArrayHasKey('status', $firstResponse);
        $this->assertArrayHasKey('qr_code', $firstResponse);
        $this->assertArrayHasKey('qr_code_base64', $firstResponse);
        $this->assertArrayHasKey('ticket_url', $firstResponse);
        $this->assertArrayHasKey('expiration_date', $firstResponse);
        $this->assertEquals('payment-contract-123', $firstResponse['payment_id']);
        $this->assertEquals('pending', $firstResponse['status']);
        $this->assertEquals('qr-contract-test', $firstResponse['qr_code']);
        $this->assertEquals('base64-contract-test', $firstResponse['qr_code_base64']);
        $this->assertEquals('https://www.mercadopago.com.br/checkout/contract', $firstResponse['ticket_url']);
        $this->assertEquals('2026-08-03 23:59:59', $firstResponse['expiration_date']);

        // Configurar mock para replay (não deve chamar cliente)
        $action = MercadoPagoAction::where('order_id', 'order-contract-test')->first();
        $this->assertNotNull($action);

        $mockIdempotencyService = $this->createMockIdempotencyService($action);
        $this->paymentService->setIdempotencyService($mockIdempotencyService);

        $mockClientNoCall = $this->createMockClientThatFailsIfCalled();
        $this->paymentService->setClient($mockClientNoCall);

        // Replay com mesma idempotency key
        $replayResponse = $this->paymentService->createPixPayment($orderData);

        // Verificar campos públicos no replay
        $this->assertArrayHasKey('payment_id', $replayResponse);
        $this->assertArrayHasKey('status', $replayResponse);
        $this->assertArrayHasKey('qr_code', $replayResponse);
        $this->assertArrayHasKey('qr_code_base64', $replayResponse);
        $this->assertArrayHasKey('ticket_url', $replayResponse);
        $this->assertArrayHasKey('expiration_date', $replayResponse);
        $this->assertArrayHasKey('from_cache', $replayResponse);

        // Verificar equivalência dos campos públicos
        $this->assertEquals($firstResponse['payment_id'], $replayResponse['payment_id']);
        $this->assertEquals($firstResponse['status'], $replayResponse['status']);
        $this->assertEquals($firstResponse['qr_code'], $replayResponse['qr_code']);
        $this->assertEquals($firstResponse['qr_code_base64'], $replayResponse['qr_code_base64']);
        $this->assertEquals($firstResponse['ticket_url'], $replayResponse['ticket_url']);
        $this->assertEquals($firstResponse['expiration_date'], $replayResponse['expiration_date']);

        // Verificar que replay não criou novo registro
        $actionCount = MercadoPagoAction::where('order_id', 'order-contract-test')->count();
        $this->assertEquals(1, $actionCount);
    }

    private function createMockClient(MercadoPagoResponse $response)
    {
        $mock = $this->createMock(\App\Services\MercadoPago\MercadoPagoClient::class);
        $mock->method('createPayment')->willReturn($response);
        return $mock;
    }

    private function createMockClientThatFailsIfCalled()
    {
        $mock = $this->createMock(\App\Services\MercadoPago\MercadoPagoClient::class);
        $mock->method('createPayment')->will($this->throwException(new \Exception('Client should not be called')));
        return $mock;
    }

    private function createMockIdempotencyService(MercadoPagoAction $existingAction)
    {
        $mock = $this->createMock(\App\Services\MercadoPago\MercadoPagoIdempotencyService::class);
        $mock->method('generateDeterministicKey')->willReturn($existingAction->idempotency_key);
        $mock->method('acquireAction')->willReturn(
            new \App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult(
                $existingAction,
                false,
                \App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult::REASON_EXISTING_SUCCESS,
                'test-owner'
            )
        );
        return $mock;
    }
}
