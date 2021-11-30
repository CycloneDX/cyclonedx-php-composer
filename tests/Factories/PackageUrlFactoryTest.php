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

use CycloneDX\Composer\Factories\PackageUrlFactory;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Repositories\HashRepository;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\PackageUrlFactory
 */
class PackageUrlFactoryTest extends TestCase
{
    /**
     * @dataProvider dpMakeFromComponent
     *
     * @uses         \PackageUrl\PackageUrl
     * @uses         \CycloneDX\Core\Repositories\HashRepository
     */
    public function testMakeFromComponent(Component $component, PackageUrl $expected): void
    {
        $factory = new PackageUrlFactory();

        $actual = $factory->makeFromComponent($component);

        self::assertEquals($expected, $actual);
    }

    public function dpMakeFromComponent(): \Generator
    {
        yield 'minimal' => [
            $this->createConfiguredMock(
                Component::class,
                [
                    'getName' => 'foo',
                ]
            ),
            new PackageUrl('composer', 'foo'),
        ];

        yield 'with namespace' => [
            $this->createConfiguredMock(
                Component::class,
                [
                    'getName' => 'foo',
                    'getGroup' => 'bar',
                ]
            ),
            (new PackageUrl('composer', 'foo'))
                ->setNamespace('bar'),
        ];

        yield 'with version' => [
            $this->createConfiguredMock(
                Component::class,
                [
                    'getName' => 'foo',
                    'getVersion' => 'v1.2.3',
                ]
            ),
            (new PackageUrl('composer', 'foo'))
                ->setVersion('v1.2.3'),
        ];

        yield 'with SHA-1 Hash' => [
            $this->createConfiguredMock(
                Component::class,
                [
                    'getName' => 'foo',
                    'getHashRepository' => new HashRepository(['SHA-1' => '3da541559918a808c2402bba5012f6c60b27661c']),
                ]
            ),
            (new PackageUrl('composer', 'foo'))
                ->setChecksums(['sha1:3da541559918a808c2402bba5012f6c60b27661c']),
        ];
    }
}
