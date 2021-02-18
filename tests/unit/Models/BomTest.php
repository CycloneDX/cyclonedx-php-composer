<?php

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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

namespace CycloneDX\Tests\uni\Models;

use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * Class BomTest.
 *
 * @covers \CycloneDX\Models\Bom
 */
class BomTest extends TestCase
{
    /** @psalm-var Bom */
    private $bom;

    public function setUp(): void
    {
        parent::setUp();

        $this->bom = new Bom();
    }

    /**
     * @psalm-param Component[] $components
     *
     * @dataProvider componentDataProvider()
     */
    public function testComponentsSetterGetter(array $components): void
    {
        $this->bom->setComponents($components);
        self::assertEquals($components, $this->bom->getComponents());
    }

    /**
     * @psalm-return Generator<array{array<Component>}>
     */
    public function componentDataProvider(): Generator
    {
        yield 'empty' => [[]];
        yield 'some' => [[$this->createMock(Component::class)]];
    }

    public function testVersionSetterGetter(): void
    {
        $version = random_int(1, 255);
        $this->bom->setVersion($version);
        self::assertSame($version, $this->bom->getVersion());
    }

    public function testVersionSetterInvalidValue(): void
    {
        $version = 0 - random_int(1, 255);
        $this->expectException(\DomainException::class);
        $this->bom->setVersion($version);
    }
}
