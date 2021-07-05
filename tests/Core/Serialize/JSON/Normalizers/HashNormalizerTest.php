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

use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\HashNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class HashNormalizerTest extends TestCase
{
    public function testConstructor(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    public function testNormalize(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
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

        $normalized = $normalizer->normalize('foo', 'bar');

        self::assertSame(['alg' => 'foo', 'content' => 'bar'], $normalized);
    }

    public function testNormalizeThrowOnUnsupportedAlgorithm(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
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

        $normalizer->normalize('foo', 'bar');
    }

    public function testNormalizeThrowOnUnsupportedContent(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
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

        $normalizer->normalize('foo', 'bar');
    }
}
