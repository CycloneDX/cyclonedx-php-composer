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

use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\ToolRepository;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialize\DOM\Normalizers\MetaDataNormalizer;
use CycloneDX\Core\Serialize\DOM\Normalizers\ToolRepositoryNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\MetaDataNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 */
class MetaDataNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $metaData = $this->createMock(MetaData::class);
        $spec = $this->createMock(SpecInterface::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec, 'getDocument' => new DOMDocument()]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode('<metadata></metadata>', $actual);
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
                'getDocument' => new DOMDocument(),
                'makeForToolRepository' => $toolsRepoFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $toolsRepoFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getTools())
            ->willReturn([$factory->getDocument()->createElement('FakeTool', 'dummy')]);

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode(
            '<metadata><tools><FakeTool>dummy</FakeTool></tools></metadata>',
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
                'getDocument' => new DOMDocument(),
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getComponent())
            ->willReturn($factory->getDocument()->createElement('FakeComponent', 'dummy'));

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode(
            '<metadata><FakeComponent>dummy</FakeComponent></metadata>',
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
                'getDocument' => new DOMDocument(),
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getComponent())
            ->willThrowException(new \DomainException());

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode(
            '<metadata></metadata>',
            $actual
        );
    }
}
