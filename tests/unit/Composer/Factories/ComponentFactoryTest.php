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
use CycloneDX\Models\License\DisjunctiveLicense;
use CycloneDX\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Repositories\HashRepository;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Composer\Factories\ComponentFactory
 *
 * @uses   \CycloneDX\Models\Component
 */
class ComponentFactoryTest extends TestCase
{
    public function testLicenseFactoryGetterSetter(): void
    {
        $licenseFactory1 = $this->createStub(LicenseFactory::class);
        $licenseFactory2 = $this->createStub(LicenseFactory::class);

        $factory = new ComponentFactory($licenseFactory1);
        self::assertSame($licenseFactory1, $factory->getLicenseFactory());

        $factory->setLicenseFactory($licenseFactory2);
        self::assertSame($licenseFactory2, $factory->getLicenseFactory());
    }

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
     *
     * @uses \CycloneDX\Enums\Classification::isValidValue
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
                ]
            ),
            (new Component('library', 'some-package', '1.2.3'))
                ->setPackageUrl((new PackageUrl('composer', 'some-package'))->setVersion('1.2.3')),
        ];

        // @TODO add complete test set
    }
}
