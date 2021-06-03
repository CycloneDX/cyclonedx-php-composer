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
use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Models\Component;
use CycloneDX\Models\License;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Models\Component
 */
class ComponentTest extends TestCase
{
    /** @psalm-var Component */
    private $component;

    protected function setUp(): void
    {
        parent::setUp();

        $this->component = $this->createPartialMock(Component::class, []);
    }

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

    public function testTypeSetterGetter(): void
    {
        $type = Classification::LIBRARY;
        $this->component->setType($type);
        self::assertSame($type, $this->component->getType());
    }

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

    public function testLicensesSetterGetter(): void
    {
        $licenses = [$this->createMock(License::class)];
        $this->component->setLicenses($licenses);
        self::assertSame($licenses, $this->component->getLicenses());
    }

    public function testSetLicensesWithInvalidArgument(): void
    {
        $licenses = ['foo'];
        $this->expectException(\InvalidArgumentException::class);
        $this->component->setLicenses($licenses);
    }

    public function testAddLicense(): void
    {
        $license = $this->createMock(License::class);
        $this->component->addLicense($license);
        self::assertSame([$license], $this->component->getLicenses());
    }

    // endregion licenses setter&getter

    // region hashes setter&getter

    public function testHashesSetterGetter(): void
    {
        $algorithm = HashAlgorithm::MD5;
        $content = bin2hex(random_bytes(32));
        $hashes = [$algorithm => $content];
        $this->component->setHashes($hashes);
        self::assertSame($hashes, $this->component->getHashes());
    }

    /**
     * @dataProvider dpSetHashesWithInvalidArgument
     */
    public function testSetHashesWithInvalidArgument(array $hashes, string $exceptionClass, string $exceptionRegEx): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessageMatches($exceptionRegEx);

        $this->component->setHashes($hashes);
    }

    public function dpSetHashesWithInvalidArgument()
    {
        yield 'unknown algorithm' => [
            [bin2hex(random_bytes(32)) => 'foo'],
            \DomainException::class,
            '/unknown hash algorithm/i',
        ];
        yield 'content is not string' => [
            [HashAlgorithm::SHA_1 => 1234],
            \InvalidArgumentException::class,
            '/content .+ is not string/i',
        ];
    }

    public function testHashSetterGetter(): void
    {
        $algorithm = HashAlgorithm::MD5;
        $content = bin2hex(random_bytes(32));

        $this->component->setHash($algorithm, $content);

        self::assertSame([$algorithm => $content], $this->component->getHashes());
    }

    public function testSetHashWithUnknownAlgorithm(): void
    {
        $algorithm = bin2hex(random_bytes(32));
        $content = bin2hex(random_bytes(32));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/unknown hash algorithm/i');

        $this->component->setHash($algorithm, $content);
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
