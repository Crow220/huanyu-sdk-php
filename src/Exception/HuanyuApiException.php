<?php
declare(strict_types=1);

namespace HuanyuSdk\Exception;

class HuanyuApiException extends \RuntimeException
{
    private ?int $apiTime;

    public function __construct(string $message, int $apiCode, ?int $apiTime = null)
    {
        parent::__construct($message, $apiCode);
        $this->apiTime = $apiTime;
    }

    public function getApiCode(): int
    {
        return $this->code;
    }

    public function getApiTime(): ?int
    {
        return $this->apiTime;
    }
}
