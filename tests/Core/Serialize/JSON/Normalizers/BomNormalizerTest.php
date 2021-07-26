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

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\BomNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\DependenciesNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\MetaDataNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\BomNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class BomNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
            ]
        );

        $got = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
            $got
        );
    }

    public function testNormalizeComponents(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $componentsNormalizer = $this->createMock(ComponentRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForComponentRepository' => $componentsNormalizer,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getComponentRepository' => $this->createStub(ComponentRepository::class),
            ]
        );

        $componentsNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getComponentRepository())
            ->willReturn(['FakeComponents']);

        $got = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => ['FakeComponents'],
            ],
            $got
        );
    }

    public function testNormalizeMetaData(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsMetaData' => true,
            ]
        );
        $metadataNormalizer = $this->createMock(MetaDataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $metadataNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getMetaData())
            ->willReturn(['FakeMetaData']);

        $got = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'metadata' => ['FakeMetaData'],
                'components' => [],
            ],
            $got
        );
    }

    public function testNormalizeMetaDataEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsMetaData' => true,
            ]
        );
        $metadataNormalizer = $this->createMock(MetaDataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetaData())
            ->willReturn([/* empty */]);

        $got = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
            $got
        );
    }

    public function testNormalizeMetaDataSkipped(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsMetaData' => false,
            ]
        );
        $metadataNormalizer = $this->createMock(MetaDataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetaData())
            ->willReturn(['FakeMetaData']);

        $got = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
            $got
        );
    }

    public function testNormalizeDependencies(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(DependenciesNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn(['FakeDependencies' => 'dummy']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
                'dependencies' => ['FakeDependencies' => 'dummy'],
            ],
            $actual
        );
    }

    public function testNormalizeDependenciesOmitWhenEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(DependenciesNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([]);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
                // 'dependencies' is unset,
            ],
            $actual
        );
    }
}
