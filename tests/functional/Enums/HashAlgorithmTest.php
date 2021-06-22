<?php

namespace CycloneDX\Tests\functional\Enums;

use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Tests\_data\BomSpecData;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class HashAlgorithmTest extends TestCase
{

    /**
     * @dataProvider dpSchemaValues
     */
    public function testIsValidValue(string $value): void
    {
        self::assertTrue(HashAlgorithm::isValidValue($value));
    }

    public function dpSchemaValues(): \Generator
    {
        $allValues = array_unique(array_merge(
            BomSpecData::getHashAlgEnumForVersion('1.0'),
            BomSpecData::getHashAlgEnumForVersion('1.1'),
            BomSpecData::getHashAlgEnumForVersion('1.2'),
            BomSpecData::getHashAlgEnumForVersion('1.3'),
        ));
        foreach ($allValues as $value) {
            yield $value => [$value];
        }
    }
}
