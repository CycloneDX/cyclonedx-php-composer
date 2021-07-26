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

namespace CycloneDX\Tests\Core\Repositories;

use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Repositories\HashRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Repositories\HashRepository
 *
 * @uses \CycloneDX\Core\Enums\HashAlgorithm::isValidValue()
 */
class HashRepositoryTest extends TestCase
{
    public function testNonEmptyConstructor(): void
    {
        $hashes = new HashRepository([HashAlgorithm::MD5 => 'foobar']);

        self::assertCount(1, $hashes);
        self::assertArrayHasKey(HashAlgorithm::MD5, $hashes->getHashes());
        self::assertSame('foobar', $hashes->getHashes()[HashAlgorithm::MD5]);
        self::assertSame('foobar', $hashes->getHash(HashAlgorithm::MD5));
    }

    public function testAddHash(): void
    {
        $hashes = new HashRepository([HashAlgorithm::SHA_1 => 'foo']);

        $hashes->setHash(HashAlgorithm::MD5, 'bar');

        self::assertCount(2, $hashes);
        self::assertArrayHasKey(HashAlgorithm::MD5, $hashes->getHashes());
        self::assertSame('bar', $hashes->getHashes()[HashAlgorithm::MD5]);
        self::assertSame('bar', $hashes->getHash(HashAlgorithm::MD5));
    }

    public function testUpdateHash(): void
    {
        $hashes = new HashRepository([HashAlgorithm::MD5 => 'foo', HashAlgorithm::SHA_1 => 'foo']);

        $hashes->setHash(HashAlgorithm::MD5, 'bar');

        self::assertCount(2, $hashes);
        self::assertArrayHasKey(HashAlgorithm::MD5, $hashes->getHashes());
        self::assertSame('bar', $hashes->getHashes()[HashAlgorithm::MD5]);
        self::assertSame('bar', $hashes->getHash(HashAlgorithm::MD5));
    }

    public function testUnsetHash(): void
    {
        $hashes = new HashRepository([HashAlgorithm::MD5 => 'foo', HashAlgorithm::SHA_1 => 'foo']);
        $hashes->setHash(HashAlgorithm::MD5, null);

        self::assertNull($hashes->getHash(HashAlgorithm::MD5));
        self::assertCount(1, $hashes);
        self::assertSame([HashAlgorithm::SHA_1 => 'foo'], $hashes->getHashes());
    }

    public function testGetUnknownHash(): void
    {
        $hashes = new HashRepository();
        self::assertNull($hashes->getHash(HashAlgorithm::MD5));
    }

    public function testSetUnknownHashAlgorithmThrows(): void
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
