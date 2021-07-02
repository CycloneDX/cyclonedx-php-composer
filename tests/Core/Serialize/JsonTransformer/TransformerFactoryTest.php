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

use CycloneDX\Core\Serialize\JsonTransformer\BomTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\ComponentRepositoryTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\ComponentTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseRepositoryTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\HashRepositoryTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\HashTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\LicenseExpressionTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\TransformerFactory;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\TransformerFactory
 *
 * @uses   \CycloneDX\Core\Serialize\JsonTransformer\AbstractTransformer
 */
class TransformerFactoryTest extends TestCase
{
    public function testConstructor(): TransformerFactory
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'isSupportedFormat' => true,
                'getSupportedFormats' => ['JSON'],
            ]
        );

        $factory = new TransformerFactory($spec);
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

        new TransformerFactory($spec);
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpec(TransformerFactory $factory): void
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
    public function testSetSpecThrowsWhenUnsupported(TransformerFactory $factory): void
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
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\ComponentRepositoryTransformer
     */
    public function testMakeForComponentRepository(TransformerFactory $factory): void
    {
        $got = $factory->makeForComponentRepository();
        self::assertInstanceOf(ComponentRepositoryTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\BomTransformer
     */
    public function testMakeForBom(TransformerFactory $factory): void
    {
        $got = $factory->makeForBom();
        self::assertInstanceOf(BomTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseTransformer
     */
    public function testMakeForDisjunctiveLicense(TransformerFactory $factory): void
    {
        $got = $factory->makeForDisjunctiveLicense();
        self::assertInstanceOf(DisjunctiveLicenseTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\HashRepositoryTransformer
     */
    public function testMakeForHashRepository(TransformerFactory $factory): void
    {
        $got = $factory->makeForHashRepository();
        self::assertInstanceOf(HashRepositoryTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\ComponentTransformer
     */
    public function testMakeForComponent(TransformerFactory $factory): void
    {
        $got = $factory->makeForComponent();
        self::assertInstanceOf(ComponentTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseRepositoryTransformer
     */
    public function testMakeForDisjunctiveLicenseRepository(TransformerFactory $factory): void
    {
        $got = $factory->makeForDisjunctiveLicenseRepository();
        self::assertInstanceOf(DisjunctiveLicenseRepositoryTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\LicenseExpressionTransformer
     */
    public function testMakeForLicenseExpression(TransformerFactory $factory): void
    {
        $got = $factory->makeForLicenseExpression();
        self::assertInstanceOf(LicenseExpressionTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Serialize\JsonTransformer\HashTransformer
     */
    public function testMakeForHash(TransformerFactory $factory): void
    {
        $got = $factory->makeForHash();
        self::assertInstanceOf(HashTransformer::class, $got);
        self::assertSame($factory, $got->getTransformerFactory());
    }
}
