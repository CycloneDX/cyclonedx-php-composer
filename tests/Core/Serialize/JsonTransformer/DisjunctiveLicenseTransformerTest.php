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

namespace CycloneDX\Tests\Core\Serialize\JsonTransformer;

use CycloneDX\Core\Models\License\AbstractDisjunctiveLicense;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\TransformerFactory;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseTransformer
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\AbstractTransformer
 */
class DisjunctiveLicenseTransformerTest extends TestCase
{
    public function testConstructor(): void
    {
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new DisjunctiveLicenseTransformer($factory);
        self::assertSame($factory, $transformer->getTransformerFactory());
    }

    /**
     * @dataProvider dpTransform
     */
    public function testTransform(AbstractDisjunctiveLicense $license, array $expected): void
    {
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new DisjunctiveLicenseTransformer($factory);
        self::assertSame($expected, $transformer->transform($license));
    }

    public function dpTransform(): Generator
    {
        yield 'prefer id' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithId::class, [
                'getId' => 'foo',
            ]),
            ['license' => [
                'id' => 'foo',
            ]],
        ];

        yield 'name' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithName::class, [
                'getName' => 'bar',
            ]),
            ['license' => [
                'name' => 'bar',
            ]],
        ];

        yield 'optional url' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithName::class, [
                'getName' => 'foo',
                'getUrl' => 'http://foo.bar',
                ]),
            ['license' => [
                'name' => 'foo',
                'url' => 'http://foo.bar',
            ]],
        ];
    }

    public function testTransformThrowsOnUnknown(): void
    {
        $license = $this->createStub(AbstractDisjunctiveLicense::class);
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new DisjunctiveLicenseTransformer($factory);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/unsupported license class/i');

        $transformer->transform($license);
    }
}
