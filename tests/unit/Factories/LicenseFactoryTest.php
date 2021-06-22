<?php

namespace CycloneDX\Tests\unit\Factories;

use CycloneDX\Factories\LicenseFactory;
use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Spdx\License as SpdxLicenseValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Factories\LicenseFactory
 */
class LicenseFactoryTest extends TestCase
{

    public function testConstructAndgetSpdxLicenseValidator(): void
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $factory = new LicenseFactory($spdxLicenseValidator);
        self::assertSame($spdxLicenseValidator, $factory->getSpdxLicenseValidator());
    }

    public function testSetAndGetSpdxLicenseValidator(): void
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $factory = $this->createPartialMock(LicenseFactory::class, []);

        $factory->setSpdxLicenseValidator($spdxLicenseValidator);
        self::assertSame($spdxLicenseValidator, $factory->getSpdxLicenseValidator());
    }

    /**
     * @uses \CycloneDX\Models\License\LicenseExpression
     */
    public function testMakeExpression(): void
    {
        $makeExpression = new \ReflectionMethod(LicenseFactory::class, 'makeExpression');
        $makeExpression->setAccessible(true);
        $factory = $this->createStub(LicenseFactory::class);
        $expected = new LicenseExpression('(LGPL-2.1-only or GPL-3.0-or-later)');

        $got = $makeExpression->invoke(
            $factory,
            '(LGPL-2.1-only or GPL-3.0-or-later)'
        );

        self::assertEquals($expected, $got);
    }

    /**
     * @uses \CycloneDX\Models\License\DisjunctiveLicense
     */
    public function testMakeDisjunctive(): void
    {
        $makeExpression = new \ReflectionMethod(LicenseFactory::class, 'makeDisjunctive');
        $makeExpression->setAccessible(true);

        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $factory = $this->createPartialMock(LicenseFactory::class, []);
        $factory->setSpdxLicenseValidator($spdxLicenseValidator);
        $expected = DisjunctiveLicense::createFromNameOrId('MIT', $spdxLicenseValidator);

        $got = $makeExpression->invoke(
            $factory,
            'MIT'
        );

        self::assertEquals($expected, $got);
    }

    public function testMakeFromStringAsExpression(): void
    {
        $expected = $this->createStub(LicenseExpression::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeExpression', 'makeDisjunctive']);

        $factory->expects(self::once())->method('makeExpression')
            ->with('(LGPL-2.1-only or GPL-3.0-or-later)')
            ->willReturn($expected);

        $got = $factory->makeFromString('(LGPL-2.1-only or GPL-3.0-or-later)');

        self::assertSame($expected, $got);
    }

    public function testMakeFromStringAsDisjunctive(): void
    {
        $expected = $this->createStub(DisjunctiveLicense::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeExpression', 'makeDisjunctive']);

        $factory->method('makeExpression')
            ->with('foo')
            ->willThrowException(new \DomainException());
        $factory->expects(self::once())->method('makeDisjunctive')
            ->with('foo')
            ->willReturn($expected);


        $got = $factory->makeFromString("foo");

        self::assertSame($expected, $got);
    }
}
