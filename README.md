# huanyu-sdk-php

寰宇（PISCES）商户平台官方 PHP SDK。

## 安装

```bash
composer require crow220/huanyu-sdk-php
```

## 快速上手

```php
use HuanyuSdk\Client;

$client = new Client('你的api_key', '你的api_secret');

// 创建订单（三要素字段是否必填由商户配置决定）
$order = $client->createOrder([
    'order_type'        => '1',        // 1=买入 2=卖出
    'cny_amount'        => '100.00',
    'merchant_order_no' => 'M20260831001', // 商户内唯一，重复会被拒绝
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

- **merchant_order_no 商户内唯一**：同一商户重复单号建单返回"商户单号已存在"错误（不同商户间可重复）。网络超时后可用同一单号安全重试——若返回"已存在"，说明首单已建成，请按单号查单确认状态：

```php
use HuanyuSdk\Exception\HuanyuApiException;

try {
    $order = $client->createOrder($params);
} catch (HuanyuApiException $e) {
    if (strpos($e->getMessage(), '商户单号已存在') !== false) {
        // 首单已建成：按商户单号查单确认状态即可，不要重复下单
        $order = $client->orderDetail(\HuanyuSdk\Type\OrderDetailQuery::byMerchantOrderNo($no));
    } else {
        throw $e;
    }
}
```

- **nonce 自动生成**：平台要求每个请求的 nonce 在 10 分钟窗口内一次性有效（防重放）。SDK 每次调用都会自动生成全新的 timestamp/nonce/signature，失败后直接再次调用即可，无需（也不要）缓存复用请求参数。
- timestamp 为秒级时间戳，本机时钟偏差超过 ±300 秒会验签失败。
