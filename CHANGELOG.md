# Changelog

## 1.0.0 - 2026-09-01

首个公开发布。

### 功能

- `Client`：封装平台全部对外端点——`createOrder`（唯一下单方法，三要素是否必填由商户配置决定）、`orderList`、`orderDetail`、`uploadPaymentProof`、`confirmPayment`；自动注入通用参数（api_key / timestamp / nonce / signature），统一解析 `{code, msg, data, time}` 信封，`code != 1` 抛 `HuanyuApiException`。
- `Signature`：与后端 `MerchantAuth` 真源一致的 MD5 签名（数组 JSON 化保序不转义、ksort、跳空值），由共享规格仓的后端实测向量锁定（含中文、空值、乱序键数组、`\/` 转义等 8 组用例）。
- `CallbackVerifier`：回调通知验签（`hash_equals` 恒时比较）。
- 参数类型 `CreateOrderParams` / `OrderListFilters` / `OrderDetailQuery`：字段白名单过滤，未知字段丢弃。

### 注意

- 下单金额参数为 `cny_amount`（字符串金额，如 `'100.00'`）。
- `merchant_order_no` 商户内唯一：重复建单返回"商户单号已存在"，超时可凭同一单号安全重试（示例见 README）。
- nonce 每次调用自动全新生成，时间窗内一次性有效（平台防重放）。

### 环境要求

- PHP >= 7.4，Guzzle ^7.0；CI 矩阵覆盖 PHP 7.4–8.3。
