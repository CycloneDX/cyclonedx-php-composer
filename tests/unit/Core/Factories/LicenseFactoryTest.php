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

namespace CycloneDX\Tests\unit\Core\Factories;

use CycloneDX\Core\Factories\LicenseFactory;
use CycloneDX\Core\Models\License\DisjunctiveLicense;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Spdx\License as SpdxLicenseValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Factories\LicenseFactory
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
     * @uses \CycloneDX\Core\Models\License\LicenseExpression
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
     * @uses \CycloneDX\Core\Models\License\DisjunctiveLicense
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

        $got = $factory->makeFromString('foo');

        self::assertSame($expected, $got);
    }
}
