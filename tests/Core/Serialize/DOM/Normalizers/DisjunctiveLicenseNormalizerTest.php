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

namespace CycloneDX\Tests\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Models\License\AbstractDisjunctiveLicense;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers\DisjunctiveLicenseNormalizer;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\DisjunctiveLicenseNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 * @covers \CycloneDX\Core\Helpers\SimpleDomTrait
 */
class DisjunctiveLicenseNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testConstructor(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new DisjunctiveLicenseNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @dataProvider dpNormalize
     */
    public function testNormalize(AbstractDisjunctiveLicense $license, string $expected): void
    {
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getDocument' => new DOMDocument()]);
        $normalizer = new DisjunctiveLicenseNormalizer($factory);
        self::assertStringEqualsDomNode($expected, $normalizer->normalize($license));
    }

    public function dpNormalize(): Generator
    {
        yield 'prefer id' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithId::class, [
                'getId' => 'foo',
            ]),
            '<license><id>foo</id></license>',
        ];

        yield 'name' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithName::class, [
                'getName' => 'bar',
            ]),
            '<license><name>bar</name></license>',
        ];

        yield 'optional url' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithName::class, [
                'getName' => 'foo',
                'getUrl' => 'http://foo.bar',
                ]),
            '<license><name>foo</name><url>http://foo.bar</url></license>',
        ];
    }

    public function testNormalizeThrowsOnUnknown(): void
    {
        $license = $this->createStub(AbstractDisjunctiveLicense::class);
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new DisjunctiveLicenseNormalizer($factory);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/unsupported license class/i');

        $normalizer->normalize($license);
    }
}
