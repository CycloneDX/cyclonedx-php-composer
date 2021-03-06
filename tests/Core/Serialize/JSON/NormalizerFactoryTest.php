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

namespace CycloneDX\Tests\Core\Serialize\JSON;

use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\BomNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\LicenseExpressionNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\NormalizerFactory
 *
 * @uses   \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class NormalizerFactoryTest extends TestCase
{
    public function testConstructor(): NormalizerFactory
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'isSupportedFormat' => true,
                'getSupportedFormats' => ['JSON'],
            ]
        );

        $factory = new NormalizerFactory($spec);
        self::assertSame($spec, $factory->getSpec());

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
                'getSupportedFormats' => ['JSON'],
            ]
        );

        $got = $factory->setSpec($spec);

        self::assertSame($spec, $factory->getSpec());
        self::assertSame($got, $factory);
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
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\ComponentRepositoryNormalizer
     */
    public function testMakeForComponentRepository(NormalizerFactory $factory): void
    {
        $got = $factory->makeForComponentRepository();
        self::assertInstanceOf(ComponentRepositoryNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\BomNormalizer
     */
    public function testMakeForBom(NormalizerFactory $factory): void
    {
        $got = $factory->makeForBom();
        self::assertInstanceOf(BomNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseNormalizer
     */
    public function testMakeForDisjunctiveLicense(NormalizerFactory $factory): void
    {
        $got = $factory->makeForDisjunctiveLicense();
        self::assertInstanceOf(DisjunctiveLicenseNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\HashRepositoryNormalizer
     */
    public function testMakeForHashRepository(NormalizerFactory $factory): void
    {
        $got = $factory->makeForHashRepository();
        self::assertInstanceOf(HashRepositoryNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer
     */
    public function testMakeForComponent(NormalizerFactory $factory): void
    {
        $got = $factory->makeForComponent();
        self::assertInstanceOf(ComponentNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseRepositoryNormalizer
     */
    public function testMakeForDisjunctiveLicenseRepository(NormalizerFactory $factory): void
    {
        $got = $factory->makeForDisjunctiveLicenseRepository();
        self::assertInstanceOf(DisjunctiveLicenseRepositoryNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\LicenseExpressionNormalizer
     */
    public function testMakeForLicenseExpression(NormalizerFactory $factory): void
    {
        $got = $factory->makeForLicenseExpression();
        self::assertInstanceOf(LicenseExpressionNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\HashNormalizer
     */
    public function testMakeForHash(NormalizerFactory $factory): void
    {
        $got = $factory->makeForHash();
        self::assertInstanceOf(HashNormalizer::class, $got);
        self::assertSame($factory, $got->getNormalizerFactory());
    }
}
