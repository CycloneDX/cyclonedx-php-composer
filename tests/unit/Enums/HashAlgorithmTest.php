<?php

namespace CycloneDX\Tests\unit\Enums;

use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Tests\_data\BomSpecData;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Enums\HashAlgorithm
 */
class HashAlgorithmTest extends TestCase
{

    /**
     * @dataProvider dpKnownValues
     * @dataProvider dpUnknownValue
     */
    public function testIsValidValue(string $value, bool $expected): void
    {
        self::assertSame($expected, HashAlgorithm::isValidValue($value));
    }

    public function dpKnownValues(): \Generator
    {
        $allValues = (new \ReflectionClass(HashAlgorithm::class))->getConstants();
        foreach ($allValues as $value) {
            yield $value => [$value, true];
        }
    }

    public function dpUnknownValue(): \Generator {
        yield 'invalid' => ['UnknownAlg', false];
    }
}
