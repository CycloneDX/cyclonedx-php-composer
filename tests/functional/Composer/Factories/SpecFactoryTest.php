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

namespace CycloneDX\Tests\functional\Composer\Factories;

use CycloneDX\Composer\Factories\SpecFactory;
use CycloneDX\Core\Spec\SpecInterface;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class SpecFactoryTest extends TestCase
{
    /**
     * @dataProvider versionsOfSPECS
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
