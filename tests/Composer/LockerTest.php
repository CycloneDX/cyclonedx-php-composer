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

namespace CycloneDX\Tests\unit\Composer;

use Composer\Package\Locker as ComposerPackageLocker;
use Composer\Package\PackageInterface;
use Composer\Repository\LockArrayRepository;
use CycloneDX\Composer\Locker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Locker
 */
class LockerTest extends TestCase
{
    public function testGetSetComposerPackageLocker(): void
    {
        $composerLocker = $this->createStub(ComposerPackageLocker::class);
        $locker = new Locker($composerLocker);

        $got = $locker->getComposerPackageLocker();

        self::assertSame($composerLocker, $got);
    }

    // region getLockedRepository

    public function testGetLockedRepositoryWithoutAnyExclusions(): void
    {
        $repo = $this->createStub(LockArrayRepository::class);
        $composerLocker = $this->createPartialMock(ComposerPackageLocker::class, ['getLockedRepository']);
        $locker = new Locker($composerLocker);

        $composerLocker->expects(self::once())->method('getLockedRepository')
            ->with(true)
            ->willReturn($repo);

        $got = $locker->getLockedRepository(false, false);

        self::assertSame($repo, $got);
    }

    public function testGetLockedRepositoryWithDevExclusions(): void
    {
        $repo = $this->createStub(LockArrayRepository::class);
        $composerLocker = $this->createPartialMock(ComposerPackageLocker::class, ['getLockedRepository']);
        $locker = new Locker($composerLocker);

        $composerLocker->expects(self::once())->method('getLockedRepository')
            ->with(false)
            ->willReturn($repo);

        $got = $locker->getLockedRepository(true, false);

        self::assertSame($repo, $got);
    }

    public function testGetLockedRepositoryWithPluginExclusions(): void
    {
        $package1 = $this->createConfiguredMock(PackageInterface::class, ['getType' => 'library']);
        $package2 = $this->createConfiguredMock(PackageInterface::class, ['getType' => 'composer-plugin']);
        $repo = $this->createPartialMock(LockArrayRepository::class, ['getPackages', 'removePackage']);
        $composerLocker = $this->createPartialMock(ComposerPackageLocker::class, ['getLockedRepository']);
        $locker = new Locker($composerLocker);

        $composerLocker->expects(self::once())->method('getLockedRepository')
            ->with(true)
            ->willReturn($repo);
        $repo->expects(self::once())->method('getPackages')
            ->willReturn([$package1, $package2]);
        $repo->expects(self::once())->method('removePackage')
            ->with($package2);

        $got = $locker->getLockedRepository(false, true);

        self::assertSame($repo, $got);
    }

    // endregion getLockedRepository
}
