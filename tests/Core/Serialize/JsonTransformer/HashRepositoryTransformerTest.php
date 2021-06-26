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

namespace CycloneDX\Tests\Core\Serialize\JsonTransformer;

use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\JsonTransformer\Factory;
use CycloneDX\Core\Serialize\JsonTransformer\HashRepositoryTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\HashTransformer;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\HashRepositoryTransformer
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\AbstractTransformer
 */
class HashRepositoryTransformerTest extends TestCase
{
    public function testConstructor(): HashRepositoryTransformer
    {
        $factory = $this->createMock(Factory::class);

        $transformer = new HashRepositoryTransformer($factory);

        self::assertSame($factory, $transformer->getFactory());

        return $transformer;
    }

    public function testTransform(): void
    {
        $hashTransformer = $this->createMock(HashTransformer::class);
        $factory = $this->createConfiguredMock(Factory::class, ['makeForHash' => $hashTransformer]);
        $transformer = new HashRepositoryTransformer($factory);
        $repo = $this->createStub(HashRepository::class);
        $repo->method('getHashes')->willReturn(['alg1' => 'content1', 'alg2' => 'content2']);

        $hashTransformer->expects(self::exactly(2))->method('transform')
            ->withConsecutive(['alg1', 'content1'], ['alg2', 'content2'])
            ->willReturnOnConsecutiveCalls(['dummy1'], ['dummy2']);

        $transformed = $transformer->transform($repo);

        self::assertSame([['dummy1'], ['dummy2']], $transformed);
    }

    /**
     * @depends testConstructor
     */
    public function testTransformEmptyToNull(HashRepositoryTransformer $transformer): void
    {
        $repo = $this->createStub(HashRepository::class);
        $repo->method('getHashes')->willReturn([]);

        $transformed = $transformer->transform($repo);

        self::assertNull($transformed);
    }

    /**
     * @depends testConstructor
     */
    public function testTransformThrowOnThrow(): void
    {
        $hashTransformer = $this->createMock(HashTransformer::class);
        $factory = $this->createConfiguredMock(Factory::class, ['makeForHash' => $hashTransformer]);
        $transformer = new HashRepositoryTransformer($factory);

        $repo = $this->createConfiguredMock(HashRepository::class, [
            'getHashes' => ['alg1' => 'cont1', 'alg2', 'cont2'],
        ]);

        $exception = new DomainException();
        $hashTransformer->expects(self::once())->method('transform')
            ->with('alg1', 'cont1')
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $transformer->transform($repo);
    }
}
