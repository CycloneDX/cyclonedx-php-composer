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

namespace CycloneDX\Tests\unit\Spdx;

use CycloneDX\Spdx\License;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \CycloneDX\Spdx\License
 */
class LicenseTest extends TestCase
{
    /**
     * @psalm-var License&\PHPUnit\Framework\MockObject\MockObject
     */
    private $license;

    private const LICENSES_FILE = __DIR__.'/../../_data/spdx-licenses.json';

    public function setUp(): void
    {
        $this->license = $this->createPartialMock(License::class, [
            'getResourcesFile', /* @see License::getResourcesFile() */
        ]);
        $this->license->method('getResourcesFile')
            ->willReturn(self::LICENSES_FILE);
        $this->license->loadLicenses();
    }

    public function testGetLicensesNotEmpty(): void
    {
        $licenses = $this->license->getLicenses();
        self::assertNotEmpty($licenses);
    }

    /**
     * @dataProvider validLicense
     */
    public function testValidate(string $identifier): void
    {
        $valid = $this->license->validate($identifier);
        self::assertTrue($valid);
    }

    public function testValidateWithUnknown(): void
    {
        $identifier = uniqid('unknown', false);
        $valid = $this->license->validate($identifier);
        self::assertFalse($valid);
    }

    /**
     * @dataProvider validLicense
     */
    public function testGetLicense(string $identifier): void
    {
        $license = $this->license->getLicense($identifier);
        self::assertNotNull($license);
    }

    public function testGetLicenseWithUnknown(): void
    {
        $identifier = uniqid('unknown', false);
        $license = $this->license->getLicense($identifier);
        self::assertNull($license);
    }

    public static function validLicense(): Generator
    {
        $licenses = ['MIT', 'mit', 'Mit'];
        foreach ($licenses as $license) {
            yield $license => [$license];
        }
    }
}
