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

namespace CycloneDX\Tests\uni\Serialize;

use CycloneDX\Models\PackageUrl as Model;
use CycloneDX\Serialize\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Serialize\PackageUrl
 *
 * @uses \CycloneDX\Models\PackageUrl
 */
class PackageUrlTest extends TestCase
{
    /** @var PackageUrl */
    private $serializer;

    public function setUp(): void
    {
        parent::setUp();
        $this->serializer = new PackageUrl();
    }

    /**
     * @dataProvider \CycloneDX\Tests\_data\PackageUrlProvider::examples
     */
    public function testSerialize(string $expected, Model $model): void
    {
        $serialized = $this->serializer->serialize($model);
        self::assertEquals($expected, $serialized);
    }

    /**
     * @dataProvider \CycloneDX\Tests\_data\PackageUrlProvider::examples
     */
    public function testDeserialize(string $string, Model $expected): void
    {
        $deserialized = $this->serializer->deserialize($string);
        self::assertEquals($expected, $deserialized);
    }

    public function testDeserializeEmpty(): void
    {
        $deserialized = $this->serializer->deserialize('');
        self::assertNull($deserialized);
    }
}
