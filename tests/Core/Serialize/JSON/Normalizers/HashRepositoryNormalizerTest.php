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

namespace CycloneDX\Tests\Core\Serialize\JSON\Normalizers;

use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashRepositoryNormalizer;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\HashRepositoryNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class HashRepositoryNormalizerTest extends TestCase
{
    public function testConstructor(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashRepositoryNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    public function testNormalize(): void
    {
        $hashNormalizer = $this->createMock(HashNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['makeForHash' => $hashNormalizer]);
        $normalizer = new HashRepositoryNormalizer($factory);
        $repo = $this->createStub(HashRepository::class);
        $repo->method('getHashes')->willReturn(['alg1' => 'content1', 'alg2' => 'content2']);

        $hashNormalizer->expects(self::exactly(2))->method('normalize')
            ->withConsecutive(['alg1', 'content1'], ['alg2', 'content2'])
            ->willReturnOnConsecutiveCalls(['dummy1'], ['dummy2']);

        $normalized = $normalizer->normalize($repo);

        self::assertSame([['dummy1'], ['dummy2']], $normalized);
    }

    /**
     * @depends testConstructor
     */
    public function testNormalizeSkipOnThrow(): void
    {
        $hashNormalizer = $this->createMock(HashNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['makeForHash' => $hashNormalizer]);
        $normalizer = new HashRepositoryNormalizer($factory);

        $repo = $this->createConfiguredMock(HashRepository::class, [
            'getHashes' => ['alg1' => 'cont1', 'alg2' => 'cont2'],
        ]);

        $hashNormalizer->expects(self::exactly(2))
            ->method('normalize')
            ->withConsecutive(['alg1', 'cont1'], ['alg2', 'cont2'])
            ->willThrowException(new DomainException());

        $got = $normalizer->normalize($repo);

        self::assertSame([], $got);
    }
}
