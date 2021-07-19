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

use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\ToolRepository;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers\ToolNormalizer;
use CycloneDX\Core\Serialize\DOM\Normalizers\ToolRepositoryNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use DOMElement;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\ToolRepositoryNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 * @covers \CycloneDX\Core\Helpers\SimpleDomTrait
 */
class ToolRepositoryNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new ToolRepositoryNormalizer($factory);
        $tools = $this->createConfiguredMock(ToolRepository::class, ['count' => 0]);

        $actual = $normalizer->normalize($tools);

        self::assertSame([], $actual);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $toolNormalizer = $this->createMock(ToolNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForTool' => $toolNormalizer,
        ]);
        $normalizer = new ToolRepositoryNormalizer($factory);
        $tool = $this->createStub(Tool::class);
        $tools = $this->createConfiguredMock(ToolRepository::class, [
            'count' => 1,
            'getTools' => [$tool],
        ]);
        $FakeTool = $this->createStub(DOMElement::class);

        $toolNormalizer->expects(self::once())->method('normalize')
            ->with($tool)
            ->willReturn($FakeTool);

        $actual = $normalizer->normalize($tools);

        self::assertSame([$FakeTool], $actual);
    }

    public function testNormalizeSkipsOnThrow(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $toolNormalizer = $this->createMock(ToolNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForTool' => $toolNormalizer,
        ]);
        $normalizer = new ToolRepositoryNormalizer($factory);
        $tool1 = $this->createStub(Tool::class);
        $tool2 = $this->createStub(Tool::class);
        $tools = $this->createConfiguredMock(ToolRepository::class, [
            'count' => 1,
            'getTools' => [$tool1, $tool2],
        ]);

        $toolNormalizer->expects(self::exactly(2))->method('normalize')
            ->withConsecutive([$tool1], [$tool2])
            ->willThrowException(new \DomainException());

        $actual = $normalizer->normalize($tools);

        self::assertSame([], $actual);
    }
}
