<?php
declare(strict_types=1);

namespace HuanyuSdk\Tests;

use HuanyuSdk\Signature;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    /** @dataProvider vectorProvider */
    public function testSignMatchesBackendVectors(array $params, string $apiSecret, string $expected): void
    {
        $this->assertSame($expected, Signature::sign($params, $apiSecret));
    }

    public function vectorProvider(): array
    {
        $provider = [];
        foreach (['signature_vectors.json', 'callback_vectors.json'] as $file) {
            $data = json_decode(file_get_contents(__DIR__ . '/../common/vectors/' . $file), true);
            foreach ($data['cases'] as $case) {
                $provider[$case['id']] = [$case['params'], $data['api_secret'], $case['expected_signature']];
            }
        }
        return $provider;
    }
}
