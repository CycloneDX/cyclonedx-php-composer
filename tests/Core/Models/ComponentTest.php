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

namespace CycloneDX\Tests\Core\Models;

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\BomRefRepository;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\HashRepository;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Core\Models\Component
 *
 * @uses   \CycloneDX\Core\Enums\Classification::isValidValue
 * @uses   \CycloneDX\Core\Models\BomRef::__construct
 */
class ComponentTest extends TestCase
{
    private $component;

    protected function setUp(): void
    {
        $this->component = new Component(
            'library',
            uniqid('myName', false),
            uniqid('myVersion', true)
        );
    }

    /**
     * @uses \CycloneDX\Core\Enums\Classification::isValidValue
     * @uses \CycloneDX\Core\Models\BomRef
     */
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

    public function testGetBomRefConstant(): void
    {
        $bomRef = $this->component->getBomRef();
        self::assertInstanceOf(BomRef::class, $bomRef);
        self::assertSame($bomRef, $this->component->getBomRef());
    }

    /**
     * @uses \CycloneDX\Core\Models\BomRef::getValue
     * @uses \CycloneDX\Core\Models\BomRef::setValue
     */
    public function testsetBomRefValue(): void
    {
        $bomRef = $this->component->getBomRef();
        self::assertNull($bomRef->getValue());

        $this->component->setBomRefValue('foo');

        self::assertSame('foo', $bomRef->getValue());
    }

    // region type getter&setter

    /**
     * @uses \CycloneDX\Core\Enums\Classification::isValidValue()
     */
    public function testTypeSetterGetter(): void
    {
        $type = Classification::LIBRARY;
        $this->component->setType($type);
        self::assertSame($type, $this->component->getType());
    }

    /**
     * @uses \CycloneDX\Core\Enums\Classification::isValidValue()
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

    /**
     * @dataProvider dpDescriptionSetterGetter
     */
    public function testDescriptionSetterGetter(?string $description, ?string $expected): void
    {
        $setOn = $this->component->setDescription($description);

        self::assertSame($this->component, $setOn);
        self::assertSame($expected, $this->component->getDescription());
    }

    public function dpDescriptionSetterGetter(): \Generator
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$group, $group];
    }

    // endregion description setter&getter

    // region group setter&getter

    /**
     * @dataProvider dpGroupSetterGetter
     */
    public function testGroupSetterGetter(?string $group, ?string $expected): void
    {
        $setOn = $this->component->setGroup($group);

        self::assertSame($this->component, $setOn);
        self::assertSame($expected, $this->component->getGroup());
    }

    public function dpGroupSetterGetter(): \Generator
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$group, $group];
    }

    // endregion group setter&getter

    // region dependenciesBomRefRepository setter&getter

    public function testDependenciesBomRefRepositorySetterGetter(): void
    {
        $repo = $this->createMock(BomRefRepository::class);
        self::assertNull($this->component->getDependenciesBomRefRepository());

        $this->component->setDependenciesBomRefRepository($repo);

        self::assertSame($repo, $this->component->getDependenciesBomRefRepository());
    }

    // endregion dependenciesBomRefRepository setter&getter

    // region clone

    public function testCloneHasOwnBom(): void
    {
        $actual = clone $this->component;

        self::assertEquals($this->component, $actual);
        self::assertNotSame($this->component->getBomRef(), $actual->getBomRef());
    }

    // endregion clone
}
