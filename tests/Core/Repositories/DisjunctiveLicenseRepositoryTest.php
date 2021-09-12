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
    public function testEmptyConstructor(): void
    {
        $repo = new DisjunctiveLicenseRepository();

        self::assertCount(0, $repo);
        self::assertSame([], $repo->getLicenses());
    }

    public function testNonEmptyConstruct(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);

        $repo = new DisjunctiveLicenseRepository($license1, $license2, $license1, $license2);

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getLicenses());
        self::assertContains($license1, $repo->getLicenses());
        self::assertContains($license2, $repo->getLicenses());
    }

    public function testAddLicense(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithName::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license3 = $this->createStub(DisjunctiveLicenseWithName::class);
        $repo = new DisjunctiveLicenseRepository($license1, $license2);

        $actual = $repo->addLicense($license2, $license3, $license3);

        self::assertSame($repo, $actual);
        self::assertCount(3, $repo);
        self::assertCount(3, $repo->getLicenses());
        self::assertContains($license1, $repo->getLicenses());
        self::assertContains($license2, $repo->getLicenses());
        self::assertContains($license3, $repo->getLicenses());
    }
}
