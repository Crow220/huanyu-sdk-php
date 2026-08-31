<?php
declare(strict_types=1);

namespace HuanyuSdk\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HuanyuSdk\Client;
use HuanyuSdk\Exception\HuanyuApiException;
use HuanyuSdk\Signature;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private const KEY = 'mk_test_001';
    private const SECRET = 'test-secret-0001';

    private function client(MockHandler $mock): Client
    {
        return new Client(self::KEY, self::SECRET, 'https://api.example.test/addons/huanyu',
            new GuzzleClient(['handler' => HandlerStack::create($mock)]));
    }

    public function testCreateOrderSignsAndParsesEnvelope(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode([
            'code' => 1, 'msg' => '订单创建成功', 'time' => 1756684800,
            'data' => ['order_no' => 'HY001', 'result_status' => 'success'],
        ]))]);

        $order = $this->client($mock)->createOrder([
            'order_type' => '1', 'payment_amount' => '100.00', 'merchant_order_no' => 'M001',
        ]);

        $this->assertSame('HY001', $order['order_no']);
        $request = $mock->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/addons/huanyu/merchant/createOrder', $request->getUri()->getPath());
        parse_str((string) $request->getBody(), $sent);
        $expected = Signature::sign($sent, self::SECRET);
        $this->assertSame($expected, $sent['signature']);
        $this->assertSame(self::KEY, $sent['api_key']);
        $this->assertArrayHasKey('timestamp', $sent);
    }

    public function testCreateOrderPendingIdentity(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode([
            'code' => 1, 'msg' => '需补充客户身份信息',
            'data' => ['result_status' => 'pending_identity', 'identity_url' => 'https://x.test/i?o=1'],
        ]))]);
        $order = $this->client($mock)->createOrder(['order_type' => '1', 'payment_amount' => '1.00']);
        $this->assertSame('pending_identity', $order['result_status']);
    }

    public function testNonSuccessCodeThrowsApiException(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode([
            'code' => 0, 'msg' => '签名错误', 'time' => 1756684800, 'data' => null,
        ]))]);
        try {
            $this->client($mock)->orderList();
            $this->fail('应抛出 HuanyuApiException');
        } catch (HuanyuApiException $e) {
            $this->assertSame(0, $e->getApiCode());
            $this->assertSame('签名错误', $e->getMessage());
            $this->assertSame(1756684800, $e->getApiTime());
        }
    }

    public function testOrderDetailSendsQueryParams(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode([
            'code' => 1, 'msg' => '获取成功', 'data' => ['order_no' => 'HY001'],
        ]))]);
        $this->client($mock)->orderDetail(\HuanyuSdk\Type\OrderDetailQuery::byOrderNo('HY001'));
        $query = $mock->getLastRequest()->getUri()->getQuery();
        $this->assertStringContainsString('order_no=HY001', $query);
        $this->assertStringContainsString('signature=', $query);
    }

    public function testConfirmPaymentOptionalProof(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode([
            'code' => 1, 'msg' => '付款确认成功', 'data' => [],
        ]))]);
        $this->client($mock)->confirmPayment('HY001', 'https://cdn.test/p.png');
        parse_str((string) $mock->getLastRequest()->getBody(), $sent);
        $this->assertSame('HY001', $sent['order_no']);
        $this->assertSame('https://cdn.test/p.png', $sent['payment_proof']);
    }
}
