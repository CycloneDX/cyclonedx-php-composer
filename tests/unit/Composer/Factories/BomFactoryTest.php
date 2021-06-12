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

namespace CycloneDX\Tests\unit\Composer\Factories;

use Composer\Package\Package;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\Factories\BomFactory;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Composer\Locker;
use CycloneDX\Models\Bom;
use CycloneDX\Models\Component;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\BomFactory
 *
 * @uses   \CycloneDX\Models\Bom
 */
class BomFactoryTest extends TestCase
{
    public function testMakeFromLocker(): void
    {
        $expectedComponent1 = $this->createStub(Component::class);
        $expectedComponent2 = $this->createStub(Component::class);
        $expected = (new Bom())->setComponents([$expectedComponent1, $expectedComponent2]);
        $package1 = $this->createStub(Package::class);
        $package2 = $this->createStub(Package::class);
        $lockArrayRepository = $this->createConfiguredMock(
            LockArrayRepository::class,
            ['getPackages' => [$package1, $package2]]
        );
        $locker = $this->createConfiguredMock(Locker::class, ['getLockedRepository' => $lockArrayRepository]);
        $componentFactory = $this->createMock(ComponentFactory::class);
        $factory = new BomFactory(false, true, $componentFactory);

        $componentFactory->expects(self::exactly(2))->method('makeFromPackage')
            ->withConsecutive([$package1], [$package2])
            ->willReturnOnConsecutiveCalls($expectedComponent1, $expectedComponent2);

        $got = $factory->makeFromLocker($locker);

        self::assertEquals($expected, $got);
        self::assertSame($expected->getComponents(), $got->getComponents());
    }
}
