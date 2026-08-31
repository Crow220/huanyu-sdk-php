<?php
declare(strict_types=1);

namespace HuanyuSdk\Type;

class OrderListFilters
{
    private const FIELDS = [
        'page', 'limit', 'status', 'order_type', 'start_time', 'end_time',
        'order_no', 'merchant_order_no', 'min_cny_amount', 'max_cny_amount',
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
