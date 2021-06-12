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

    private const LICENSES_FILE_CONTENT = <<<'JSON'
        [
            "FooBaR"
        ]
        JSON;

    protected function setUp(): void
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), __CLASS__);
        file_put_contents($tempFilePath, self::LICENSES_FILE_CONTENT);

        $this->license = $this->createPartialMock(License::class, ['getResourcesFile']);
        $this->license->method('getResourcesFile')->willReturn($tempFilePath);
        $this->license->loadLicenses();

        @unlink($tempFilePath);
    }

    public function testGetLicensesAsExpected(): void
    {
        $expected = json_decode(self::LICENSES_FILE_CONTENT, true, 2, \JSON_THROW_ON_ERROR);
        $licenses = $this->license->getLicenses();
        self::assertIsArray($expected);
        self::assertSame($expected, array_values($licenses));
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
        yield 'UPPERCASE' => ['FOOBAR'];
        yield 'lowercase' => ['foobar'];
        yield 'PascalCase' => ['FooBar'];
    }

    public function testShippedLicensesFile(): void
    {
        $file = (new License())->getResourcesFile();

        self::assertFileExists($file);

        $json = file_get_contents($file);
        self::assertIsString($json);
        self::assertJson($json);

        $licenses = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($licenses);
        self::assertNotEmpty($licenses);

        foreach ($licenses as $license) {
            self::assertIsString($license);
        }
    }

    public function testWithMalformedLicenseFile(): void
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), __METHOD__);
        file_put_contents($tempFilePath, '["foo');
        $license = $this->createPartialMock(License::class, ['getResourcesFile']);
        $license->method('getResourcesFile')->willReturn($tempFilePath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/malformed licenses file/i');

        $license->loadLicenses();
    }

    public function testWithMissingLicenseFile(): void
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), __METHOD__);
        unlink($tempFilePath);

        $license = $this->createPartialMock(License::class, ['getResourcesFile']);
        $license->method('getResourcesFile')->willReturn($tempFilePath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing licenses file/i');

        $license->loadLicenses();
    }
}
