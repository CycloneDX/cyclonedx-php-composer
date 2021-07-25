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

namespace CycloneDX\Tests\Composer\Factories;

use Composer\Package\PackageInterface;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\Factories\BomFactory;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\ComponentRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\BomFactory
 */
class BomFactoryTest extends TestCase
{
    public function testConstruct(): BomFactory
    {
        $componentFactory = $this->createMock(ComponentFactory::class);
        $tool = $this->createMock(Tool::class);

        $factory = new BomFactory($componentFactory, $tool);

        self::assertSame($componentFactory, $factory->getComponentFactory());
        self::assertSame($tool, $factory->getTool());

        return $factory;
    }

    /**
     * @uses \CycloneDX\Core\Models\Bom
     * @uses \CycloneDX\Core\Models\MetaData
     */
    public function testMakeForPackageWithComponents(): void
    {
        $componentFactory = $this->createMock(ComponentFactory::class);
        $factory = new BomFactory($componentFactory);
        $package = $this->createMock(PackageInterface::class);
        $componentsPackages = [$this->createStub(PackageInterface::class)];
        $components = $this->createConfiguredMock(LockArrayRepository::class, ['getPackages' => $componentsPackages]);

        $componentFactory->expects(self::once())->method('makeFromPackages')
            ->with($componentsPackages)
            ->willReturn($this->createStub(ComponentRepository::class));

        $factory->makeForPackageWithComponents($package, $components);
    }
}
