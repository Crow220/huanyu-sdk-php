<?php
declare(strict_types=1);

namespace HuanyuSdk\Type;

class OrderDetailQuery
{
    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
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
