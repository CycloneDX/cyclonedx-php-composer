<?php

namespace CycloneDX\Tests\unit\Composer\Factories;


use Composer\Package\CompletePackageInterface;
use CycloneDX\Composer\Factories\LicenseFactory;
use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Repositories\DisjunctiveLicenseRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\LicenseFactory
 */
class LicenseFactoryTest extends TestCase
{
    public function testMakeFromPackageWithExpression(): void
    {
        $expected = $this->createStub(LicenseExpression::class);
        $factory = $this->createPartialMock(
            LicenseFactory::class,
            ['makeExpression', 'makeDisjunctive', 'makeDisjunctiveLicenseRepository']
        );
        $package = $this->createMock(CompletePackageInterface::class);

        $package->expects(self::once())->method('getLicense')
            ->willReturn(['(LGPL-2.1-only or GPL-3.0-or-later)']);
        $factory->expects(self::once())->method('makeExpression')
            ->with('(LGPL-2.1-only or GPL-3.0-or-later)')
            ->willReturn($expected);

        $got = $factory->makeFromPackage($package);

        self::assertSame($expected, $got);
    }

    public function testMakeFromPackageWithDisjunctiveFallback(): void
    {
        $expected = $this->createStub(DisjunctiveLicenseRepository::class);
        $factory = $this->createPartialMock(
            LicenseFactory::class,
            ['makeExpression', 'makeDisjunctive', 'makeDisjunctiveLicenseRepository']
        );
        $package = $this->createMock(CompletePackageInterface::class);

        $package->expects(self::once())->method('getLicense')
            ->willReturn(['MIT']);

        $factory->method('makeExpression')->willThrowException(new \DomainException());
        $factory->expects(self::once())->method('makeDisjunctiveLicenseRepository')
            ->with('MIT')
            ->willReturn($expected);

        $got = $factory->makeFromPackage($package);

        self::assertSame($expected, $got);
    }

    public function testMakeFromPackageWithDisjunctive(): void
    {
        $expected = $this->createStub(DisjunctiveLicenseRepository::class);
        $factory = $this->createPartialMock(
            LicenseFactory::class,
            ['makeExpression', 'makeDisjunctive', 'makeDisjunctiveLicenseRepository']
        );
        $package = $this->createMock(CompletePackageInterface::class);

        $package->expects(self::once())->method('getLicense')
            ->willReturn(['(LGPL-2.1-only or GPL-3.0-or-later)', 'MIT']);

        $factory->expects(self::once())->method('makeDisjunctiveLicenseRepository')
            ->with('(LGPL-2.1-only or GPL-3.0-or-later)', 'MIT')
            ->willReturn($expected);

        $got = $factory->makeFromPackage($package);

        self::assertSame($expected, $got);
    }

    /**
     * @uses \CycloneDX\Repositories\DisjunctiveLicenseRepository
     */
    public function testMakeDisjunctiveLicenseRepository(): void
    {
        $makeDisjunctiveLicenseRepository = new \ReflectionMethod(
            LicenseFactory::class,
            'makeDisjunctiveLicenseRepository'
        );
        $makeDisjunctiveLicenseRepository->setAccessible(true);

        $disjunctiveLicense1 = $this->createStub(DisjunctiveLicense::class);
        $disjunctiveLicense2 = $this->createStub(DisjunctiveLicense::class);
        $expected = new DisjunctiveLicenseRepository($disjunctiveLicense1, $disjunctiveLicense2);
        $factory = $this->createPartialMock(
            LicenseFactory::class,
            ['makeExpression', 'makeDisjunctive', 'makeDisjunctiveLicenseRepository']
        );
        $factory->expects(self::exactly(2))->method('makeDisjunctive')
            ->withConsecutive(['Foo'], ['Bar'])
            ->willReturnMap(
                [
                    ['Foo', $disjunctiveLicense1],
                    ['Bar', $disjunctiveLicense2],
                ]
            );

        /** @var DisjunctiveLicenseRepository $got */
        $got = $makeDisjunctiveLicenseRepository->invoke(
            $factory,
            'Foo',
            'Bar'
        );

        self::assertEquals($expected, $got);
    }
}
