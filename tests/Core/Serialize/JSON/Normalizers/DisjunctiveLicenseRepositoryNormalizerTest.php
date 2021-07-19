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

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseRepositoryNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseRepositoryNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class DisjunctiveLicenseRepositoryNormalizerTest extends TestCase
{
    /**
     * @uses \CycloneDX\Core\Serialize\JSON\Normalizers\DisjunctiveLicenseNormalizer
     */
    public function testNormalize(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);
        $licenseNormalizer = $this->createMock(DisjunctiveLicenseNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['makeForDisjunctiveLicense' => $licenseNormalizer]);
        $normalizer = new DisjunctiveLicenseRepositoryNormalizer($factory);
        $repo = $this->createStub(DisjunctiveLicenseRepository::class);
        $repo->method('getLicenses')->willReturn([$license1, $license2]);

        $licenseNormalizer->expects(self::exactly(2))->method('normalize')
            ->withConsecutive([$license1], [$license2])
            ->willReturnOnConsecutiveCalls(['dummy1'], ['dummy2']);

        $normalized = $normalizer->normalize($repo);

        self::assertSame([['dummy1'], ['dummy2']], $normalized);
    }

    public function testNormalizeSkipOnThrows(): void
    {
        $license1 = $this->createStub(DisjunctiveLicenseWithId::class);
        $license2 = $this->createStub(DisjunctiveLicenseWithName::class);
        $licenseNormalizer = $this->createMock(DisjunctiveLicenseNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['makeForDisjunctiveLicense' => $licenseNormalizer]);
        $normalizer = new DisjunctiveLicenseRepositoryNormalizer($factory);
        $repo = $this->createStub(DisjunctiveLicenseRepository::class);
        $repo->method('getLicenses')->willReturn([$license1, $license2]);

        $licenseNormalizer->expects(self::exactly(2))->method('normalize')
            ->withConsecutive([$license1], [$license2])
            ->willThrowException(new \InvalidArgumentException());

        $got = $normalizer->normalize($repo);

        self::assertSame([], $got);
    }
}
