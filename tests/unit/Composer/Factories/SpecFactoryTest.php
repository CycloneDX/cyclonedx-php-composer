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

namespace CycloneDX\Tests\unit\Composer\Factories;

use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Specs\Spec11;
use CycloneDX\Specs\Spec12;
use CycloneDX\Specs\Spec13;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\SpecFactory
 */
class SpecFactoryTest extends TestCase
{
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new SpecFactory();
    }

    /**
     * @psalm-param class-string $expectedClassName
     *
     * @dataProvider dpMakeExpected
     *
     * @uses \CycloneDX\Spec\Spec11
     * @uses \CycloneDX\Spec\Spec12
     * @uses \CycloneDX\Spec\Spec13
     */
    public function testMakeExpected(string $version, string $expectedClassName): void
    {
        $spec = $this->factory->make($version);
        self::assertInstanceOf($expectedClassName, $spec);
    }

    public static function dpMakeExpected(): Generator
    {
        // yield '1.0' => ['1.0', Spec10::class]; // not implemented
        yield '1.1' => ['1.1', Spec11::class];
        yield '1.2' => ['1.2', Spec12::class];
        yield '1.3' => ['1.3', Spec13::class];
    }

    public function testMakeThrowsOnUnexpected(): void
    {
        $version = uniqid('unknown', true);
        $this->expectException(InvalidArgumentException::class);
        $this->factory->make($version);
    }
}
