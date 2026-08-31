<?php
declare(strict_types=1);

namespace HuanyuSdk\Type;

/**
 * 订单详情查询条件（id / order_no / merchant_order_no 三选一）。
 * 推荐直接用数组：$client->orderDetail(['order_no' => 'HY001'])，
 * 工厂方法保留作为等价写法。
 */
class OrderDetailQuery
{
    private const FIELDS = ['id', 'order_no', 'merchant_order_no'];

    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromArray(array $params): self
    {
        return new self(array_intersect_key($params, array_flip(self::FIELDS)));
    }

    public static function byId(int $id): self
    {
        return new self(['id' => (string) $id]);
    }

    public static function byOrderNo(string $orderNo): self
    {
        return new self(['order_no' => $orderNo]);
    }

    public static function byMerchantOrderNo(string $merchantOrderNo): self
    {
        return new self(['merchant_order_no' => $merchantOrderNo]);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
