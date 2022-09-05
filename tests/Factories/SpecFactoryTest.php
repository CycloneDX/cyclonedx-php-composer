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

namespace CycloneDX\Tests\Factories;

use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Core\Spec\Spec11;
use CycloneDX\Core\Spec\Spec12;
use CycloneDX\Core\Spec\Spec13;
use CycloneDX\Core\Spec\SpecInterface;
use Generator;
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
     * @uses \CycloneDX\Core\Spec\Spec11
     * @uses \CycloneDX\Core\Spec\Spec12
     * @uses \CycloneDX\Core\Spec\Spec13
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
        $this->expectException(\UnexpectedValueException::class);
        $this->factory->make($version);
    }

    /**
     * @dataProvider versionsOfSPECS
     *
     * @coversNothing
     */
    public function testSpecsVersion(string $version): void
    {
        $className = SpecFactory::SPECS[$version];
        $spec = new $className();
        self::assertInstanceOf(SpecInterface::class, $spec);
        self::assertSame($version, $spec->getVersion());
    }

    public static function versionsOfSPECS(): Generator
    {
        foreach (array_keys(SpecFactory::SPECS) as $version) {
            yield $version => [$version];
        }
    }

    public function testSpecsContainLatest(): void
    {
        self::assertArrayHasKey(SpecFactory::VERSION_LATEST, SpecFactory::SPECS);
    }
}
