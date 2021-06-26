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

namespace CycloneDX\Tests\Core\Models\License;

use CycloneDX\Core\Models\License\LicenseExpression;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Models\License\LicenseExpression
 */
class LicenseExpressionTest extends TestCase
{
    public function testConstructAndGet(): void
    {
        $expression = $this->dpValidLicenseExpressions()[0];

        $license = new LicenseExpression("$expression");
        $got = $license->getExpression();

        self::assertSame($expression, $got);
    }

    public function testConstructThrowsOnUnknownExpression(): void
    {
        $expression = $this->dpInvalidLicenseExpressions()[0];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/invalid expression/i');

        new LicenseExpression("$expression");
    }

    public function testSetAndGetExpression(): void
    {
        $expression = $this->dpValidLicenseExpressions()[0];
        $license = $this->createPartialMock(LicenseExpression::class, []);

        $license->setExpression("$expression");
        $got = $license->getExpression();

        self::assertSame($expression, $got);
    }

    public function testSetThrowsOnUnknownExpression(): void
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
        return [
            'MIT',
            '(c) me and myself',
        ];
    }
}
