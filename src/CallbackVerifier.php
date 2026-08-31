<?php
declare(strict_types=1);

namespace HuanyuSdk;

/**
 * 商户回调验签。入参为 application/x-www-form-urlencoded body 解析出的键值对（含 signature）。
 * 验签通过后商户应输出含 "success" 的 HTTP 200 响应，否则平台按 5/30/120/600s 重试共 5 次。
 */
class CallbackVerifier
{
    private string $apiSecret;

    public function __construct(string $apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    public function verify(array $formPayload): bool
    {
        if (empty($formPayload['signature'])) {
            return false;
        }
        return hash_equals(
            Signature::sign($formPayload, $this->apiSecret),
            (string) $formPayload['signature']
        );
    }
}
