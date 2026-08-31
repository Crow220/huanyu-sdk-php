<?php
declare(strict_types=1);

namespace HuanyuSdk;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use HuanyuSdk\Exception\HuanyuApiException;
use HuanyuSdk\Type\CreateOrderParams;
use HuanyuSdk\Type\OrderDetailQuery;
use HuanyuSdk\Type\OrderListFilters;

/**
 * 商户 API 客户端。注意：平台对 merchant_order_no 不去重，createOrder 重试可能重复建单，
 * 调用方必须自行保证商户单号唯一。
 */
class Client
{
    public const DEFAULT_BASE_URL = 'https://api.pisces-pay.cn/addons/huanyu';

    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private ClientInterface $http;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        ?string $baseUrl = null,
        ?ClientInterface $httpClient = null,
        int $timeout = 30
    ) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = rtrim($baseUrl ?? self::DEFAULT_BASE_URL, '/');
        $this->http = $httpClient ?? new GuzzleClient(['timeout' => $timeout]);
    }

    public function createOrder($params): array
    {
        if (is_array($params)) {
            $params = CreateOrderParams::fromArray($params);
        }
        return $this->request('POST', '/merchant/createOrder', $params->toArray());
    }

    public function orderList($filters = []): array
    {
        if (is_array($filters)) {
            $filters = OrderListFilters::fromArray($filters);
        }
        return $this->request('GET', '/merchant/orderListApi', $filters->toArray());
    }

    public function orderDetail(OrderDetailQuery $query): array
    {
        return $this->request('GET', '/merchant/orderDetailApi', $query->toArray());
    }

    public function uploadPaymentProof(string $orderNo, string $proofImageUrl): array
    {
        return $this->request('POST', '/merchant/uploadPaymentProof', [
            'order_no' => $orderNo, 'proof_image_url' => $proofImageUrl,
        ]);
    }

    public function confirmPayment(string $orderNo, ?string $paymentProof = null): array
    {
        $params = ['order_no' => $orderNo];
        if ($paymentProof !== null) {
            $params['payment_proof'] = $paymentProof;
        }
        return $this->request('POST', '/merchant/confirmPayment', $params);
    }

    private function request(string $method, string $path, array $params): array
    {
        $params['api_key'] = $this->apiKey;
        $params['timestamp'] = (string) time();
        $params['nonce'] = $this->randomNonce();
        $params['signature'] = Signature::sign($params, $this->apiSecret);

        $options = $method === 'GET'
            ? ['query' => $params]
            : ['form_params' => $params];
        $response = $this->http->request($method, $this->baseUrl . $path, $options);

        $body = json_decode((string) $response->getBody(), true);
        if (!is_array($body) || !isset($body['code'])) {
            throw new \RuntimeException('平台响应格式异常: ' . substr((string) $response->getBody(), 0, 200));
        }
        if ((int) $body['code'] !== 1) {
            throw new HuanyuApiException(
                (string) ($body['msg'] ?? '未知错误'),
                (int) $body['code'],
                isset($body['time']) ? (int) $body['time'] : null
            );
        }
        return $body['data'] ?? [];
    }

    private function randomNonce(int $length = 16): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $length);
    }
}
