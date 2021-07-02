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

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseRepositoryTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseTransformer;
use CycloneDX\Core\Serialize\JsonTransformer\TransformerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\DisjunctiveLicenseRepositoryTransformer
 * @covers \CycloneDX\Core\Serialize\JsonTransformer\AbstractTransformer
 */
class DisjunctiveLicenseRepositoryTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);
        $licenseTransformer = $this->createMock(DisjunctiveLicenseTransformer::class);
        $factory = $this->createConfiguredMock(TransformerFactory::class, ['makeForDisjunctiveLicense' => $licenseTransformer]);
        $transformer = new DisjunctiveLicenseRepositoryTransformer($factory);
        $repo = $this->createStub(DisjunctiveLicenseRepository::class);
        $repo->method('getLicenses')->willReturn([$license1, $license2]);

        $licenseTransformer->expects(self::exactly(2))->method('transform')
            ->withConsecutive([$license1], [$license2])
            ->willReturnOnConsecutiveCalls(['dummy1'], ['dummy2']);

        $transformed = $transformer->transform($repo);

        self::assertSame([['dummy1'], ['dummy2']], $transformed);
    }

    public function testTransformThrows(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);
        $licenseTransformer = $this->createMock(DisjunctiveLicenseTransformer::class);
        $factory = $this->createConfiguredMock(TransformerFactory::class, ['makeForDisjunctiveLicense' => $licenseTransformer]);
        $transformer = new DisjunctiveLicenseRepositoryTransformer($factory);
        $repo = $this->createStub(DisjunctiveLicenseRepository::class);
        $repo->method('getLicenses')->willReturn([$license1, $license2]);

        $licenseTransformer->expects(self::once())->method('transform')
            ->with($license1)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported license/i');

        $transformer->transform($repo);
    }
}
