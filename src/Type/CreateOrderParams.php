<?php
declare(strict_types=1);

namespace HuanyuSdk\Type;

class CreateOrderParams
{
    private const FIELDS = [
        'order_type', 'payment_amount', 'payment_method', 'customer_name', 'id_card',
        'mobile', 'remark', 'merchant_order_no',
    ];

    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromArray(array $params): self
    {
        return new self(array_intersect_key($params, array_flip(self::FIELDS)));
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
