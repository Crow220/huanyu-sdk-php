<?php
declare(strict_types=1);

namespace HuanyuSdk;

/**
 * 签名算法实现，逐步对应 huanyu-sdk-common/spec/signature.md。
 * 算法真源：huanyu-backend MerchantAuth::generateSignature，由测试向量锁定。
 */
class Signature
{
    public static function sign(array $params, string $apiSecret): string
    {
        // 1. 移除 signature 字段本身
        unset($params['signature']);

        // 2. 数组参数 JSON 化（保持插入顺序、中文不转义）
        $processed = [];
        foreach ($params as $key => $value) {
            $processed[$key] = is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                : $value;
        }

        // 3. 顶层按键名升序
        ksort($processed);

        // 4. 跳过空串与 null，拼 key=value&
        $stringToSign = '';
        foreach ($processed as $key => $value) {
            if ($value !== '' && $value !== null) {
                $stringToSign .= $key . '=' . $value . '&';
            }
        }
        $stringToSign = rtrim($stringToSign, '&');

        // 5/6. 追加 api_secret 后取大写 MD5
        return strtoupper(md5($stringToSign . '&api_secret=' . $apiSecret));
    }
}
