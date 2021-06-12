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

use Composer\Package\CompletePackageInterface;
use CycloneDX\Composer\Factories\LicenseFactory;
use CycloneDX\Models\License;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\LicenseFactory
 *
 * @uses \CycloneDX\Models\License
 * @uses \CycloneDX\Spdx\License
 */
class LicenseFactoryTest extends TestCase
{
    public function testMakeFromString(): void
    {
        $randomString = bin2hex(random_bytes(32));
        $expected = new License($randomString);
        $factory = new LicenseFactory();

        $got = $factory->makeFromString($randomString);

        self::assertEquals($expected, $got);
    }

    public function testMakeFromPackage(): void
    {
        $license1 = $this->createStub(License::class);
        $license2 = $this->createStub(License::class);
        $license3 = $this->createStub(License::class);
        $license4 = $this->createStub(License::class);
        $license5 = $this->createStub(License::class);
        $expected = [$license1, $license2, $license3, $license4, $license5];
        $licenses = ['license1', '(license2 or license3)', ['license4', 'license5']];
        $package = $this->createConfiguredMock(CompletePackageInterface::class, ['getLicense' => $licenses]);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeFromString']);

        $factory->expects(self::exactly(\count($expected)))->method('makeFromString')
            ->withConsecutive(['license1'], ['license2'], ['license3'], ['license4'], ['license5'])
            ->willReturnMap([
                ['license1', $license1],
                ['license2', $license2],
                ['license3', $license3],
                ['license4', $license4],
                ['license5', $license5],
            ]);

        $got = $factory->makeFromPackage($package);

        self::assertSame($expected, $got);
    }
}
