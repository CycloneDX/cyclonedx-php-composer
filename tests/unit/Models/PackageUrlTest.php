<?php

declare(strict_types=1);

/*
 * This file is part of the CycloneDX PHP Composer Plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
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

namespace CycloneDX\Tests\uni\Models;

use CycloneDX\Models\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Models\PackageUrl
 */
class PackageUrlTest extends TestCase
{
    /** @var PackageUrl */
    private $pUrl;

    public function setUp(): void
    {
        parent::setUp();
        $this->pUrl = new PackageUrl(bin2hex(random_bytes(5)), bin2hex(random_bytes(8)));
    }

    // region type setter&getter

    public function testTypeSetterGetter(): void
    {
        $name = bin2hex(random_bytes(32));
        $this->pUrl->setType($name);
        self::assertEquals($name, $this->pUrl->getType());
    }

    public function testTypeSetterInvalid(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/empty/i');
        $this->pUrl->setType('');
    }

    // endregion type setter&getter

    // region name setter&getter

    public function testNameSetterGetter(): void
    {
        $name = bin2hex(random_bytes(32));
        $this->pUrl->setName($name);
        self::assertEquals($name, $this->pUrl->getName());
    }

    public function testNameSetterInvalid(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/empty/i');
        $this->pUrl->setName('');
    }

    // endregion name setter&getter

    // region namespace setter&getter

    /**
     * @dataProvider \CycloneDX\Tests\_data\GeneralDataProvider::stringRandomEmptyNull
     */
    public function testNamespaceSetterGetter(?string $namespace): void
    {
        $this->pUrl->setNamespace($namespace);
        self::assertEquals($namespace, $this->pUrl->getNamespace());
    }

    // endregion namespace setter&getter

    // region version setter&getter

    /**
     * @dataProvider \CycloneDX\Tests\_data\GeneralDataProvider::stringRandomEmptyNull
     */
    public function testVersionSetterGetter(?string $version): void
    {
        $this->pUrl->setVersion($version);
        self::assertEquals($version, $this->pUrl->getVersion());
    }

    // endregion version setter&getter

    // region Qualifiers setter&getter

    /**
     * @dataProvider \CycloneDX\Tests\_data\GeneralDataProvider::stringRandomEmptyNull
     */
    public function testQualifiersSetterGetter(?string $qualifiers): void
    {
        $this->pUrl->setQualifiers($qualifiers);
        self::assertEquals($qualifiers, $this->pUrl->getQualifiers());
    }

    // endregion Qualifiers setter&getter

    // region subpath setter&getter

    /**
     * @dataProvider \CycloneDX\Tests\_data\GeneralDataProvider::stringRandomEmptyNull
     */
    public function testsSubpathSetterGetter(?string $subpath): void
    {
        $this->pUrl->setsubpath($subpath);
        self::assertEquals($subpath, $this->pUrl->getsubpath());
    }

    // endregion subpath setter&getter
}
