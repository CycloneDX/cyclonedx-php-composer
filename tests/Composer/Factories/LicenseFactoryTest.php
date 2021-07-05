<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\Composer\Factories;

use Composer\Package\CompletePackageInterface;
use CycloneDX\Composer\Factories\LicenseFactory;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
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
     * @uses \CycloneDX\Core\Repositories\DisjunctiveLicenseRepository
     */
    public function testMakeDisjunctiveLicenseRepository(): void
    {
        /** @see \CycloneDX\Composer\Factories\LicenseFactory::makeDisjunctiveLicenseRepository() */
        $makeDisjunctiveLicenseRepository = new \ReflectionMethod(
            LicenseFactory::class,
            'makeDisjunctiveLicenseRepository'
        );
        $makeDisjunctiveLicenseRepository->setAccessible(true);

        $disjunctiveLicense1 = $this->createStub(DisjunctiveLicenseWithName::class);
        $disjunctiveLicense2 = $this->createStub(DisjunctiveLicenseWithId::class);
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
