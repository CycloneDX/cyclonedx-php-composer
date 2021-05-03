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

namespace CycloneDX\Tests\unit\Models;

use CycloneDX\Models\License;
use PHPUnit\Framework\TestCase;

/**
 * Class LicenseTest.
 *
 * @covers \CycloneDX\Models\License
 *
 * @uses \CycloneDX\Spdx\License
 */
class LicenseTest extends TestCase
{
    /** @psalm-var License */
    private $license;

    public function setUp(): void
    {
        parent::setUp();

        $this->license = new License(random_bytes(255));
    }

    // region name|id setters&getters

    public function testWithId(): void
    {
        $id = 'MIT';
        $this->license->setNameOrId($id);
        self::assertEquals($id, $this->license->getId());
        self::assertNull($this->license->getName());
    }

    public function testWithName(): void
    {
        $name = 'some non-SPDX license name';
        $this->license->setNameOrId($name);
        self::assertEquals($name, $this->license->getName());
        self::assertNull($this->license->getId());
    }

    // endregion name|id setters&getters

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
