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

namespace CycloneDX\Tests\unit\Models;

use CycloneDX\Models\License;
use CycloneDX\Spdx\License as SpdxLicenseValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class LicenseTest.
 *
 * @covers \CycloneDX\Models\License
 */
class LicenseTest extends TestCase
{
    /** @var License */
    private $license;

    protected function setUp(): void
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $spdxLicenseValidator->method('validate')->willReturn(true);
        $spdxLicenseValidator->method('getLicense')->willReturnArgument(0);

        $this->license = License::createFromNameOrId(uniqid('DUMMY'), $spdxLicenseValidator);
    }

    // region name|id createFromNameOrId & getters

    public function testCreateFromNameOrIdWithId(): void
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $spdxLicenseValidator->method('validate')->with('someID')->willReturn(true);
        $spdxLicenseValidator->method('getLicense')->with('someID')->willReturn('realID');

        $license = License::createFromNameOrId('someID', $spdxLicenseValidator);

        self::assertEquals('realID', $license->getId());
        self::assertNull($license->getName());
    }

    public function testCreateFromNameOrIdWithName(): void
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $spdxLicenseValidator->method('validate')->with('someName')->willReturn(false);
        $spdxLicenseValidator->method('getLicense')->with('someName')->willReturn(null);

        $license = License::createFromNameOrId('someName', $spdxLicenseValidator);

        self::assertEquals('someName', $license->getName());
        self::assertNull($license->getId());
    }

    // endregion name|id createFromNameOrId & getters

    // region url setter&getter

    public function testWithUrlValid(): void
    {
        $url = 'http://example.com/'.bin2hex(random_bytes(32));

        $this->license->setUrl($url);

        self::assertEquals($url, $this->license->getUrl());
    }

    public function testWithUrlInvalid(): void
    {
        $url = 'example.com/'.bin2hex(random_bytes(32));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/invalid url/i');

        $this->license->setUrl($url);
    }

    // endregion url setter&getter
}
