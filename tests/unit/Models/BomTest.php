<?php

declare(strict_types=1);

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

    // region components setter&getter&modifiers

    /**
     * @dataProvider componentDataProvider()
     */
    public function testComponentsSetterGetter(array $components): void
    {
        $expected = array_values($components);
        $this->bom->setComponents($components);
        self::assertEquals($expected, $this->bom->getComponents());
    }

    public function testComponentsSetterInvalid(): void
    {
        $components = [$this->createMock(\stdClass::class)];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/not a component/i');
        $this->bom->setComponents($components);
    }

    /**
     * @dataProvider componentDataProvider()
     */
    public function testComponentsAdd(array $components): void
    {
        if ('assoc' === $this->dataName() && 0 > version_compare(PHP_VERSION, '8.0.0')) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $expected = array_values($components);
        $this->bom->addComponent(...$components);
        self::assertEquals($expected, $this->bom->getComponents());
    }

    /**
     * @psalm-return Generator<array{array<Component>}>
     */
    public function componentDataProvider(): Generator
    {
        yield 'empty' => [[]];
        yield 'some' => [[$this->createMock(Component::class), $this->createMock(Component::class)]];
        yield 'assoc' => [['foo' => $this->createMock(Component::class)]];
    }

    // endregion components setter&getter&modifiers

    // region version setter&getter

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

    // endregion version setter&getter
}
