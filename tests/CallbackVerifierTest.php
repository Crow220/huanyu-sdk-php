<?php
declare(strict_types=1);

namespace HuanyuSdk\Tests;

use HuanyuSdk\CallbackVerifier;
use PHPUnit\Framework\TestCase;

class CallbackVerifierTest extends TestCase
{
    /** @dataProvider callbackVectorProvider */
    public function testVerifyAcceptsValidCallback(array $payload, string $apiSecret): void
    {
        $this->assertTrue((new CallbackVerifier($apiSecret))->verify($payload));
    }

    public function testVerifyRejectsTamperedField(): void
    {
        [$payload, $secret] = $this->firstVector();
        $payload['cny_amount'] = '99999.00';
        $this->assertFalse((new CallbackVerifier($secret))->verify($payload));
    }

    public function testVerifyRejectsMissingSignature(): void
    {
        [$payload, $secret] = $this->firstVector();
        unset($payload['signature']);
        $this->assertFalse((new CallbackVerifier($secret))->verify($payload));
    }

    public function callbackVectorProvider(): array
    {
        $provider = [];
        $data = json_decode(file_get_contents(__DIR__ . '/../common/vectors/callback_vectors.json'), true);
        foreach ($data['cases'] as $case) {
            $payload = $case['params'];
            $payload['signature'] = $case['expected_signature'];
            $provider[$case['id']] = [$payload, $data['api_secret']];
        }
        return $provider;
    }

    private function firstVector(): array
    {
        return $this->callbackVectorProvider()['callback-full'];
    }
}
