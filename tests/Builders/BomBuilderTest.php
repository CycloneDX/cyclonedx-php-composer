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

namespace Tests\Builders;

use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\Builders\BomBuilder;
use CycloneDX\Composer\Builders\ComponentBuilder;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\BomRefRepository;
use CycloneDX\Core\Repositories\ComponentRepository;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Builders\BomBuilder
 */
class BomBuilderTest extends TestCase
{
    public function testConstruct(): BomBuilder
    {
        $componentBuilder = $this->createMock(ComponentBuilder::class);
        $tool = $this->createMock(Tool::class);

        $builder = new BomBuilder($componentBuilder, $tool);

        self::assertSame($componentBuilder, $builder->getComponentBuilder());
        self::assertSame($tool, $builder->getTool());

        return $builder;
    }

    /**
     * @uses \CycloneDX\Core\Models\Bom
     * @uses \CycloneDX\Core\Models\MetaData
     * @uses \CycloneDX\Core\Models\BomRef
     * @uses \CycloneDX\Core\Repositories\BomRefRepository
     * @uses \CycloneDX\Core\Repositories\ComponentRepository
     * @uses \CycloneDX\Core\Repositories\ToolRepository
     */
    public function testMakeForPackageWithRequires(): void
    {
        $componentBuilder = $this->createMock(ComponentBuilder::class);
        $tool = $this->createStub(Tool::class);
        $builder = new BomBuilder($componentBuilder, $tool);
        $rootPackageOverride = uniqid('vRand-', true);
        $rootPackageWithDeps = $this->createConfiguredMock(
            RootPackageInterface::class,
            [
                'getRequires' => [$this->createConfiguredMock(Link::class, ['getTarget' => 'foo/p1'])],
                'getDevRequires' => [$this->createConfiguredMock(Link::class, ['getTarget' => 'foo/p2'])],
            ]
        );
        $requiredPackageDepP2 = $this->createConfiguredMock(
            PackageInterface::class,
            [
                'getRequires' => [$this->createConfiguredMock(Link::class, ['getTarget' => 'foo/p2'])],
            ]
        );
        $requiredPackageNoDeps = $this->createConfiguredMock(
            PackageInterface::class,
            [
                'getRequires' => [],
            ]
        );
        $requires = $this->createConfiguredMock(
            LockArrayRepository::class,
            ['getPackages' => [$requiredPackageDepP2, $requiredPackageNoDeps]]
        );
        $rootComponentWithDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef(),
                'getName' => 'rootPackage',
                'getGroup' => 'foo',
            ]
        );
        $componentDepP2 = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef(),
                'getName' => 'p1',
                'getGroup' => 'foo',
            ]
        );
        $componentNoDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef(),
                'getName' => 'p2',
                'getGroup' => 'foo',
            ]
        );
        $componentBuilder->method('splitNameAndVendor')
            ->willReturnMap(
                [
                    ['foo/p1', ['p1', 'foo']],
                    ['foo/p2', ['p2', 'foo']],
                ]
            );

        $componentBuilder->expects(self::exactly(3))
            ->method('makeFromPackage')
            ->willReturnMap(
                [
                    [$rootPackageWithDeps, $rootPackageOverride, $rootComponentWithDeps],
                    [$requiredPackageDepP2, null, $componentDepP2],
                    [$requiredPackageNoDeps, null, $componentNoDeps],
                ]
            );
        $rootComponentWithDeps->expects(self::once())
            ->method('setDependenciesBomRefRepository')
            ->with(
                self::callback(
                    function (BomRefRepository $refs) use ($componentNoDeps, $componentDepP2): bool {
                        (new IsIdentical([$componentDepP2->getBomRef(), $componentNoDeps->getBomRef()]))
                            ->evaluate($refs->getBomRefs());

                        return true;
                    }
                )
            )
            ->willReturnSelf();
        $componentDepP2->expects(self::once())
            ->method('setDependenciesBomRefRepository')
            ->with(
                self::callback(
                    function (BomRefRepository $refs) use ($componentNoDeps) {
                        (new IsIdentical([$componentNoDeps->getBomRef()]))
                            ->evaluate($refs->getBomRefs());

                        return true;
                    }
                )
            )
            ->willReturnSelf();
        $componentNoDeps->method('setDependenciesBomRefRepository')
            ->with(null)
            ->willReturnSelf();

        $bom = $builder->makeForPackageWithRequires($rootPackageWithDeps, $requires, $rootPackageOverride);

        self::assertEquals(new ComponentRepository($componentDepP2, $componentNoDeps), $bom->getComponentRepository());
        self::assertSame($rootComponentWithDeps, $bom->getMetaData()->getComponent());
        self::assertSame([$tool], $bom->getMetaData()->getTools()->getTools());
    }
}
