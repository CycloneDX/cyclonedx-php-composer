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

namespace CycloneDX\Tests\Core\Models\License;

use CycloneDX\Core\Models\License\AbstractDisjunctiveLicense;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Models\License\AbstractDisjunctiveLicense
 */
abstract class AbstractDisjunctiveLicenseTestCase extends TestCase
{
    public function testSetAndGetUrl(): AbstractDisjunctiveLicense
    {
        $license = $this->createPartialMock(AbstractDisjunctiveLicense::class, []);

        $license->setUrl('http://example.com');
        self::assertSame('http://example.com', $license->getUrl());

        return $license;
    }

    /**
     * @depends testSetAndGetUrl
     */
    public function testSetUrlThrows(AbstractDisjunctiveLicense $license): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/invalid URL/i');
        $license->setUrl('foo');
    }

    /**
     * @depends testSetAndGetUrl
     */
    public function testSetUrlNull(AbstractDisjunctiveLicense $license): void
    {
        $license->setUrl(null);
        self::assertNull($license->getUrl());
    }
}
