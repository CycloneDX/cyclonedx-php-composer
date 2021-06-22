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

namespace CycloneDX\Tests\unit\Repositories;

use CycloneDX\Enums\HashAlgorithm;
use CycloneDX\Repositories\HashRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Repositories\HashRepository
 *
 * @uses \CycloneDX\Enums\HashAlgorithm::isValidValue()
 */
class HashRepositoryTest extends TestCase
{
    public function testConstructorAndGetHash(): void
    {
        $hashes = new HashRepository([HashAlgorithm::MD5 => 'foobar']);
        self::assertSame([HashAlgorithm::MD5 => 'foobar'], $hashes->getHashes());
    }

    public function testCount(): void
    {
        $hashes = new HashRepository([HashAlgorithm::MD5 => 'foobar', HashAlgorithm::SHA_1 => 'barfoo']);
        self::assertSame(2, $hashes->count());
    }

    public function testSetAndGetHash(): void
    {
        $hashes = new HashRepository();
        $hashes->setHash(HashAlgorithm::MD5, 'foobar');
        self::assertSame('foobar', $hashes->getHash(HashAlgorithm::MD5));
    }

    public function testUnsetHash(): void
    {
        $hashes = new HashRepository();
        $hashes->setHash(HashAlgorithm::MD5, 'foobar');
        self::assertSame('foobar', $hashes->getHash(HashAlgorithm::MD5));

        $hashes->setHash(HashAlgorithm::MD5, null);
        self::assertNull($hashes->getHash(HashAlgorithm::MD5));
    }

    public function testGetUnknownHash(): void
    {
        $hashes = new HashRepository();
        self::assertNull($hashes->getHash(HashAlgorithm::MD5));
    }

    public function testSetHashThrows(): void
    {
        $hashes = new HashRepository();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/unknown hash algorithm/i');

        $hashes->setHash('unknownAlgorithm', 'foobar');
    }

    public function testSetGetHashes(): void
    {
        $hashes = new HashRepository([HashAlgorithm::SHA_256 => 'barbar']);

        $hashes->setHashes([HashAlgorithm::MD5 => 'foobar', 'unknownAlgorithm' => 'foobar']);
        $got = $hashes->getHashes();

        self::assertCount(2, $got);
        self::assertSame('barbar', $got[HashAlgorithm::SHA_256]);
        self::assertSame('foobar', $got[HashAlgorithm::MD5]);
    }
}
