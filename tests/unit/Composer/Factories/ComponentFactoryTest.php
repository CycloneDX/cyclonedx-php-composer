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
use Composer\Package\PackageInterface;
use CycloneDX\Composer\Factories\ComponentFactory;
use CycloneDX\Composer\Factories\LicenseFactory;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\ComponentFactory
 *
 * @uses   \CycloneDX\Models\Component
 */
class ComponentFactoryTest extends TestCase
{
    public function testMakeFromPackageThrowsOnEmptyName(): void
    {
        $licenseFactory = $this->createStub(LicenseFactory::class);
        $factory = new ComponentFactory($licenseFactory);
        $package = $this->createConfiguredMock(
            PackageInterface::class,
            [
                'getPrettyName' => '',
            ]
        );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectErrorMessageMatches('/package without name/i');

        $factory->makeFromPackage($package);
    }

    public function testMakeFromPackageThrowsOnEmptyVersion(): void
    {
        $licenseFactory = $this->createStub(LicenseFactory::class);
        $factory = new ComponentFactory($licenseFactory);
        $package = $this->createConfiguredMock(
            PackageInterface::class,
            [
                'getPrettyName' => 'vendor/name',
                'getPrettyVersion' => '',
            ]
        );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectErrorMessageMatches('/package without version/i');

        $factory->makeFromPackage($package);
    }

    /**
     * @dataProvider dpMakeFromPackage
     */
    public function testMakeFromPackage(
        PackageInterface $package,
        Component $expected,
        ?LicenseFactory $licenseFactory = null
    ): void {
        $factory = new ComponentFactory($licenseFactory ?? $this->createStub(LicenseFactory::class));

        $got = $factory->makeFromPackage($package);

        self::assertEquals($expected, $got);
    }

    public function dpMakeFromPackage(): \Generator
    {
        yield 'minimal package' => [
            $this->createConfiguredMock(
                PackageInterface::class,
                [
                    'getPrettyName' => 'some-package',
                    'getPrettyVersion' => 'v1.2.3',
                    'isDev' => false,
                    'getDistSha1Checksum' => '',
                ]
            ),
            (new Component('library', 'some-package', '1.2.3'))
                ->setPackageUrl((new PackageUrl('composer', 'some-package'))->setVersion('1.2.3')),
        ];

        yield 'dev package' => [
            $this->createConfiguredMock(
                PackageInterface::class,
                [
                    'getPrettyName' => 'some-package',
                    'getPrettyVersion' => 'v1.2.3',
                    'isDev' => true,
                    'getDistSha1Checksum' => '',
                ]
            ),
            (new Component('library', 'some-package', 'v1.2.3'))
                ->setPackageUrl((new PackageUrl('composer', 'some-package'))->setVersion('v1.2.3')),
        ];

        $licenses = [$this->createStub(License::class)];
        $package = $this->createConfiguredMock(
            CompletePackageInterface::class,
            [
                'getPrettyName' => 'SomeVendor/some-package',
                'getPrettyVersion' => '1.2.3',
                'isDev' => false,
                'getDescription' => 'some description',
                'getLicense' => ['MIT'],
                'getDistSha1Checksum' => '1234567890',
            ]
        );
        $expected = (new Component('library', 'some-package', '1.2.3'))
            ->setGroup('SomeVendor')
            ->setDescription('some description')
            ->setLicenses($licenses)
            ->setHashes(['SHA-1' => '1234567890'])
            ->setPackageUrl(
                (new PackageUrl('composer', 'some-package'))
                    ->setNamespace('SomeVendor')
                    ->setVersion('1.2.3')
                    ->setChecksums(['sha1:1234567890'])
            );
        $licenseFactory = $this->createStub(LicenseFactory::class);
        $licenseFactory->method('makeFromPackage')->with($package)->willReturn($licenses);
        yield 'complete set' => [$package, $expected, $licenseFactory];
    }
}
