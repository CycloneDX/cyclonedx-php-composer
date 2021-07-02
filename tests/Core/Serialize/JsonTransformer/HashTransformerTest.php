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

use CycloneDX\Core\Serialize\JsonTransformer\HashTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\TransformerFactory;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\HashTransformer
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\AbstractTransformer
 */
class HashTransformerTest extends TestCase
{
    public function testConstructor(): void
    {
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new HashTransformer($factory);
        self::assertSame($factory, $transformer->getTransformerFactory());
    }

    public function testTransform(): void
    {
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new HashTransformer($factory);
        $factory->method('getSpec')->willReturn(
            $this->createConfiguredMock(
                SpecInterface::class,
                [
                    'isSupportedHashAlgorithm' => true,
                    'getSupportedHashAlgorithms' => ['foo'],
                    'isSupportedHashContent' => true,
                ]
            )
        );

        $transformed = $transformer->transform('foo', 'bar');

        self::assertSame(['alg' => 'foo', 'content' => 'bar'], $transformed);
    }

    public function testTransformThrowOnUnsupportedAlgorithm(): void
    {
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new HashTransformer($factory);
        $factory->method('getSpec')->willReturn(
            $this->createConfiguredMock(
                SpecInterface::class,
                [
                    'isSupportedHashAlgorithm' => false,
                    'getSupportedHashAlgorithms' => [],
                    'isSupportedHashContent' => true,
                ]
            )
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid hash algorithm/i');

        $transformer->transform('foo', 'bar');
    }

    public function testTransformThrowOnUnsupportedContent(): void
    {
        $factory = $this->createMock(TransformerFactory::class);
        $transformer = new HashTransformer($factory);
        $factory->method('getSpec')->willReturn(
            $this->createConfiguredMock(
                SpecInterface::class,
                [
                    'isSupportedHashAlgorithm' => true,
                    'getSupportedHashAlgorithms' => ['foo'],
                    'isSupportedHashContent' => false,
                ]
            )
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid hash content/i');

        $transformer->transform('foo', 'bar');
    }
}
