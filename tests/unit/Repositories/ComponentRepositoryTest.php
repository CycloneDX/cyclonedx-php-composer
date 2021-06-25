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

namespace CycloneDX\Tests\unit\Repositories;

use CycloneDX\Models\Component;
use CycloneDX\Repositories\ComponentRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Repositories\ComponentRepository
 */
class ComponentRepositoryTest extends TestCase
{
    public function testAddAndGetComponent(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $component3 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1);
        $repo->addComponent($component2, $component3);
        $got = $repo->getComponents();

        self::assertCount(3, $got);
        self::assertContains($component1, $got);
        self::assertContains($component2, $got);
        self::assertContains($component3, $got);
    }

    public function testCount(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1);
        $repo->addComponent($component2);

        self::assertSame(2, $repo->count());
    }

    public function testConstructAndGet(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1, $component2);
        $got = $repo->getComponents();

        self::assertCount(2, $got);
        self::assertContains($component1, $got);
        self::assertContains($component2, $got);
    }
}
