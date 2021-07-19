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

use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\ToolRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\MetaDataNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\ToolRepositoryNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\MetaDataNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class MetaDataNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $metaData = $this->createMock(MetaData::class);
        $spec = $this->createMock(SpecInterface::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new MetaDataNormalizer($factory);

        $actual = $normalizer->normalize($metaData);

        self::assertSame([], $actual);
    }

    public function testNormalizeTools(): void
    {
        $metaData = $this->createConfiguredMock(
            MetaData::class,
            [
                'getTools' => $this->createConfiguredMock(ToolRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $toolsRepoFactory = $this->createMock(ToolRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForToolRepository' => $toolsRepoFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $toolsRepoFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getTools())
            ->willReturn(['FakeTool' => 'dummy']);

        $actual = $normalizer->normalize($metaData);

        self::assertSame(
            ['tools' => ['FakeTool' => 'dummy']],
            $actual
        );
    }

    public function testNormalizeComponent(): void
    {
        $metaData = $this->createConfiguredMock(
            MetaData::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $componentFactory = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getComponent())
            ->willReturn(['FakeComponent' => 'dummy']);

        $actual = $normalizer->normalize($metaData);

        self::assertSame(
            ['component' => ['FakeComponent' => 'dummy']],
            $actual
        );
    }

    public function testNormalizeComponentUnsupported(): void
    {
        $metaData = $this->createConfiguredMock(
            MetaData::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(SpecInterface::class);
        $componentFactory = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getComponent())
            ->willThrowException(new \DomainException());

        $actual = $normalizer->normalize($metaData);

        self::assertSame([], $actual);
    }
}
