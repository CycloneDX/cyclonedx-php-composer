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

use CycloneDX\Enums\Classification;
use CycloneDX\Models\Component;
use CycloneDX\Models\License\LicenseExpression;
use CycloneDX\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Repositories\HashRepository;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Models\Component
 */
class ComponentTest extends TestCase
{
    private $component;

    protected function setUp(): void
    {
        parent::setUp();

        $this->component = $this->createPartialMock(Component::class, []);
    }

    /** @uses \CycloneDX\Enums\Classification::isValidValue */
    public function testConstructor(): void
    {
        $type = Classification::LIBRARY;
        $name = bin2hex(random_bytes(random_int(23, 255)));
        $version = uniqid('v', true);

        $component = new Component($type, $name, $version);

        self::assertSame($type, $component->getType());
        self::assertSame($name, $component->getName());
        self::assertSame($version, $component->getVersion());
    }

    // region type getter&setter

    /**
     * @uses \CycloneDX\Enums\Classification::isValidValue()
     */
    public function testTypeSetterGetter(): void
    {
        $type = Classification::LIBRARY;
        $this->component->setType($type);
        self::assertSame($type, $this->component->getType());
    }

    /**
     * @uses \CycloneDX\Enums\Classification::isValidValue()
     */
    public function testSetTypeWithUnknownValue(): void
    {
        $this->expectException(\DomainException::class);
        $this->component->setType('something unknown');
    }

    // endregion type getter&setter

    // region version setter&getter

    public function testVersionSetterGetter(): void
    {
        $version = uniqid('v', true);
        $this->component->setVersion($version);
        self::assertSame($version, $this->component->getVersion());
    }

    // endregion version setter&getter

    // region licenses setter&getter

    /**
     * @dataProvider dpLicensesSetterGetter
     */
    public function testLicensesSetterGetter($license): void
    {
        $this->component->setLicense($license);
        self::assertSame($license, $this->component->getLicense());
    }

    public function dpLicensesSetterGetter(): \Generator
    {
        yield 'null' => [null];
        yield 'repo' => [$this->createStub(DisjunctiveLicenseRepository::class)];
        yield 'expression' => [$this->createStub(LicenseExpression::class)];
    }

    public function testLicensesSetterGetterThrowsOnInvalidArgument(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/invalid license type/i');

        $this->component->setLicense(new \stdClass());
    }

    // endregion licenses setter&getter

    // region hashes setter&getter

    /**
     * @dataProvider dpHashesSetterGetter
     */
    public function testHashesSetterGetter($hashes): void
    {
        $this->component->setHashRepository($hashes);
        self::assertSame($hashes, $this->component->getHashRepository());
    }

    public function dpHashesSetterGetter(): \Generator
    {
        yield 'null' => [null];
        yield 'repo' => [$this->createStub(HashRepository::class)];
    }

    // endregion hashes setter&getter

    // region packageUrl setter&getter

    public function testPackageUrlSetterGetter(): void
    {
        $url = $this->createMock(PackageUrl::class);
        $this->component->setPackageUrl($url);
        self::assertSame($url, $this->component->getPackageUrl());
    }

    // endregion packageUrl setter&getter

    // region description setter&getter

    public function testDescriptionSetterGetter(): void
    {
        $description = bin2hex(random_bytes(32));
        $this->component->setDescription($description);
        self::assertSame($description, $this->component->getDescription());
    }

    // endregion description setter&getter

    // region group setter&getter

    public function testGroupSetterGetter(): void
    {
        $group = bin2hex(random_bytes(32));
        $this->component->setGroup($group);
        self::assertSame($group, $this->component->getGroup());
    }

    // endregion group setter&getter
}
