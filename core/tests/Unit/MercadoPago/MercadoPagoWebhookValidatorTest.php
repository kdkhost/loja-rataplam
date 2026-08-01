<?php
namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoWebhookValidator;
use PHPUnit\Framework\TestCase;

class MercadoPagoWebhookValidatorTest extends TestCase
{
    public function test_valid_signature()
    {
        $validator = new MercadoPagoWebhookValidator();
        $ts = time();
        $dataId = '12345';
        $requestId = 'req123';
        $secret = 'my_secret';
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $hash = hash_hmac('sha256', $manifest, $secret);
        
        $headers = [
            'x-signature' => ["ts={$ts},v1={$hash}"],
            'x-request-id' => [$requestId]
        ];

        $this->assertTrue($validator->isValid($headers, $dataId, $secret));
    }

    public function test_invalid_signature()
    {
        $validator = new MercadoPagoWebhookValidator();
        $ts = time();
        
        $headers = [
            'x-signature' => ["ts={$ts},v1=wronghash"],
            'x-request-id' => ['req123']
        ];

        $this->assertFalse($validator->isValid($headers, '12345', 'my_secret'));
    }

    public function test_missing_signature()
    {
        $validator = new MercadoPagoWebhookValidator();
        $headers = [
            'x-request-id' => ['req123']
        ];
        $this->assertFalse($validator->isValid($headers, '12345', 'my_secret'));
    }

    public function test_missing_request_id()
    {
        $validator = new MercadoPagoWebhookValidator();
        $ts = time();
        $headers = [
            'x-signature' => ["ts={$ts},v1=somehash"]
        ];
        $this->assertFalse($validator->isValid($headers, '12345', 'my_secret'));
    }

    public function test_missing_data_id()
    {
        $validator = new MercadoPagoWebhookValidator();
        $ts = time();
        $headers = [
            'x-signature' => ["ts={$ts},v1=somehash"],
            'x-request-id' => ['req123']
        ];
        $this->assertFalse($validator->isValid($headers, null, 'my_secret'));
    }

    public function test_malformed_signature()
    {
        $validator = new MercadoPagoWebhookValidator();
        $headers = [
            'x-signature' => ["bad_format_here"],
            'x-request-id' => ['req123']
        ];
        $this->assertFalse($validator->isValid($headers, '12345', 'my_secret'));
    }

    public function test_missing_ts()
    {
        $validator = new MercadoPagoWebhookValidator();
        $headers = [
            'x-signature' => ["v1=somehash"],
            'x-request-id' => ['req123']
        ];
        $this->assertFalse($validator->isValid($headers, '12345', 'my_secret'));
    }

    public function test_missing_v1()
    {
        $validator = new MercadoPagoWebhookValidator();
        $headers = [
            'x-signature' => ["ts=1234567890"],
            'x-request-id' => ['req123']
        ];
        $this->assertFalse($validator->isValid($headers, '12345', 'my_secret'));
    }

    public function test_empty_secret()
    {
        $validator = new MercadoPagoWebhookValidator();
        $headers = [
            'x-signature' => ["ts=123,v1=hash"],
            'x-request-id' => ['req123']
        ];
        $this->assertFalse($validator->isValid($headers, '12345', ''));
    }

    public function test_old_but_cryptographically_valid_signature()
    {
        $validator = new MercadoPagoWebhookValidator();
        // Time from 10 years ago
        $ts = time() - (10 * 365 * 24 * 60 * 60);
        $dataId = '12345';
        $requestId = 'req123';
        $secret = 'my_secret';
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $hash = hash_hmac('sha256', $manifest, $secret);
        
        $headers = [
            'x-signature' => ["ts={$ts},v1={$hash}"],
            'x-request-id' => [$requestId]
        ];

        // Should be valid because we removed the rigid 5-min rejection in this phase
        $this->assertTrue($validator->isValid($headers, $dataId, $secret));
    }
}
