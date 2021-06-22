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

namespace CycloneDX\Tests\unit\Models\License;

use CycloneDX\Models\License\DisjunctiveLicense;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Models\License\DisjunctiveLicense
 */
class DisjunctiveLicenseTest extends TestCase
{
    public function testCreateWithIdAndGetId()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(true);
        $spdxLicenseValidator->method('getLicense')->willReturn('bar');
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        self::assertSame('bar', $license->getId());
        self::assertNull($license->getName());
    }

    public function testCreateWithnameAndGetName()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->willReturn(null);
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        self::assertSame('foo', $license->getName());
        self::assertNull($license->getId());
    }

    public function testSetAndGetUrl()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->willReturn(null);
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        $license->setUrl('http://foo.bar/baz');
        self::assertSame('http://foo.bar/baz', $license->getUrl());
    }

    public function testSetUrlThrowsOnWrongFormat()
    {
        $spdxLicenseValidator = $this->createStub(\CycloneDX\Spdx\License::class);
        $spdxLicenseValidator->method('validate')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->willReturn(null);
        $license = DisjunctiveLicense::createFromNameOrId('foo', $spdxLicenseValidator);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/invalid url/i');

        $license->setUrl('foobar');
    }
}
