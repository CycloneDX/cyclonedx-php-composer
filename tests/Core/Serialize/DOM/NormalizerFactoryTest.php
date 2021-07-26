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

namespace CycloneDX\Tests\Core\Serialize\DOM;

use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\NormalizerFactory
 *
 * @uses   \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 */
class NormalizerFactoryTest extends TestCase
{
    public function testConstructor(): NormalizerFactory
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'isSupportedFormat' => true,
                'getSupportedFormats' => ['DOM'],
            ]
        );

        $factory = new NormalizerFactory($spec);
        self::assertSame($spec, $factory->getSpec());
        self::assertInstanceOf(\DOMDocument::class, $factory->getDocument());

        return $factory;
    }

    public function testConstructThrowsWhenUnsupported(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'isSupportedFormat' => false,
                'getSupportedFormats' => [],
            ]
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported format/i');

        new NormalizerFactory($spec);
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpec(NormalizerFactory $factory): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'isSupportedFormat' => true,
                'getSupportedFormats' => ['DOM'],
            ]
        );

        $actual = $factory->setSpec($spec);

        self::assertSame($spec, $factory->getSpec());
        self::assertSame($factory, $actual);
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpecThrowsWhenUnsupported(NormalizerFactory $factory): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'isSupportedFormat' => false,
                'getSupportedFormats' => [],
            ]
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported format/i');

        $factory->setSpec($spec);
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\ComponentRepositoryNormalizer
     */
    public function testMakeForComponentRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForComponentRepository();
        self::assertInstanceOf(Normalizers\ComponentRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\BomNormalizer
     */
    public function testMakeForBom(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForBom();
        self::assertInstanceOf(Normalizers\BomNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\DisjunctiveLicenseNormalizer
     */
    public function testMakeForDisjunctiveLicense(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForDisjunctiveLicense();
        self::assertInstanceOf(Normalizers\DisjunctiveLicenseNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\HashRepositoryNormalizer
     */
    public function testMakeForHashRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForHashRepository();
        self::assertInstanceOf(Normalizers\HashRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\ComponentNormalizer
     */
    public function testMakeForComponent(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForComponent();
        self::assertInstanceOf(Normalizers\ComponentNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\DisjunctiveLicenseRepositoryNormalizer
     */
    public function testMakeForDisjunctiveLicenseRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForDisjunctiveLicenseRepository();
        self::assertInstanceOf(Normalizers\DisjunctiveLicenseRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\LicenseExpressionNormalizer
     */
    public function testMakeForLicenseExpression(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForLicenseExpression();
        self::assertInstanceOf(Normalizers\LicenseExpressionNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\HashNormalizer
     */
    public function testMakeForHash(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForHash();
        self::assertInstanceOf(Normalizers\HashNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\MetaDataNormalizer
     */
    public function testMakeForMetaData(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForMetaData();
        self::assertInstanceOf(Normalizers\MetaDataNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\ToolRepositoryNormalizer
     */
    public function testMakeForToolRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForToolRepository();
        self::assertInstanceOf(Normalizers\ToolRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\ToolNormalizer
     */
    public function testMakeForTool(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForTool();
        self::assertInstanceOf(Normalizers\ToolNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\ToolNormalizer
     */
    public function testMakeForDependencies(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForDependencies();
        self::assertInstanceOf(Normalizers\DependenciesNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }
}
