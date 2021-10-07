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

namespace CycloneDX\Tests\Composer;

use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Repository\LockArrayRepository;
use Composer\Semver\Constraint\MatchAllConstraint;
use CycloneDX\Composer\Builders\ComponentBuilder;
use CycloneDX\Composer\ToolUpdater;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\HashRepository;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\ToolUpdater
 */
class ToolUpdaterTest extends TestCase
{
    public function testUpdateTool(): void
    {
        $componentBuilder = $this->createMock(ComponentBuilder::class);
        $updater = new ToolUpdater($componentBuilder);
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => 'myVendor',
                'getName' => 'myName',
            ]
        );
        $lockRepo = $this->createMock(LockArrayRepository::class);
        $hashes = $this->createStub(HashRepository::class);
        $package = $this->createStub(PackageInterface::class);
        $alias = $this->createStub(AliasPackage::class);
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getVersion' => 'myVersion',
                'getHashRepository' => $hashes,
            ]
        );

        $lockRepo->method('findPackages')
            ->with('myVendor/myName', new IsInstanceOf(MatchAllConstraint::class))
            ->willReturn([$alias, $package]);

        $componentBuilder->method('makeFromPackage')
            ->with($package)
            ->willReturn($component);

        $tool->expects(self::once())
            ->method('setVersion')
            ->with('myVersion');

        $tool->expects(self::once())
            ->method('setHashRepository')
            ->with($hashes);

        $updated = $updater->updateTool($tool, $lockRepo);

        self::assertTrue($updated);
    }

    public function testUpdateToolWithputNameAndVendor(): void
    {
        $componentBuilder = $this->createMock(ComponentBuilder::class);
        $updater = new ToolUpdater($componentBuilder);
        $tool = $this->createMock(Tool::class);
        $lockRepo = $this->createStub(LockArrayRepository::class);

        $tool->expects(self::never())
            ->method('setVersion');

        $tool->expects(self::never())
            ->method('setHashRepository');

        $updated = $updater->updateTool($tool, $lockRepo);

        self::assertFalse($updated);
    }

    public function testUpdateToolThatIsUnknown(): void
    {
        $componentBuilder = $this->createMock(ComponentBuilder::class);
        $updater = new ToolUpdater($componentBuilder);
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => 'myVendor',
                'getName' => 'myName',
            ]
        );
        $lockRepo = $this->createMock(LockArrayRepository::class);

        $lockRepo->method('findPackages')
            ->with('myVendor/myName', new IsInstanceOf(MatchAllConstraint::class))
            ->willReturn([]);

        $tool->expects(self::never())
            ->method('setVersion');

        $tool->expects(self::never())
            ->method('setHashRepository');

        $updated = $updater->updateTool($tool, $lockRepo);

        self::assertFalse($updated);
    }

    public function testUpdateToolThatDoesNotConvertToComponent(): void
    {
        $componentBuilder = $this->createMock(ComponentBuilder::class);
        $updater = new ToolUpdater($componentBuilder);
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => 'myVendor',
                'getName' => 'myName',
            ]
        );
        $lockRepo = $this->createMock(LockArrayRepository::class);
        $package = $this->createStub(PackageInterface::class);

        $lockRepo->method('findPackages')
            ->with('myVendor/myName', new IsInstanceOf(MatchAllConstraint::class))
            ->willReturn([$package]);

        $componentBuilder->method('makeFromPackage')
            ->with($package)
            ->willThrowException(new \Exception());

        $tool->expects(self::never())
            ->method('setVersion');

        $tool->expects(self::never())
            ->method('setHashRepository');

        $updated = $updater->updateTool($tool, $lockRepo);

        self::assertFalse($updated);
    }
}
