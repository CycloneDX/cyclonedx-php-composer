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

namespace CycloneDX\Tests\Core\Repositories;

use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Repositories\BomRefRepository;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Repositories\BomRefRepository
 */
class BomRefRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new BomRefRepository();

        self::assertSame([], $repo->getBomRefs());
        self::assertCount(0, $repo);
    }

    /**
     * @dataProvider dpNonEmptyConstructor
     *
     * @param BomRef[] $bomRefs
     * @param BomRef[] $expectedContains
     */
    public function testNonEmptyConstructor(array $bomRefs, array $expectedContains): void
    {
        $repo = new BomRefRepository(...$bomRefs);

        self::assertSameSize($expectedContains, $repo);
        self::assertSameSize($expectedContains, $repo->getBomRefs());
        foreach ($expectedContains as $expectedContain) {
            self::assertContains($expectedContain, $repo->getBomRefs());
        }
    }

    public function dpNonEmptyConstructor(): Generator
    {
        $r1 = new BomRef();
        $r2 = new BomRef();
        $r3 = new BomRef('foo');
        $r4 = new BomRef('foo');
        $r5 = new BomRef('bar');

        yield 'identical' => [
            [$r1, $r1],
            [$r1],
        ];

        yield 'different' => [
            [$r1, $r2, $r3, $r4, $r5, $r1, $r2, $r3, $r4, $r5],
            [$r1, $r2, $r3, $r4, $r5],
        ];
    }

    /**
     * @dataProvider dpAddBomRef
     *
     * @param BomRef[] $initial
     * @param BomRef[] $add
     * @param BomRef[] $expected
     */
    public function testAddBomRef(array $initial, array $add, array $expectedContains): void
    {
        $repo = new BomRefRepository(...$initial);

        $actual = $repo->addBomRef(...$add);

        self::assertSame($actual, $repo);
        self::assertSameSize($expectedContains, $repo);
        self::assertSameSize($expectedContains, $repo->getBomRefs());
        foreach ($expectedContains as $expectedContain) {
            self::assertContains($expectedContain, $repo->getBomRefs());
        }
    }

    public function dpAddBomRef(): Generator
    {
        $r1 = new BomRef();
        $r2 = new BomRef();
        $r3 = new BomRef('foo');
        $r4 = new BomRef('foo');
        $r5 = new BomRef('bar');

        yield 'identical' => [
            [$r1],
            [$r1],
            [$r1],
        ];

        yield 'different' => [
            [$r1, $r2],
            [$r2, $r3, $r4, $r5],
            [$r1, $r2, $r3, $r4, $r5],
        ];
    }
}
