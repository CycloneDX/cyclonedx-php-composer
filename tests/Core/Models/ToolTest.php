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

use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\HashRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Models\Tool
 */
class ToolTest extends TestCase
{
    public function testConstruct(): Tool
    {
        $tool = new Tool();

        self::assertNull($tool->getVendor());
        self::assertNull($tool->getName());
        self::assertNull($tool->getVersion());
        self::assertNull($tool->getHashRepository());

        return $tool;
    }

    /**
     * @depends testConstruct
     */
    public function testSetterGetterVersion(Tool $tool): void
    {
        $version = 'v1.2.3';
        $tool->setVersion($version);
        self::assertSame($version, $tool->getVersion());
    }

    /**
     * @depends testConstruct
     */
    public function testSetterGetterVendor(Tool $tool): void
    {
        $vendor = 'myVendor';
        $tool->setVendor($vendor);
        self::assertSame($vendor, $tool->getVendor());
    }

    /**
     * @depends testConstruct
     */
    public function testSetterGetterName(Tool $tool): void
    {
        $name = 'myName';
        $tool->setName($name);
        self::assertSame($name, $tool->getName());
    }

    /**
     * @depends testConstruct
     */
    public function testSetterGetterHashRepository(Tool $tool): void
    {
        $hashes = $this->createStub(HashRepository::class);
        $tool->setHashRepository($hashes);
        self::assertSame($hashes, $tool->getHashRepository());
    }
}
