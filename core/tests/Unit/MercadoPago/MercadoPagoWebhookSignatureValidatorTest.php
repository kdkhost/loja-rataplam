<?php

namespace Tests\Unit\MercadoPago;

use App\Exceptions\InvalidMercadoPagoWebhookSignatureException;
use App\Services\MercadoPago\MercadoPagoWebhookSignatureValidator;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MercadoPagoWebhookSignatureValidatorTest extends TestCase
{
    private const SECRET = 'synthetic-webhook-secret';
    private const REQUEST_ID = 'synthetic-request-id';
    private const DATA_ID = 'payment-abc123';
    private const TIMESTAMP = '1704908010';

    private MercadoPagoWebhookSignatureValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new MercadoPagoWebhookSignatureValidator();
    }

    public function test_valid_signature(): void
    {
        $this->validator->validate($this->signature(), self::REQUEST_ID, self::DATA_ID, self::SECRET);
        $this->addToAssertionCount(1);
    }

    public function test_empty_secret_is_rejected(): void
    {
        $this->expectFailure('missing_secret', fn () => $this->validator->validate($this->signature(), self::REQUEST_ID, self::DATA_ID, ''));
    }

    public function test_missing_signature_is_rejected(): void
    {
        $this->expectFailure('missing_signature', fn () => $this->validator->validate(null, self::REQUEST_ID, self::DATA_ID, self::SECRET));
    }

    public function test_missing_request_id_is_rejected(): void
    {
        $this->expectFailure('missing_request_id', fn () => $this->validator->validate($this->signature(), null, self::DATA_ID, self::SECRET));
    }

    public function test_missing_data_id_is_rejected(): void
    {
        $this->expectFailure('missing_data_id', fn () => $this->validator->validate($this->signature(), self::REQUEST_ID, null, self::SECRET));
    }

    public function test_missing_timestamp_is_rejected(): void
    {
        $this->expectFailure('missing_timestamp', fn () => $this->validator->validate('v1=' . str_repeat('a', 64), self::REQUEST_ID, self::DATA_ID, self::SECRET));
    }

    public function test_non_numeric_timestamp_is_rejected(): void
    {
        $this->expectFailure('invalid_timestamp', fn () => $this->validator->validate('ts=invalid,v1=' . str_repeat('a', 64), self::REQUEST_ID, self::DATA_ID, self::SECRET));
    }

    public function test_missing_v1_is_rejected(): void
    {
        $this->expectFailure('missing_hash', fn () => $this->validator->validate('ts=' . self::TIMESTAMP, self::REQUEST_ID, self::DATA_ID, self::SECRET));
    }

    public function test_invalid_v1_format_is_rejected(): void
    {
        $this->expectFailure('invalid_hash', fn () => $this->validator->validate('ts=' . self::TIMESTAMP . ',v1=not-hex', self::REQUEST_ID, self::DATA_ID, self::SECRET));
    }

    public function test_malformed_header_is_rejected(): void
    {
        $this->expectFailure('missing_timestamp', fn () => $this->validator->validate('malformed', self::REQUEST_ID, self::DATA_ID, self::SECRET));
    }

    public function test_official_spaces_are_accepted(): void
    {
        $this->validator->validate(' ts = ' . self::TIMESTAMP . ' , v1 = ' . $this->hash() . ' ', self::REQUEST_ID, self::DATA_ID, self::SECRET);
        $this->addToAssertionCount(1);
    }

    public function test_component_order_is_irrelevant(): void
    {
        $this->validator->validate('v1=' . $this->hash() . ',ts=' . self::TIMESTAMP, self::REQUEST_ID, self::DATA_ID, self::SECRET);
        $this->addToAssertionCount(1);
    }

    public function test_unknown_components_are_ignored(): void
    {
        $this->validator->validate('future=value,' . $this->signature(), self::REQUEST_ID, self::DATA_ID, self::SECRET);
        $this->addToAssertionCount(1);
    }

    public function test_alphanumeric_data_id_is_lowercased(): void
    {
        $hash = $this->hash('Payment-AbC123');
        $this->validator->validate('ts=' . self::TIMESTAMP . ',v1=' . $hash, self::REQUEST_ID, 'Payment-AbC123', self::SECRET);
        $this->addToAssertionCount(1);
    }

    public function test_wrong_secret_is_rejected(): void
    {
        $this->expectFailure('signature_mismatch', fn () => $this->validator->validate($this->signature(), self::REQUEST_ID, self::DATA_ID, 'another-synthetic-secret'));
    }

    public function test_different_request_id_is_rejected(): void
    {
        $this->expectFailure('signature_mismatch', fn () => $this->validator->validate($this->signature(), 'different-request-id', self::DATA_ID, self::SECRET));
    }

    public function test_different_data_id_is_rejected(): void
    {
        $this->expectFailure('signature_mismatch', fn () => $this->validator->validate($this->signature(), self::REQUEST_ID, 'different-payment', self::SECRET));
    }

    public function test_exception_contains_no_sensitive_material(): void
    {
        try {
            $this->validator->validate($this->signature(), self::REQUEST_ID, self::DATA_ID, 'wrong-secret');
            $this->fail('A validacao deveria falhar.');
        } catch (InvalidMercadoPagoWebhookSignatureException $exception) {
            $serialized = json_encode([
                $exception->getMessage(),
                $exception->failureCode(),
                $exception->requestId(),
            ]);
            $this->assertStringNotContainsString(self::SECRET, $serialized);
            $this->assertStringNotContainsString($this->hash(), $serialized);
            $this->assertStringNotContainsString($this->signature(), $serialized);
        }
    }

    public function test_validation_writes_no_logs(): void
    {
        Log::spy();
        try {
            $this->validator->validate($this->signature(), self::REQUEST_ID, self::DATA_ID, 'wrong-secret');
        } catch (InvalidMercadoPagoWebhookSignatureException) {
        }
        Log::shouldNotHaveReceived('log');
        Log::shouldNotHaveReceived('error');
        Log::shouldNotHaveReceived('warning');
    }

    public function test_implementation_uses_hash_equals(): void
    {
        $source = file_get_contents(app_path('Services/MercadoPago/MercadoPagoWebhookSignatureValidator.php'));
        $this->assertStringContainsString('hash_equals($computedHash, $receivedHash)', $source);
    }

    private function signature(): string
    {
        return 'ts=' . self::TIMESTAMP . ',v1=' . $this->hash();
    }

    private function hash(string $dataId = self::DATA_ID): string
    {
        $manifest = 'id:' . strtolower($dataId)
            . ';request-id:' . self::REQUEST_ID
            . ';ts:' . self::TIMESTAMP . ';';

        return hash_hmac('sha256', $manifest, self::SECRET);
    }

    private function expectFailure(string $code, callable $operation): void
    {
        try {
            $operation();
            $this->fail('A validacao deveria falhar.');
        } catch (InvalidMercadoPagoWebhookSignatureException $exception) {
            $this->assertSame($code, $exception->failureCode());
            $this->assertSame('Assinatura do webhook invalida.', $exception->getMessage());
        }
    }
}
