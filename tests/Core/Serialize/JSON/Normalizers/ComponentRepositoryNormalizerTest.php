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
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\ComponentRepositoryNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\ComponentRepositoryNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class ComponentRepositoryNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new ComponentRepositoryNormalizer($factory);
        $components = $this->createConfiguredMock(ComponentRepository::class, ['count' => 0]);

        $got = $normalizer->normalize($components);

        self::assertSame([], $got);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $componentNormalizer = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForComponent' => $componentNormalizer,
        ]);
        $normalizer = new ComponentRepositoryNormalizer($factory);
        $component = $this->createStub(Component::class);
        $components = $this->createConfiguredMock(ComponentRepository::class, [
            'count' => 1,
            'getComponents' => [$component],
        ]);

        $componentNormalizer->expects(self::once())->method('normalize')
            ->with($component)
            ->willReturn(['FakeComponent']);

        $got = $normalizer->normalize($components);

        self::assertSame([['FakeComponent']], $got);
    }

    public function testNormalizeSkipsOnThrow(): void
    {
        $spec = $this->createStub(SpecInterface::class);
        $componentNormalizer = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForComponent' => $componentNormalizer,
        ]);
        $normalizer = new ComponentRepositoryNormalizer($factory);
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $components = $this->createConfiguredMock(ComponentRepository::class, [
            'count' => 1,
            'getComponents' => [$component1, $component2],
        ]);

        $componentNormalizer->expects(self::exactly(2))->method('normalize')
            ->withConsecutive([$component1], [$component2])
            ->willThrowException(new \DomainException());

        $got = $normalizer->normalize($components);

        self::assertSame([], $got);
    }
}
