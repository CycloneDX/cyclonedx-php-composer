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

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Repositories\DisjunctiveLicenseRepository
 */
class DisjunctiveLicenseRepositoryTest extends TestCase
{
    public function testAddAndGetLicense(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithName::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license3 = $this->createStub(DisjunctiveLicenseWithName::class);
        $repo = new DisjunctiveLicenseRepository($license1);

        $repo->addLicense($license2, $license3);

        $got = $repo->getLicenses();

        self::assertCount(3, $got);
        self::assertContains($license1, $got);
        self::assertContains($license2, $got);
        self::assertContains($license3, $got);
    }

    public function testConstructAndGet(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);
        $repo = new DisjunctiveLicenseRepository($license1, $license2);
        $got = $repo->getLicenses();

        self::assertCount(2, $got);
        self::assertContains($license1, $got);
        self::assertContains($license2, $got);
    }

    public function testCount(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);
        $repo = new DisjunctiveLicenseRepository($license1);
        $repo->addLicense($license2);

        self::assertSame(2, $repo->count());
    }
}
