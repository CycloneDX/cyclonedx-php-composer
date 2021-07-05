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

use Composer\Package\Package;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\Factories\BomFactory;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Composer\Locker;
use CycloneDX\Core\Repositories\ComponentRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\BomFactory
 */
class BomFactoryTest extends TestCase
{
    /**
     * @uses \CycloneDX\Core\Models\Bom
     */
    public function testMakeFromLocker(): void
    {
        $expectedComponents = $this->createStub(ComponentRepository::class);
        $package1 = $this->createStub(Package::class);
        $package2 = $this->createStub(Package::class);
        $locker = $this->createMock(Locker::class);
        $lockedRepository = $this->createConfiguredMock(LockArrayRepository::class, ['getPackages' => [$package1, $package2]]);
        $componentFactory = $this->createMock(ComponentFactory::class);
        $factory = new BomFactory(false, true, $componentFactory);

        $locker->expects(self::once())->method('getLockedRepository')
            ->with(false, true)
            ->willReturn($lockedRepository);
        $componentFactory->expects(self::once())->method('makeFromPackages')
            ->with([$package1, $package2])
            ->willReturn($expectedComponents);

        $got = $factory->makeFromLocker($locker);
        $gotComponents = $got->getComponentRepository();

        self::assertInstanceOf(ComponentRepository::class, $gotComponents);
        self::assertSame($expectedComponents, $gotComponents);
    }
}
