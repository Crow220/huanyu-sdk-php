# huanyu-sdk-php

寰宇（PISCES）商户平台官方 PHP SDK。

## 安装

composer require crow220/huanyu-sdk-php

## 快速上手

```php
use HuanyuSdk\Client;

$client = new Client('你的api_key', '你的api_secret');

// 创建订单（三要素字段是否必填由商户配置决定）
$order = $client->createOrder([
    'order_type'        => '1',        // 1=买入 2=卖出
    'payment_amount'    => '100.00',
    'merchant_order_no' => 'M20260831001', // 商户自己保证唯一！
]);
// $order['result_status'] === 'pending_identity' 时引导用户访问 $order['identity_url']

// 查询
$list  = $client->orderList(['status' => 'paid,confirmed', 'page' => 1, 'limit' => 20]);
$detail = $client->orderDetail(\HuanyuSdk\Type\OrderDetailQuery::byOrderNo($order['order_no']));

// 卖单确认付款 / 上传凭证
$client->confirmPayment($order['order_no']);
$client->uploadPaymentProof($order['order_no'], 'https://your.cdn/proof.png');
```

## 回调处理

```php
use HuanyuSdk\CallbackVerifier;

$verifier = new CallbackVerifier('你的api_secret');
$payload = $_POST;
if (!$verifier->verify($payload)) {
    http_response_code(403);
    exit;
}
// ...业务处理（回调仅在订单 completed 时推送）
echo 'success'; // 必须响应 HTTP 200 且含 success，否则平台按 5/30/120/600s 重试 5 次
```

## 重要注意事项

- **平台不校验 merchant_order_no 唯一性**：网络超时后盲目重试 `createOrder` 会重复建单，请用商户单号做本地幂等。
- timestamp 为秒级时间戳，本机时钟偏差超过 ±300 秒会验签失败。
