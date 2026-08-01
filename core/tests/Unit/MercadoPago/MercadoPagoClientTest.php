<?php
namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoMoney;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;

class MercadoPagoClientTest extends TestCase
{
    public function test_refund_total_omits_request_body()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(201, [], json_encode(['id' => '123']))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockClient = new Client(['handler' => $handlerStack]);
        $money = new MercadoPagoMoney();
        $client = new MercadoPagoClient('fake-token', $mockClient, $money);

        $client->refund('pay-123', null, 'idem-key-123');

        $request = $container[0]['request'];
        $body = (string) $request->getBody();
        $this->assertEquals('', $body); // No body
        $this->assertEquals('idem-key-123', $request->getHeaderLine('X-Idempotency-Key'));
    }

    public function test_refund_partial_sends_amount()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(201, [], json_encode(['id' => '123']))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockClient = new Client(['handler' => $handlerStack]);
        $money = new MercadoPagoMoney();
        $client = new MercadoPagoClient('fake-token', $mockClient, $money);

        $client->refund('pay-123', '10.5', 'idem-key-123');

        $request = $container[0]['request'];
        $body = json_decode((string) $request->getBody(), true);

        $this->assertArrayHasKey('amount', $body);
        $this->assertEquals(10.50, $body['amount']);
    }

    public function test_refund_sends_exact_idempotency_header()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(201, [], json_encode(['id' => '123']))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockClient = new Client(['handler' => $handlerStack]);
        $money = new MercadoPagoMoney();
        $client = new MercadoPagoClient('fake-token', $mockClient, $money);

        $client->refund('pay-123', '10', 'idem-key-456');

        $request = $container[0]['request'];
        $this->assertTrue($request->hasHeader('X-Idempotency-Key'));
        $this->assertFalse($request->hasHeader('Idempotency-Key'));
        $this->assertEquals('idem-key-456', $request->getHeaderLine('X-Idempotency-Key'));
    }

    public function test_refund_total_has_no_body_option()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(201, [], json_encode(['id' => '123']))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockClient = new Client(['handler' => $handlerStack]);
        $money = new MercadoPagoMoney();
        $client = new MercadoPagoClient('fake-token', $mockClient, $money);

        $client->refund('pay-123', null, 'idem-key-123');

        $request = $container[0]['request'];
        $body = (string) $request->getBody();
        $this->assertEquals('', $body);
        $this->assertNotEquals('{}', $body);
        $this->assertStringNotContainsString('stdClass', $body);
    }

    public function test_refund_total_has_no_amount()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(201, [], json_encode(['id' => '123']))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockClient = new Client(['handler' => $handlerStack]);
        $money = new MercadoPagoMoney();
        $client = new MercadoPagoClient('fake-token', $mockClient, $money);

        $client->refund('pay-123', null, 'idem-key-123');

        $request = $container[0]['request'];
        $body = (string) $request->getBody();
        $this->assertEquals('', $body);
        $decoded = json_decode($body, true);
        $this->assertNull($decoded);
    }

    public function test_refund_partial_sends_decimal_amount()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(201, [], json_encode(['id' => '123']))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockClient = new Client(['handler' => $handlerStack]);
        $money = new MercadoPagoMoney();
        $client = new MercadoPagoClient('fake-token', $mockClient, $money);

        $client->refund('pay-123', '10.5', 'idem-key-123');

        $request = $container[0]['request'];
        $body = json_decode((string) $request->getBody(), true);

        $this->assertArrayHasKey('amount', $body);
        $this->assertEquals(10.50, $body['amount']);
        $this->assertIsFloat($body['amount']);
    }
}
