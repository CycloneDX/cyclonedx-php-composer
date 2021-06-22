<?php

namespace CycloneDX\Tests\unit\Models\License;

use CycloneDX\Models\License\LicenseExpression;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Models\License\LicenseExpression
 */
class LicenseExpressionTest extends TestCase
{

    public function testConstructAndGet()
    {
        $expression = $this->dpValidLicenseExpressions()[0];

        $license = new LicenseExpression("$expression");
        $got = $license->getExpression();

        self::assertSame($expression, $got);
    }

    public function testConstructThrowsOnUnknownExpression()
    {
        $expression = $this->dpInvalidLicenseExpressions()[0];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/invalid expression/i');

        new LicenseExpression("$expression");
    }

    public function testSetAndGetExpression()
    {
        $expression = $this->dpValidLicenseExpressions()[0];
        $license = $this->createPartialMock(LicenseExpression::class, []);

        $license->setExpression("$expression");
        $got = $license->getExpression();

        self::assertSame($expression, $got);
    }

    public function testSetThrowsOnUnknownExpression()
    {
        $expression = $this->dpInvalidLicenseExpressions()[0];
        $license = $this->createPartialMock(LicenseExpression::class, []);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/invalid expression/i');

        $license->setExpression("$expression");
    }

    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid(string $expression, $expected): void
    {
        $isValid = LicenseExpression::isValid($expression);
        self::assertSame($expected, $isValid);
    }

    public function dpIsValid(): \Generator
    {
        foreach ($this->dpValidLicenseExpressions() as $license) {
            yield $license => [$license, true];
        }
        foreach ($this->dpInvalidLicenseExpressions() as $license) {
            yield $license => [$license, false];
        }
    }

    public function dpValidLicenseExpressions()
    {
        return [
            '(MIT or Apache-2)',
            '(LGPL-2.1-only or GPL-3.0-or-later)',
            ];
    }

    public function dpInvalidLicenseExpressions()
    {
        return  [
            'MIT',
            '(c) me and myself'
        ];
    }
}
