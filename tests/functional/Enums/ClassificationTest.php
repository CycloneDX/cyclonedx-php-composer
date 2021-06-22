<?php

namespace CycloneDX\Tests\functional\Enums;

use CycloneDX\Enums\Classification;
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Tests\_data\BomSpecData;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ClassificationTest extends TestCase
{

    /**
     * @dataProvider dpSchemaValues
     */
    public function testIsValidValue(string $value): void
    {
        self::assertTrue(Classification::isValidValue($value));
    }

    public function dpSchemaValues(): \Generator
    {
        $allValues = array_unique(array_merge(
            BomSpecData::getClassificationEnumForVersion('1.0'),
            BomSpecData::getClassificationEnumForVersion('1.1'),
            BomSpecData::getClassificationEnumForVersion('1.2'),
            BomSpecData::getClassificationEnumForVersion('1.3'),
        ));
        foreach ($allValues as $value) {
            yield $value => [$value];
        }
    }
}
